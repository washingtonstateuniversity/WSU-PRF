<?php
/*
	Still needs a good refactor
	- actions should be moved and ?page should be detected?
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

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
				if (isset($_params['scrape_export'])) {// Check if pdf export is performed
					add_action('init', array( $this, 'export' ));// Add export hook
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
        add_submenu_page(SCRAPE_BASE_NAME, _('Crawl'), _('Crawl'), 'manage_options', 'scrape-download-pdf', array( $this, 'download_page' ));

    }

    /*
     * Display "Download" page
     */
    public function download_page() {
		global $scrape_data;
        $data                  = array();
        $args                  = array(
            'orderby' => 'name',
            'order' => 'ASC',
            'hierarchical' => 1,
            'hide_empty' => '0'
        );
        $options               = get_option('scrape_options');
        // Construct category dropdown
        $select_cats           = wp_dropdown_categories(array(
            'echo' => 0,
            'hierarchical' => 1
        ));
		
		$post_types      = get_post_types(array(
            'public'   => true,
                     //'_builtin' => false
        ),'names' , 'and' );
		$select_types= '<select name="type[]" multiple="multiple" class="postform" >';
		foreach ($post_types  as $post_type ) {
			$select_types.='<option value="'. $post_type.'"  class="level-0" >'. $post_type. '</option>';
		}
		$select_types.='</select>';


		$select_tags= '<select name="tags[]" multiple="multiple" class="postform" >';
		foreach ($post_types  as $post_type ) {
			$select_tags.='<option value="'. $post_type.'"  class="level-0" >'. $post_type. '</option>';
		}
		$select_tags.='</select>';

		
		
        $select_cats           = str_replace("name='cat' id=", "name='cat[]' multiple='multiple' id=", $select_cats);
        $select_cats           = str_replace("<option", '<option ', $select_cats);
        // Construct user dropdown
        $select_author         = wp_dropdown_users(array(
            'id' => 'author',
            'echo' => false
        ));
        $select_author         = str_replace("name='user' ", "name='user[]' multiple='multiple' ", $select_author);
        $select_author         = str_replace("<option", '<option ', $select_author);
		
		$data['select_tags']  = $select_tags;
		$data['select_types']  = $select_types;
        $data['select_cats']   = $select_cats;
        $data['select_author'] = $select_author;
        $data['select_sizes']  = array( );
        $data['select_ors']    = array( );
        $data['option_url']    = "";//$tool_url;

        $data['message']       = $this->get_message();
        // Display export form
        $this->view(SCRAPE_PATH . '/includes/views/export.php', $data);
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
		$data['scrape_options']   = $scrape_data->get_options();
        // Get templates
        $data['templates'] = "";
        // Display option form
        $this->view(SCRAPE_PATH . '/includes/views/options.php', $data);
    }
    /*-------------------------------------------------------------------------*/
    /* -Export- 															   */
    /*-------------------------------------------------------------------------*/
    /*
     * Perform export pdf
     */
    public function findlinks() {
        global $scrape_output, $_params;
        die("going to build the link array");
		$page=scrape_get_content($_params['url'], $_params['selector'], $_params['xpath'], $scrapeopt);
		
		
		
		
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
?>