<?php
/**
 * Handles all shortcode logic
 **/
if ( ! class_exists( 'UCF_Tuition_Fees_Shortcode' ) ) {
	class UCF_Tuition_Fees_Shortcode {
		
		/**
		 * Registers the `tuition-fees` shortcode.
		 * @author Jim Barnes
		 * @since 1.0.0
		 **/
		public static function register_shortcode() {
			add_shortcode( 'tuition-fees', array( 'UCF_Tuition_Fees_Shortcode', 'callback' ) );
		}

		/**
		 * The shortcode callback
		 * @author Jim Barnes
		 * @since 1.0.0
		 * @param $atts Array | The shortcode argument array
		 * @param $content string | The content string printed after the layout output
		 * @return string | The shortcode output
		 **/
		public static function callback( $atts, $content='' ) {
			$atts = shortcode_atts( array( 
				'layout'     => 'default',
				'title'      => null,
				'program'    => 'UnderGrad',
				'schoolYear' => 'current',
				'feeType'    => 'SCH'
			), $atts );

			$layout = array_shift( $atts );
			$title  = array_shift( $atts );

			$items = UCF_Tuition_Fees_Feed::fetch( $atts );

			return UCF_Tuition_Fees_Common::display( $items, $title, $layout );
		}
	}
}
