<?php
/*
Plugin Name: WSU Policies and Procedures
Plugin URI: http://prf.wsu.edu/
Description: Handles functionality for WSU Policies and Procedures
Author: washingtonstateuniversity, jeremyBass
Version: 0.1
*/
define('PNP_URL', plugin_dir_url(__FILE__));
define('PNP_PATH', plugin_dir_path(__FILE__));
include( dirname( __FILE__ ) . '/includes/wsu-content-type-policy.php' );
