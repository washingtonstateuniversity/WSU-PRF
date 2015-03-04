<?php
/*
Plugin Name: Concatenated PDF
Version: 0.1
Plugin URI: #
Description: A full feature PDF exporter.  Full-Controll,Caching,Smart
Author: jeremyBass
Author URI: #
*/

set_time_limit(300);
ini_set('memory_limit', '-1');

define('CATPDF_NAME', 'Concatenated PDFs');
define('CATPDF_BASE_NAME', 'concatenated-pdfs');
define('CATPDF_VERSION', '0.1');

define('CATPDF_URL', plugin_dir_url(__FILE__));
define('CATPDF_PATH', plugin_dir_path(__FILE__));

define('CATPDF_CACHE_PATH', CATPDF_PATH . 'cache/');
define('CATPDF_CACHE_URL', CATPDF_URL . 'cache/');

define('CATPDF_LOG_PATH', CATPDF_CACHE_PATH . 'logs/');
define('CATPDF_MERGING_PATH', CATPDF_CACHE_PATH . 'merging_stage/');

define('CATPDF_STYLE', CATPDF_PATH . '/css/style.css');
define('PDF_STYLE', CATPDF_URL . 'css/pdf_style.css');


	/**
	 * The slug used to register the catpdf key used for meta data and such.
	 *
	 * @cons string
	 */
	define('CATPDF_KEY', 'wsuwp_catpdf');

if ( ! class_exists( 'concatenatedPDFsLoad' ) ) {
	class concatenatedPDFsLoad {

		public $catpdf_core = NULL;
		
		/*
		 * Initiate the plug-in.
		 */			
		public function __construct() {
			global $catpdf_core;
			include(CATPDF_PATH . '/includes/class.core.php');// Include core
			$catpdf_core = new catpdf_core();// Instantiate core class
			register_activation_hook(__FILE__, array( $this, '_activation' ) );
			register_deactivation_hook(__FILE__, array( $this, '_deactivation' ) );
		}
		/**
		 * Get the plugin into an active state
		 * 
		 * @global class $catpdf_core
		 *
		 * @access public
		 */
		function _activation() {
			global $catpdf_core;
			$catpdf_core->install_init();		// Call plugin initializer
		}
		
		/**
		 * Get the plugin into an inactive state
		 *
		 * @access public
		 */
		function _deactivation() {
			delete_option('catpdf_options');	// Delete plugin options
		}
	}
	global $concatenatedPDFsLoad;
	$concatenatedPDFsLoad = new concatenatedPDFsLoad();
}

?>