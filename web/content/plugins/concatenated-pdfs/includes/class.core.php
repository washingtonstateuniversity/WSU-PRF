<?php

/* things still to do
-remove the use themes templates inlue of per template css path link
-must beable to sort on optional items like tax/type etc
-cache the pdfs on md5 of (tmp-ops)+(lastpost-date)+(query)
-provide more areas to controll
-make the index
-create ruls for the bookmarking


*/
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class catpdf_core {
    public $dompdf = NULL;
	public $shortcode = NULL;
	public $catpdf_pages = NULL;
	public $catpdf_templates = NULL;
	public $catpdf_output = NULL;
	
    public $message = array();
    public $post = array();
    public $title = '';
    public $posts;
	public $catpdf_params;
    function __construct() {
		global $dompdf,$shortcode,$catpdf_pages,$catpdf_templates,$catpdf_output;

		// Include dompdf //make sure to get back to pulling this in to the settings
		include(CATPDF_PLUGIN_PATH . '/dompdf/dompdf_config.inc.php');
		$dompdf = new DOMPDF(); // Instantiate dompdf library
		
		// Include shortcode class
		include(CATPDF_PLUGIN_PATH . '/includes/class.shortcode.php');
		$shortcode = new shortcode();// Instantiate shortcode class
		
		// Include functions
		include(CATPDF_PLUGIN_PATH . '/includes/functions.php');

		// Include page
		include(CATPDF_PLUGIN_PATH . '/includes/class.pages.php');
		$catpdf_pages = new catpdf_pages();// Instantiate pages class

		// Include templates
		include(CATPDF_PLUGIN_PATH . '/includes/class.templates.php');
		$catpdf_templates = new catpdf_templates();// Instantiate pages class
		
		// Include output
		include(CATPDF_PLUGIN_PATH . '/includes/class.output.php');
		$catpdf_output = new catpdf_output();// Instantiate output class
		
        if (!is_admin()) {
            $options = get_option('catpdf_options');
            if ($options['postdl'] == 'on') {
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
    private function _add_table() {
        global $wpdb;
        // Construct query
        $table_name = $wpdb->prefix . "catpdf_template";
        $sql        = "CREATE TABLE " . $table_name . " (
	    template_id mediumint(9) NOT NULL AUTO_INCREMENT,
	    template_name varchar(50) NOT NULL,
		template_description text,
	    template_loop text,
	    template_body text,
		template_pageheader text,
		template_pagefooter text,
		create_by mediumint(9) NOT NULL,
		create_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
	    UNIQUE KEY id (template_id)
		);";
        // Import wordpress database library
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        // Save version
        add_option('catpdf_db_version', CATPDF_VERSION);
        // Add plugin option holder
        $options = array(
            'enablecss' => 'on',
            'title' => 'Report %mm-%yyyy',
            'dltemplate' => 'def',
            'postdl' => 'off'
        );
        add_option('catpdf_options', $options, '', 'yes');
    }
	/*
     * Set option defaults
     */
    private function _insert_defaults() {
        // Check if default template exist
        if (!$this->_is_exist('template_name', 'Sample Template')) {
            // Get default template
            $default_template = $this->custruct_default_template();
            // Set up data
            $data             = array(
                'template_name' => 'Sample Template',
                'template_loop' => $default_template['loop'],
                'template_body' => $default_template['body'],
				'template_pageheader' => $default_template['pageheader'],
				'template_pagefooter' => $default_template['pagefooter'],
				
            );
            // Insert template
            $this->add_this($data);
        }
    }
    /*
     * Returns download button link
     */
    public function apply_post_download_button($content) {
        if ($GLOBALS['post']->post_type == 'post') {
            $id   = $GLOBALS['post']->ID;
            $url  = add_query_arg('catpdf_dl', $id);
            $link = '<a href="' . $url . '"><img src="' . CATPDF_PLUGIN_URL . 'images/download-icon.png"></a>';
            return $content . $link;
        } else {
            return $content;
        }
    }



}
?>