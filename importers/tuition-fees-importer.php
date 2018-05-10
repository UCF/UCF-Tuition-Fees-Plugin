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
		$mapping = array(
			'DPT'  => array( 'plan_code' => 'PT-DPT', 'subplan_code' => '' ),
			'MD'   => array( 'plan_code' => 'MEDICIN-MD', 'subplan_code' => '' ),
			'FIEA' => array( 'plan_code' => 'DIGMED-MS', 'subplan_code' => '' ),
			'EMBA' => array( 'plan_code' => 'BUS-MBA', 'subplan_code' => 'EXEC-MBA' ),
			'PMBA' => array( 'plan_code' => 'BUS-MBA', 'subplan_code' => 'PROF-MBA' ),
			'PMSM' => array( 'plan_code' => 'BUS-MS', 'subplan_code' => 'BUSHR-MS' ),
			'PMRE' => array( 'plan_code' => 'RLESTAT-MS', 'subplan_code' => '' ),
			'EHSA' => array( 'plan_code' => 'HLTHSCI-MS', 'subplan_code' => 'ZMRHLEXECH' ),
			'MRA'  => array( 'plan_code' => 'RCHADM-MRA', 'subplan_code' => '' ),
			'MNM'  => array( 'plan_code' => 'NONPRFTMNM', 'subplan_code' => 'MNM-COHORT' ),
			'GCRA' => array( 'plan_code' => 'RCHADM-CRT', 'subplan_code' => '' ),
			'GCIA' => array( 'plan_code' => 'HCIADMCRT', 'subplan_code' => '' ),
			'MSEM' => array( 'plan_code' => 'ENGRMGT-MS', 'subplan_code' => '' ),
			'MSAN' => array( 'plan_code' => 'BUS-MS', 'subplan_code' => 'BUSAN-MS' ),
			'MSD'  => array( 'plan_code' => 'DATAANAMS', 'subplan_code' => '' ),
		);

	/**
	 * Constructor
	 * @author Jim Barnes
	 * @since 2.0.2
	 * @param string $api The url of the tuition and fees feed
	 * @param string $post_type The post type of posts to assign tuition and fee data to
	 * @return Tuition_Fees_Data_Importer
	 */
	public function __construct( $api, $post_type='degree' ) {
		$this->api = $api;
		$this->post_type = $post_type;
		$this->data = array();
		$this->degrees = array();
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
				if ( stripos( $fee->FeeName, '(Per Hour)' ) === false &&
					 stripos( $fee->FeeName, '(Per Term)' ) === false &&
					 stripos( $fee->FeeName, '(Annual)' ) === false )
				{
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
			$parent_program_type    = wp_get_post_terms( $degree->ID, 'program_types', array( 'parent' => 0 ) );
			$parent_program_type_id = is_array( $parent_program_type ) ? $parent_program_type[0]->term_id : 0;
			$program_type = wp_get_post_terms( $degree->ID, 'program_types', array( 'parent' => $parent_program_type_id ) );
			$program_type = ( is_array( $program_type ) && ! empty( $program_type ) ) ? $program_type[0] : null;
			$plan_code    = get_post_meta( $degree->ID, UCF_Tuition_Fees_Config::get_option_or_default( 'degree_plan_code_name' ), true );
			$subplan_code = get_post_meta( $degree->ID, UCF_Tuition_Fees_Config::get_option_or_default( 'degree_subplan_code_name' ), true );
			$is_online    = filter_var( get_post_meta( $degree->ID, UCF_Tuition_Fees_Config::get_option_or_default( 'degree_is_online_name' ), true ), FILTER_VALIDATE_BOOLEAN );

			// If no program type, skip it
			if ( ! $program_type ) { $this->skipped_total++; continue; }

			$schedule_code = $this->get_schedule_code( $program_type->name, $plan_code, $subplan_code, $is_online );

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

	private function get_schedule_code( $program_type, $plan_code, $subplan_code, $is_online ) {
		// Loop through the mapping variable and look for a match
		// This should handle unique exceptions for graduate programs
		foreach ( $this->mapping as $sched_code => $plan_codes ) {
			if (
				$plan_codes['plan_code'] === $plan_code
				&& $plan_codes['subplan_code'] === $subplan_code
			) {
				$this->mapped_count++;
				return $sched_code;
			}
		}

		// Handle exceptions for online programs
		if ( $is_online ) {
			if ( $program_type === 'Bachelor' ) {
				return 'UOU';
			}
			if ( in_array( $program_type, array( 'Master', 'Doctorate' ) ) ) {
				return 'UOG';
			}
		}

		// Handle supported undergraduate programs
		if ( in_array( $program_type, array( 'Bachelor', 'Minor' ) ) ) {
			return 'UnderGrad';
		}

		// Handle supported graduate programs
		if ( in_array( $program_type, array( 'Master', 'Doctorate' ) ) ) {
			return 'Grad';
		}

		// Skip anything else
		return null;
	}
}
