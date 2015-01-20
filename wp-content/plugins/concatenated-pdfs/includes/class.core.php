<?php


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class catpdf_core {
    public $dompdf = NULL;
	public $shortcode = NULL;
	public $catpdf_pages = NULL;
	public $catpdf_templates = NULL;
	public $catpdf_output = NULL;
	public $catpdf_data = NULL;
	
    public $message = array();
    public $post = array();
    public $title = '';
    public $posts;
	public $_params;
    function __construct() {
		global $dompdf,$shortcode,$catpdf_pages,$catpdf_templates,$catpdf_output,$catpdf_data,$_params;
		$_params = $_POST;
		// Include dompdf //make sure to get back to pulling this in to the settings
		include(CATPDF_PATH . '/dompdf/dompdf_config.inc.php');
		$dompdf = new DOMPDF();
		
		// Include shortcode class
		include(CATPDF_PATH . '/includes/class.shortcode.php');
		$shortcode = new shortcode();
		
		// Include functions
		include(CATPDF_PATH . '/includes/functions.php');

		// Include page
		include(CATPDF_PATH . '/includes/class.pages.php');
		$catpdf_pages = new catpdf_pages();

		// Include templates
		include(CATPDF_PATH . '/includes/class.templates.php');
		$catpdf_templates = new catpdf_templates();
		
		// Include output
		include(CATPDF_PATH . '/includes/class.output.php');
		$catpdf_output = new catpdf_output();

		// Include data
		include(CATPDF_PATH . '/includes/class.data.php');
		$catpdf_data = new catpdf_data();

        if (!is_admin()) {
            if ($catpdf_data->get_options() == 'on') {
                // Initialize public functions
                add_filter('the_content', array( $this, 'apply_post_download_button' ));
            }
        }
       
    }
    /*
     * Initialize install
     */
    public function install_init() {
        // Add database table
        $this->_add_table();
        // Insert default datas
        $this->_insert_defaults();
    }
    /*
     * Add template table
     */
    public function _add_table() {
        global $wpdb,$catpdf_data;
        // Construct query
        $table_name = $wpdb->prefix . "catpdf_template";
        $sql        = "
		CREATE TABLE `{$table_name}`  (
			`template_id` mediumint(9) NOT NULL AUTO_INCREMENT,
			`template_name` varchar(50) NOT NULL,
			`template_description` text,
			`template_loop` text,
			`template_body` text,
			`template_pageheader` text,
			`template_pagefooter` text,
			`create_by` mediumint(9) NOT NULL,
			`create_date` datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
	    UNIQUE KEY id (template_id)
		);";
        // Import wordpress database library
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        // Save version
        add_option('catpdf_db_version', CATPDF_VERSION);
        // Add plugin option holder
        $options = $catpdf_data->get_options();
        add_option('catpdf_options', $options, '', 'yes');
		// Define and create required directories
		$required_dir = array(
			'htmlfragments' => SCRAPE_PATH . '/cache/html',
			'pdf' => SCRAPE_PATH . '/cache/pdf'
		);
		foreach ($required_dir as $dir)
			if( !is_dir($dir) ) @mkdir($dir, 0777);
		
		
		
    }
	/*
     * Set option defaults
     */
    public function _insert_defaults() {
		global $catpdf_templates;
        // Check if default template exist
        if (!$this->_is_exist('template_name', 'Sample Template')) {
            // Get default template
            $default_template = $catpdf_templates->custruct_default_template();
            // Set up data
            $data             = array(
                'template_name' => 'Sample Template',
                'template_loop' => $default_template['loop'],
                'template_body' => $default_template['body'],
				'template_pageheader' => $default_template['pageheader'],
				'template_pagefooter' => $default_template['pagefooter'],
				
            );
            // Insert template
            $catpdf_templates->add_this($data);
        }
    }
	
    /*
     * Check if entry already exist
     * @column - string
     * @value - string
     */
    private function _is_exist($column = '', $value = '') {
        global $wpdb;
        $table_name = $wpdb->prefix . "catpdf_template";
        $result     = $wpdb->get_results("SELECT * FROM " . $table_name . " WHERE " . $column . " = '" . $value . "'");
        if (count($result) > 0) {
            return true;
        } else {
            return false;
        }
    }
	
    /*
     * Returns download button link
     */
    public function apply_post_download_button($content) {
        if ($GLOBALS['post']->post_type == 'post') {
            $id   = $GLOBALS['post']->ID;
            $url  = add_query_arg('catpdf_dl', $id);
            $link = '<a href="' . $url . '"><img src="' . CATPDF_URL . 'images/download-icon.png"></a>';
            return $content . $link;
        } else {
            return $content;
        }
    }



}
?>