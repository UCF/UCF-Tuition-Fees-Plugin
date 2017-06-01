<?php
/**
 * Responsible for general utilities
 **/
if ( ! class_exists( 'UCF_Tuition_Fees_Utils' ) ) {
	class UCF_Tuition_Fees_Utils {
		/**
		 * Formats fees based on logic used by the
		 * tuition and fees website.
		 * @author Jim Barnes
		 * @since 1.0.0
		 * @param $min int | The minimum fee
		 * @param $max int | The maximum fee
		 * @return string | The display string
		 **/
		public static function format_fee( $min, $max ) {
			if ( $min === $max ) {
				return (string)$max;
			}

			if ( $min === 0 && $max > 0 ) {
				return 'Up to ' . (string)$max;
			}

			if ( $min !== 0 && $max > $min ) {
				return (string)$min . ' - ' . (string)$max;
			}

			// If all else fails, return the max fee.
			return (string)$max;
		}
	}
}
