<?php
/*
	Still needs a good refactor
	- actions should be moved and ?page should be detected?
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! class_exists( 'scrape_pages' ) ) {
	class scrape_pages {
		public $dompdf = NULL;
		public $message = array();
		public $title = '';
	
		
		function __construct() {
			global $_params;
			if (is_admin()) {
				if (isset($_params)) {
					if (isset($_params['scrape_save_option'])) {// Check if option save is performed
						add_action('init', array( $this, 'update_options' ));// Add update option action hook
					}
					if (isset($_params['scrape_findlinks'])) {// Check if pdf export is performed
						add_action('init', array( $this, 'findlinks' ));// Add export hook
					}
				}
				add_action('admin_init', array( $this, 'admin_init' ));
				add_action('admin_menu', array( $this, 'admin_menu' ));
			}
			if (isset($_GET['scrape_dl'])) {// Check if post download is performed
				add_action('init', array( $this, 'download_post' ));// Add download action hook
			}
			if (isset($_GET['scrape_post_dl'])) {// Check if single post download is performed
				add_action('init', array( $this, 'download_posts' ));// Add download action hook
			}
		}
		/*
		 * Initailize plugin admin part
		 */
		public function admin_init() {
			global $wp_scripts;
			// Enque style and script		
			wp_enqueue_script('jquery-ui-core');
			wp_enqueue_script('jquery-ui-datepicker', SCRAPE_URL.'js/ui/jquery.ui.datepicker.js', array('jquery'), '1.9.0', 'all');
			wp_enqueue_style('jquery-ui-datepicker', SCRAPE_URL.'css/ui/jquery.ui.all.css', false, '1.9.0', 'all');
			
			wp_enqueue_script('jquery-ui-tabs', SCRAPE_URL.'js/ui/jquery.ui.tabs.js', array('jquery'), '1.9.0', 'all');		
			wp_enqueue_style('jquery-ui-tabs', SCRAPE_URL.'css/ui/jquery.ui.all.css', false, '1.9.0', 'all');
			// get registered script object for jquery-ui
			$ui = $wp_scripts->query('jquery-ui-core');
		 
			// tell WordPress to load the Smoothness theme from Google CDN
			$protocol = is_ssl() ? 'https' : 'http';
			$url = "$protocol://ajax.googleapis.com/ajax/libs/jqueryui/{$ui->ver}/themes/smoothness/jquery-ui.min.css";
			wp_enqueue_style('jquery-ui-smoothness', $url, false, null);
	
			wp_enqueue_script('scrape-js', SCRAPE_URL . 'js/scrape.custom.js', array('jquery'), '', 'all');
			wp_enqueue_style('scrape-style', SCRAPE_URL . 'css/style.css', false, '1.9.0', 'all');
		}
		/*
		 * Add plugin menu
		 */
		public function admin_menu() {
			// Register menu
			add_menu_page(SCRAPE_NAME, SCRAPE_NAME, 'manage_options', SCRAPE_BASE_NAME, array( $this, 'option_page' ), SCRAPE_URL . 'images/nav-icon.png');
			// Register sub-menu
			add_submenu_page(SCRAPE_BASE_NAME, _('Crawl'), _('Crawl'), 'manage_options', 'scrape-crawler', array( $this, 'crawler_page' ));
	
		}
	
		/*
		 * Display "Download" page
		 */
		public function crawler_page() {
			global $scrape_data;
			include(SCRAPE_PATH . '/includes/views/lists/class.crawl_list.php');
			$wp_list_table = new crawl_list();
			$wp_list_table->prepare_items();
			ob_start();
			$wp_list_table->display();
			$data['table']   = ob_get_clean();
			$data['message'] = $this->get_message();
			$data['option_url']    = "";//$tool_url;
			
			$this->view(SCRAPE_PATH . '/includes/views/crawl_list.php', $data);
		}
	
		public function findlinks() {
			global $wpdb, $scrape_output,$scrape_data, $_params;
			
			$options = $scrape_data->get_options();
	
			$url=$_params['scrape_url'];
			$scrape_data->rootUrl = parse_url($url, PHP_URL_HOST);
			//var_dump($url);
			$urls = $scrape_data->get_all_urls($url,$options['crawl_depth']);
			//var_dump($urls);
			die("going to build the link array");
	
			$this->download_page();
		}
			
		/*-------------------------------------------------------------------------*/
		/* -Option- 															   */
		/*-------------------------------------------------------------------------*/
		/*
		 * Update plugin option
		 */
		public function update_options() {
			global $_params;
			$options = $_params;
			update_option('scrape_options', $options);
		}
		/*
		 * Display "Option" page
		 */
		public function option_page() {
			global $scrape_data;
			// Set options
			$data['options']   = $scrape_data->get_options();
			$data['scrape_options']   = $data['options'];
			// Get templates
			$data['templates'] = "";
			// Display option form
			$this->view(SCRAPE_PATH . '/includes/views/options.php', $data);
		}
	
	
	
	
		/*-------------------------------------------------------------------------*/
		/* -General- 															   */
		/*-------------------------------------------------------------------------*/
		/*
		 * Return falsh message
		 */
		public function get_message() {
			global $scrape_core;
			if (!empty($scrape_core->message)) {
				$arr = $scrape_core->message;
				$message = "<div id='message' class='{$arr['type']}'><p>{$arr['message']}</p></div>";
				$scrape_core->message=NULL;
				return $message;
			}
		}
		/*
		 * Return query filter
		 * @file - string
		 * @data - array
		 * @return - boolean
		 */
		public function view($file = '', $data = array(), $return = false) {
			if (count($data) > 0) {
				extract($data);
			}
			if ($return) {
				ob_start();
				include($file);
				return ob_get_clean();
			} else {
				include($file);
			}
		}
		

	}
	global $scrape_pages;
	$scrape_pages = new scrape_pages();
}
?>