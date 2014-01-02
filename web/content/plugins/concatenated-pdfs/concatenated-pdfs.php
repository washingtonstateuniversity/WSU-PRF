<?php
/*
Plugin Name: Concatenated PDF
Version: 0.1
Plugin URI: #
Description: #
Author: Jeremy Bass
Author URI: #
*/
set_time_limit(300);
ini_set('memory_limit', '-1');
define('CATPDF_NAME', 'Concatenated PDFs');
define('CATPDF_BASE_NAME', 'concatenated-pdfs');
define('CATPDF_VERSION', '0.1');
define('CATPDF_PLUGIN_URL', plugin_dir_url(__FILE__));
define('CATPDF_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('CATPDF_STYLE', CATPDF_PLUGIN_PATH . '/css/style.css');
define('PDF_STYLE', CATPDF_PLUGIN_URL . 'css/pdf_style.css');

// Include dompdf //make sure to get back to pulling this in to the settings
include(CATPDF_PLUGIN_PATH . '/dompdf/dompdf_config.inc.php');
$dompdf = new DOMPDF(); // Instantiate dompdf library

// Include shortcode class
include(CATPDF_PLUGIN_PATH . '/inc/shortcode_class.php');
$shortcode = new shortcode();// Instantiate shortcode class

// Include core
include(CATPDF_PLUGIN_PATH . '/inc/core_class.php');
$catpdf_core = new catpdf_core();// Instantiate core class

// Include functions
include(CATPDF_PLUGIN_PATH . '/inc/functions.php');

// Set option values
function catpdf_initializer() {
    // Instantiate core class
    $catpdf_core = new catpdf_core();
    // Call plugin initializer
    $catpdf_core->install_init();
}
register_activation_hook(__FILE__, 'catpdf_initializer');

// Unset option values
function catpdf_remove() {
    // Delete plugin options
    delete_option('catpdf_options');
}
register_deactivation_hook(__FILE__, 'catpdf_remove');
?>