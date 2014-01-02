<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class catpdf_templates {
    public $title = '';
    function __construct() {
        
    }

    /*
     * Return default template structure
     */
    private function custruct_default_template($type = 'all') {
        $temp         = array();
        $temp['name'] = 'Default';
		
		
		
		// Construct template loop
		$pageheadertemplate = '[site_title],[site_tagline]';
		$pagefootertemplate = '[page numbers]';
		
		
        if ($type == 'single') {
            // Construct template loop
            $looptemplate = '<div class="post single">';
            $looptemplate .= '<h2>[title]</h2>';
            $looptemplate .= '<div class="meta"><p>Posted on <strong>[date]</strong> by <strong>[author]</strong></p></div>';
            $looptemplate .= '<p>[content]</p>';
            $looptemplate .= '<div class="taxonomy">[category label="Posted in:"] | [tags label="Tagged:"] | With [comments_count] comments</div>';
            $looptemplate .= '</div>';
            // Construct template body
            $bodytemplate = '<div class="content-wrapper">';
            $bodytemplate .= '[loop]';
            $bodytemplate .= '</div>';
        } else {
            // Construct template loop
            $looptemplate = '<div class="content-wrapper"><div class="post">';
            $looptemplate .= '<h2>[title]</h2>';
            $looptemplate .= '<div class="meta"><p>Posted on <strong>[date]</strong> by <strong>[author]</strong></p></div>';
            $looptemplate .= '<p>[content]</p>';
            $looptemplate .= '<div class="taxonomy">[category label="Posted in:"] | [tags label="Tagged:"] | With [comments_count] comments</div>';
            $looptemplate .= '</div></div>';
            // Construct template body
            $bodytemplate = '';
            $bodytemplate .= '<div class="pdf-header">';
            $bodytemplate .= '<h1>Post List</h1>';
            $bodytemplate .= '<h2>[site_title]</h2>';
            $bodytemplate .= '<h3>[site_tagline]</h3>';
            $bodytemplate .= '[from_date label="From:"] [to_date label="To:"]';
            $bodytemplate .= '</div>';
            $bodytemplate .= '<div>[loop]</div>';
            $bodytemplate .= '';
        }
        $temp['loop'] = $looptemplate;
        $temp['body'] = $bodytemplate;
		$temp['pageheader'] = $pageheadertemplate;
		$temp['pagefooter'] = $pagefootertemplate;
        return $temp;
    }
    /*
     * Return default template
     */
    public function get_default_template() {
        if (isset($_GET['catpdf_dl'])) {
            $default_template = $this->custruct_default_template('single');
        } else {
            $default_template = $this->custruct_default_template();
        }
        $arr = array();
        $arr = array(
            'template_name' => 'Default',
            'template_loop' => $default_template['loop'],
            'template_body' => $default_template['body'],
			'template_pageheader' => $default_template['pageheader'],
			'template_pagefooter' => $default_template['pagefooter']
        );
        return (object) $arr;
    }
    /*
     * Insert to template table
     * @arr - array
     */
    public function add_this($arr = array()) {
        global $wpdb, $current_user;
        // Get user info
        get_currentuserinfo();
        $user               = $current_user;
        // Insert data
        $arr['create_date'] = current_time('mysql');
        $arr['create_by']   = $user->ID;
        $table_name         = $wpdb->prefix . "catpdf_template";
        $rows_affected      = $wpdb->insert($table_name, $arr);
    }
    /*
     * Update entry in template table
     * @data - array
     */
    public function update_this($data = array()) {
        global $wpdb;
        $where         = array(
            'template_id' => $this->post['templateid']
        );
        $table_name    = $wpdb->prefix . "catpdf_template";
        $rows_affected = $wpdb->update($table_name, $data, $where);
    }

    /*
     * Return template data
     * @id - string
     */
    public function get_template($id = NULL) {
        global $wpdb;
        $table_name = $wpdb->prefix . "catpdf_template";
        if ($id !== NULL) {
            $sql      = $wpdb->prepare("SELECT * FROM " . $table_name . " WHERE template_id = %d;", $id);
            $template = $wpdb->get_row($sql);
        } else {
            $template = $wpdb->get_results("SELECT * FROM " . $table_name);
        }
        return $template;
    }
    /*
     * Add template
     */
    public function add_template() {
        if ($this->post['templatename'] != '') {
            $data = array(
                'template_name' => $this->post['templatename'],
                'template_loop' => $this->post['looptemplate'],
                'template_body' => $this->post['bodytemplate'],
				'template_pageheader' => $this->post['pageheadertemplate'],
				'template_pagefooter' => $this->post['pagefootertemplate'],
                'template_description' => $this->post['description']
            );
            // Insert template
            $this->add_this($data);
            $this->message = array(
                'type' => 'updated',
                'message' => __('Template saved.')
            );
        } else {
            $this->message = array(
                'type' => 'error',
                'message' => __('Please provide template name.')
            );
        }
    }
    /*
     * Update template database entry
     */
    public function update_template() {
        if ($this->post['templatename'] != '') {
            $data = array(
                'template_name' => $this->post['templatename'],
                'template_description' => $this->post['description'],
                'template_body' => $this->post['bodytemplate'],
				'template_pageheader' => $this->post['pageheadertemplate'],
				'template_pagefooter' => $this->post['pagefootertemplate'],
                'template_loop' => $this->post['looptemplate']
            );
            $this->update_this($data);
            $this->message = array(
                'type' => 'updated',
                'message' => __('Template updated.')
            );
        } else {
            $this->message = array(
                'type' => 'error',
                'message' => __('Please provide template name.')
            );
        }
    }
    /*
     * Delete template entry
     */
    public function delete_template($id) {
        global $wpdb;
        $table_name = $wpdb->prefix . "catpdf_template";
        $wpdb->query("DELETE FROM " . $table_name . " WHERE template_id = " . $id);
    }

}
?>