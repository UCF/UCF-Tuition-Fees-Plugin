<?php
/**
 * Responsible for fetching tuition and fee data
 **/
if ( ! class_exists( 'UCF_Tuition_Fees_Feed' ) ) {
	class UCF_Tuition_Fees_Feed {
		/**
		 * Fetches tuition and fees feed
		 * @author Jim Barnes
		 * @since 1.0.0
		 * @param $args Array | The argument array to pass to the feed
		 * @return Array | The array of fees
		 **/
		public static function fetch( $args ) {
			$defaults = array(
				'program'    => 'UnderGrad',
				'schoolYear' => 'current',
				'feeType'    => 'SCH'
			);

			// Build the url from the args
			$args = wp_parse_args( $args, $defaults );
			$url = UCF_Tuition_Fees_Config::get_option_or_default( 'base_feed_url' );
			$url = $url . '?' . http_build_query( $args );

			// Build transient name and fetch items
			$transient_name = self::get_transient_name( $url );
			$items = get_transient( $transient_name );
			$cache_results = UCF_Tuition_Fees_Config::get_option_or_default( 'cache_results' );
			$expiration = UCF_Tuition_Fees_Config::get_option_or_default( 'transient_expiration' );

			if ( $items === false || $cache_results === false ) {
				$response = wp_remote_get( $url, array( 'timeout' => 15 ) );

				if ( is_array( $response ) ) {
					$items = json_decode( wp_remote_retrieve_body( $response ) );
				} else {
					$items = false;
				}

				if ( $items ) {
					set_transient( $transient_name, $items, $expiration );
				}
			}

			return $items;
		}

		/**
		 * Returns a unique transient name based on url
		 * @author Jim Barnes
		 * @since 1.0.0
		 * @param $url string | The url to use in the transient name generation
		 * @return string | The unique transient name
		 **/
		private static function get_transient_name( $url ) {
			return 'ucf_tuition_fees_' . md5( $url );
		}
	}
}
