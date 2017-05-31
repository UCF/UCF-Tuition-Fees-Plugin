<?php
/*
Plugin Name: UCF Tuition and Fees Plugin
Version: 1.0.0
Author: UCF Web Communications
Description: Provides a shortcode for displaying tuition and fees
*/
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'TUITION_FEES__FILE', __FILE__ );

include_once 'includes/tuition-fees-config.php';

// Initiate the plugin settings
add_action( 'admin_init', array( 'UCF_Tuition_Fees_Config', 'settings_init' ) );
// Add the options page
add_action( 'admin_menu', array( 'UCF_Tuition_Fees_Config', 'add_options_page' ) );
