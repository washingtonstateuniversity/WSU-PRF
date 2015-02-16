<?php
/*
Plugin Name: Concatenated PDF
Version: 0.1
Plugin URI: #
Description: A full feature PDF exporter.  Full-Controll,Caching,Smart
Author: Jeremy Bass
Author URI: #
*/
set_time_limit(300);
ini_set('memory_limit', '-1');
define('CATPDF_NAME', 'Concatenated PDFs');
define('CATPDF_BASE_NAME', 'concatenated-pdfs');
define('CATPDF_VERSION', '0.1');
define('CATPDF_URL', plugin_dir_url(__FILE__));
define('CATPDF_PATH', plugin_dir_path(__FILE__));
define('CATPDF_STYLE', CATPDF_PATH . '/css/style.css');
define('PDF_STYLE', CATPDF_URL . 'css/pdf_style.css');
define('CATPDF_CACHE_PATH', CATPDF_PATH . 'cache/');
define('CATPDF_CACHE_URL', CATPDF_URL . 'cache/');


	/**
	 * The slug used to register the catpdf key used for meta data and such.
	 *
	 * @cons string
	 */
	define('CATPDF_KEY', 'wsuwp_catpdf');

/* things still to do
[ ]-POST/GET to $_param validation
[ ]-remove the use themes templates inlue of per template css path link
[ ]-must be able to sort on optional items like tax/type etc
[•]-cache the pdfs on md5 of (tmp-ops)+(lastpost-date)+(query)
[•]-provide more areas to controll
[x]-make the index
[ ]-create ruls for the bookmarking
[ ]-create log/debug page
*/
if ( ! class_exists( 'concatenatedPDFsLoad' ) ) {
	$catpdf_core = NULL;
	class concatenatedPDFsLoad {
		public function __construct() {
			global $catpdf_core;
			include(CATPDF_PATH . '/includes/class.core.php');// Include core
			$catpdf_core = new catpdf_core();// Instantiate core class
		}
	}
	/*
	 * Initiate the plug-in.
	 */
	register_activation_hook(__FILE__,  'catpdf_initializer');
	register_deactivation_hook(__FILE__,  'catpdf_remove');
	// Set option values
	function catpdf_initializer() {
		global $catpdf_core;
		$catpdf_core->install_init();		// Call plugin initializer
	}
	// Unset option values
	function catpdf_remove() {
		delete_option('catpdf_options');	// Delete plugin options
	}	 
	 
	global $concatenatedPDFsLoad;
	$concatenatedPDFsLoad = new concatenatedPDFsLoad();
}

?>