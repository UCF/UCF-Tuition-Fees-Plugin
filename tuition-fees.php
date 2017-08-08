<?php
/*
Plugin Name: UCF Tuition and Fees Plugin
Version: 2.0.0
Author: UCF Web Communications
Description: Provides a shortcode for displaying tuition and fees
*/
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'TUITION_FEES__FILE', __FILE__ );
define( 'TUITION_FEES__STATIC_URL', plugins_url( 'static', __FILE__ ) );
define( 'TUITION_FEES__JS_URL', TUITION_FEES__STATIC_URL . '/js' );


include_once 'includes/tuition-fees-config.php';
include_once 'includes/tuition-fees-feed.php';
include_once 'includes/tuition-fees-common.php';
include_once 'includes/tuition-fees-shortcode.php';
include_once 'includes/tuition-fees-utils.php';

// Initiate the plugin settings
add_action( 'admin_init', array( 'UCF_Tuition_Fees_Config', 'settings_init' ) );
// Add the options page
add_action( 'admin_menu', array( 'UCF_Tuition_Fees_Config', 'add_options_page' ) );
// Enqueue admin assets
add_action( 'admin_enqueue_scripts', array( 'UCF_Tuition_Fees_Config', 'enqueue_admin_assets' ), 10, 1 );
// Add the shortcode
add_action( 'init', array( 'UCF_Tuition_Fees_Shortcode', 'register_shortcode' ) );
// Add the default layout
add_action( 'ucf_tuition_fees_display_default', array( 'UCF_Tuition_Fees_Common', 'display_default' ), 10, 3 );
