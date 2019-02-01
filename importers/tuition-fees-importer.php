<?php
/**
 * Handles importing tuition and fee data
 */
class Tuition_Fees_Data_Importer {
	private
		$api, // The url to get tuition and fee data
		$data, // The tuition and fee data
		$post_type, // The post type to set tuition and fee data to
		$degrees, // Posts to add tuition and fee data to
		$mapped_total = 0,
		$updated_total = 0,
		$skipped_total = 0,
		$degree_count = 0,
		$mappings = array();

	/**
	 * Constructor
	 * @author Jim Barnes
	 * @since 2.0.2
	 * @param string $api The url of the tuition and fees feed
	 * @param string $post_type The post type of posts to assign tuition and fee data to
	 * @return Tuition_Fees_Data_Importer
	 */
	public function __construct( $api, $post_type='degree', $mappings=null ) {
		$this->api = $api;
		$this->post_type = $post_type;
		$this->data = array();
		$this->mappings = $this->parse_mappings( $mappings );
		$this->degrees = array();
	}

	/**
	 * Parses the mappings file and sets the values to the mappings array
	 * @author Jim Barnes
	 * @param string $mappings The path to the mappings file
	 * @return array The mappings array
	 */
	private function parse_mappings( $mappings ) {
		// Return empty array if there's no file
		if ( $mappings === null ) return array();

		$mapping_file = null;

		if (
			substr( $mappings, 0, 4 ) === 'http' ||
			substr( $mappings, 0, 5 ) === 'https'
		) {
			$args = array(
				'timeout' => 15
			);

			$response = wp_remote_get( $mappings, $args );
			$mapping_file = wp_remote_retrieve_body( $response );
		} else {
			$mapping_file = file_get_contents( $mappings );
		}

		if ( ! $mapping_file ) {
			throw new Exception(
				"The file provided could not be opened."
			);
		}

		$mappings = json_decode( $mapping_file );

		return $mappings;
	}

	/**
	 * Imports tuition and fee data into the specified post type
	 * @author Jim Barnes
	 * @since 2.0.2
	 */
	public function import() {
		$this->set_fee_schedules();
		$this->set_fee_data();
		$this->get_existing_degrees();
		$this->update_degrees();
	}

	/**
	 * Returns the current success/failure stats
	 * @author Jim Barnes
	 * @since 2.0.2
	 * @return string
	 */
	public function get_stats() {
		$success_percentage = round( $this->updated_total / $this->degree_count * 100 );
		return
"
Successfully updated tuition data.
Updated    : {$this->updated_total}
Exceptions : {$this->mapped_total}
Skipped    : {$this->skipped_total}
Success %  : {$success_percentage}%
";
	}

	/**
	 * Retrieves the available programs
	 * @author Jim Barnes
	 * @since 2.0.2
	 */
	private function set_fee_schedules() {
		$query = array(
			'schoolYear' => 'current',
			'feeName'    => 'Tuition'
		);

		$url = $this->api . '?' . http_build_query( $query );

		$args = array(
			'timeout' => 15
		);

		$response = wp_remote_get( $url, $args );

		$schedules = array();

		if ( is_array( $response ) ) {
			$response_body = wp_remote_retrieve_body( $response );

			$schedules = json_decode( $response_body );

			if ( ! $schedules ) {
				throw new Exception(
					'Unable to retrieve fee schedules',
					2
				);
			}
		} else {
			throw new Exception(
				'Failed to connect to the tuition and fees feed.',
				1
			);
		}

		if ( count( $schedules ) === 0 ) {
			throw new Exception(
				'No results found in the tuition and fees feed.',
				3
			);
		}

		foreach( $schedules as $schedule ) {
			if ( ! isset( $this->data[$schedule->Program] ) ) {
				$this->data[$schedule->Program] = array(
					'code'   => $schedule->Program,
					'type'   => $schedule->FeeType,
					'res'    => '',
					'nonres' => ''
				);
			}
		}
	}

	/**
	 * Retrieves the fee schedule for each program
	 * @author Jim Barnes
	 * @since 2.0.2
	 */
	private function set_fee_data() {
		foreach( $this->data as $schedule ) {
			$code = $schedule['code'];
			$type = $schedule['type'];

			$query = array(
				'schoolYear' => 'current',
				'program'    => $code,
				'feeType'    => $type
			);

			$url = $this->api . '?' . http_build_query( $query );

			$args = array(
				'timeout' => 15
			);

			$response = wp_remote_get( $url, $args );

			if ( is_array( $response ) ) {
				$response_body = wp_remote_retrieve_body( $response );

				$fees = json_decode( $response_body );

				if ( ! $fees ) {
					throw new Exception(
						'Unable to retrieve fee schedules.',
						2
					);
				}
			} else {
				throw new Exception(
					'Failed to connect to the tuition and fees feed.',
					1
				);
			}

			if ( count( $fees ) === 0 ) {
				continue;
			}

			$resident_total = 0;
			$non_resident_total = 0;

			foreach( $fees as $fee ) {
				//Make sure this isn't an "Other" fee
				if ( $this->is_required_fee( $fee ) ) {
					$resident_total += $fee->MaxResidentFee;
					$non_resident_total += $fee->MaxNonResidentFee;
				}
			}

			$per_unit = '';

			switch( $type ) {
				case 'SCH':
					$per_unit = ' per credit hour';
					break;
				case 'TRM':
					$per_unit = ' per term';
					break;
				case 'ANN':
					$per_unit = ' per year';
					break;
			}

			$this->data[$code]['res'] = apply_filters( 'ucf_tuition_fees_format_fee', $resident_total, $per_unit );
			$this->data[$code]['nonres'] = apply_filters( 'ucf_tuition_fees_format_fee', $non_resident_total, $per_unit );
		}
	}

	/**
	 * Determines if the fee should be added to the total
	 * @author Jim Barnes
	 * @since 2.1.2
	 * @param object $fee The fee object to compare
	 * @return bool True if the fee should be included
	 */
	private function is_required_fee( $fee ) {
		$retval = ( stripos( $fee->FeeName, '(Per Hour)' ) === false &&
			stripos( $fee->FeeName, '(Per Term)' ) === false &&
			stripos( $fee->FeeName, '(Annual)' ) === false );

		$retval = apply_filters( 'ucf_tuition_fees_is_required', $retval, $fee );

		return $retval;
	}

	/**
	 * Returns the current degrees
	 * @author Jim Barnes
	 * @since 2.0.2
	 * @return array
	 */
	private function get_existing_degrees() {
		$args = array(
			'post_type'      => $this->post_type,
			'posts_per_page' => -1
		);

		$this->degrees = get_posts( $args );
		$this->degree_count = count( $this->degrees );
	}

	/**
	 * Loops through posts and updates them
	 * with tuition data
	 * @author Jim Barnes
	 * @since 2.0.2
	 */
	private function update_degrees() {
		foreach( $this->degrees as $degree ) {
			$skip = get_post_meta( $degree->ID, 'degree_tuition_skip', true );
			$skip = ! empty( $skip ) ? filter_var( $skip, FILTER_VALIDATE_BOOL ) : false;

			if ( $skip ) {
				$this->skipped_total++;
				continue;
			}

			$parent_program_type    = wp_get_post_terms( $degree->ID, 'program_types', array( 'parent' => 0 ) );
			$parent_program_type_id = is_array( $parent_program_type ) ? $parent_program_type[0]->term_id : 0;
			$program_type = wp_get_post_terms( $degree->ID, 'program_types', array( 'parent' => $parent_program_type_id ) );
			if ( is_array( $program_type ) && ! empty( $program_type ) ) {
				$program_type = $program_type[0];
			}
			elseif ( $parent_program_type_id > 0 ) {
				$program_type = $parent_program_type[0];
			}
			else {
				$program_type = null;
			}

			$plan_code    = get_post_meta( $degree->ID, UCF_Tuition_Fees_Config::get_option_or_default( 'degree_plan_code_name' ), true );
			$subplan_code = get_post_meta( $degree->ID, UCF_Tuition_Fees_Config::get_option_or_default( 'degree_subplan_code_name' ), true );
			$is_online    = filter_var( get_post_meta( $degree->ID, UCF_Tuition_Fees_Config::get_option_or_default( 'degree_online_meta_field' ), true ), FILTER_VALIDATE_BOOLEAN );

			// If no program type, skip it
			if ( ! $program_type ) { $this->skipped_total++; continue; }

			$schedule_code = $this->get_schedule_code( $degree, $program_type->name, $plan_code, $subplan_code, $is_online );

			// If we can't determine the program code, skip it
			if ( ! $schedule_code ) { $this->skipped_total++; continue; }

			if ( isset( $this->data[$schedule_code] ) ) {
				$resident_total = $this->data[$schedule_code]['res'];
				$non_resident_total = $this->data[$schedule_code]['nonres'];

				update_post_meta( $degree->ID, 'degree_resident_tuition', $resident_total );
				update_post_meta( $degree->ID, 'degree_nonresident_tuition', $non_resident_total );

				$this->updated_total++;
			}
			else {
				$this->skipped_total++;
				continue;
			}
		}
	}

	private function get_schedule_code( $degree, $program_type, $plan_code, $subplan_code, $is_online ) {
		$schedule_code = null;
		$mapped_found  = false;

		// Loop through the mapping variable and look for a match
		// This should handle unique exceptions for graduate programs
		foreach ( $this->mappings as $mapping ) {
			if (
				$mapping->plan_code === $plan_code
				&& $mapping->subplan_code === $subplan_code
			) {
				$this->mapped_total++;
				$mapped_found = true;
				$schedule_code = $mapping->code;
				break;
			}
		}

		if ( ! $schedule_code ) {
			// Handle exceptions for online programs
			if ( $is_online ) {
				if ( $program_type === 'Bachelor' ) {
					$schedule_code = 'UOU';
				}
				elseif ( in_array( $program_type, array( 'Master', 'Doctorate' ) ) ) {
					$schedule_code = 'UOG';
				}
			}
			// Handle supported undergraduate programs
			elseif ( in_array( $program_type, array( 'Bachelor', 'Minor' ) ) ) {
				$schedule_code = 'UnderGrad';
			}
			// Handle supported graduate programs
			elseif ( in_array( $program_type, array( 'Master', 'Doctorate' ) ) ) {
				$schedule_code = 'Grad';
			}
		}

		if ( has_filter( 'ucf_tuition_fees_get_schedule_code' ) ) {
			$schedule_code = apply_filters( 'ucf_tuition_fees_get_schedule_code', $schedule_code, $degree, $program_type, $plan_code, $subplan_code, $is_online, $mapped_found );
		}

		return $schedule_code;
	}
}
