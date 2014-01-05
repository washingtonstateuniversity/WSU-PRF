<?php
/*
Plugin Name: Scrape-N-Post
Version: 0.1
Plugin URI: #
Description: Import content form your old site with easy
Author: Jeremy Bass
Author URI: #
*/

define('SCRAPE_NAME', 'Scrape-N-Post');
define('SCRAPE_BASE_NAME', 'scrape-n-post');
define('SCRAPE_VERSION', '0.1');
define('SCRAPE_URL', plugin_dir_url(__FILE__));
define('SCRAPE_PATH', plugin_dir_path(__FILE__));
define('SCRAPE_CACHE_PATH', SCRAPE_PATH . 'cache/');
define('SCRAPE_CACHE_URL', SCRAPE_URL . 'cache/');

/* things still to do
[•]-Add webshot for previews of the urls
[ ]-POST/GET to $_param validation
[ ]-cronjob support for active crawl and snync
*/
if ( ! class_exists( 'scrapeNpostLoad' ) ) {
	$scrape_core = NULL;
	class scrapeNpostLoad {
		public function __construct() {
			global $scrape_core;
			include(SCRAPE_PATH . '/includes/class.core.php');// Include core
			$scrape_core = new scrape_core();// Instantiate core class
			/*
			 * Initiate the plug-in.
			 */
			register_activation_hook(__FILE__,  'scrape_N_post_initializer');
			register_deactivation_hook(__FILE__,  'scrape_N_post_remove');
		}
	}

	// Set option values
	function scrape_N_post_initializer() {
		global $scrape_core;
		$scrape_core->install_init();		// Call plugin initializer
	}
	// Unset option values
	function scrape_N_post_remove() {
		//delete_option('scrape_options');	// Delete plugin options
	}	 
	 
	global $scrapeNpostLoad;
	$scrapeNpostLoad = new scrapeNpostLoad();
}

?>