<?php

/*
	Still needs a good refactor
	- sections should be abstracted to subplugin style
*/


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class catpdf_templates {
    public $title = '';
	public $current_template = NULL;
    function __construct() {
		global $_params;
        if (is_admin()) {
			if (isset($_params)) {
				// Check if template save is performed
				if (isset($_params['catpdf_save'])) {
					if ($_params['templateid'] == '') {
						// Add save template action hook
						add_action('init', array($this, 'add_template' ));
					} else {
						// Add update template action hook
						add_action('init', array( $this, 'update_template' ));
					}
				}
			}
		}  
    }

	public function get_default_render_order(){
		$sections = array(
			'cover'=>"",
			'index'=>"",
			'content'=>"",
			'appendix'=>"",
		);
		return $sections;
	}
	
	public function get_default_template_sections(){
		$sections = array(
			'cover'=>"",
			'index'=>"",
			'content'=>"",
			'appendix'=>"",
		);
		return $sections;
	}

	/*
	* @todo set this up to accept inserted parts
	*/
	public function get_template_sections(){
		return $this->get_default_template_sections();
	}
	
	public function get_section_content(){	
		global $catpdf_output;
		
		$catpdf_output->_html_structure();
		$content 		= $catpdf_output->filter_shortcodes('body');
		$contentHtml	= "<div id='catpdf_content'>{$content}</div>\n";	
		//var_dump($contentHtml);die();
		return $contentHtml;
	}	
	public function get_section_cover(){
		$cover			= "<h1 class='CoverTitle'>Cover Letter</h1>\n";
		$coverHtml 		= "<div id='catpdf_cover'>{$cover}</div>\n";	
		return $coverHtml;
	}
	
	public function get_section_index(){
		global $posts,$catpdf_output;
		$index='<script type="text/php">$GLOBALS["indexpage"]=$pdf->get_page_number(); $GLOBALS["backside"]=$pdf->open_object();</script>'."\n";
		$table = $this->resolve_template("index-table.php");
		$index.=$catpdf_output->filter_shortcodes('index',$table);
		$index.='<script type="text/php">$pdf->close_object(); </script>'."\n";
		$indexHtml="<div id='catpdf_index'>{$index}</div>";
		
		//var_dump($indexHtml);die();
		
		return $indexHtml;
	}	
	public function get_section_appendix(){	
		$appendix			= "<h1 class='CoverTitle'>appendix</h1>";
		$appendixHtml 		= "<div id='catpdf_appendix'>{$appendix}</div>";	
		return $appendixHtml;
	}
	
	/*
	* return template object
	*/
	public function get_current_tempate($type=NULL){
		if($this->current_template==NULL)$this->set_current_tempate($type);
		return $this->current_template;
	}
	/*
	* set template object
	*/
	public function set_current_tempate($type=NULL){
		global $_params,$catpdf_templates,$catpdf_data;
		$curr_temp = isset($_params['template'])?$_params['template']:null;
		
		$options   = $catpdf_data->get_options();
		
		if($curr_temp==null){
			if ($type == 'single') {
				$curr_temp = $options['single']['dltemplate'];
			}else{
				$curr_temp = $options['concat']['dltemplate'];
			}
		}

        if ($curr_temp == 'default') {
            $template = $catpdf_templates->get_default_template();
        } else {
            $template = $catpdf_templates->get_template($curr_temp);
        }
		$this->current_template = $template;
	}

	public function resolve_template($file){
		$html = "";
		if ( $overridden_template = locate_template( $file ) ) {
			// locate_template() returns path to file
			// if either the child theme or the parent theme have overridden the template
			$html = file_get_contents( $overridden_template );
		} else {
			// If neither the child nor parent theme have overridden the template,
			// we load the template from the 'templates' sub-directory of the directory this file is in
			$html = file_get_contents( CATPDF_PATH . '/templates/'.$file );
		}
		return $html;
	}

    /*
     * Return default template structure
     */
    public function construct_default_template($type = 'all') {
        $temp         = array();
        $temp['name'] = 'Default';

		// Construct template loop
		$pageheadertemplate = $this->resolve_template("pageheadertemplate.php");
		$pagefootertemplate = $this->resolve_template("pagefootertemplate.php");

		// Construct template loop
		$looptemplate = $this->resolve_template($type."-loop.php");
		// Construct template body
		$bodytemplate = $this->resolve_template($type."-body.php");

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
            $default_template = $this->construct_default_template('single');
        } else {
            $default_template = $this->construct_default_template();
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
        global $wpdb,$catpdf_core,$_params;
        $where         = array(
            'template_id' => $_params['templateid']
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
		global $catpdf_core,$_params;
        if ($_params['templatename'] != '') {
            $data = array(
                'template_name' => $_params['templatename'],
                'template_loop' => $_params['looptemplate'],
                'template_body' => $_params['bodytemplate'],
				'template_pageheader' => $_params['pageheadertemplate'],
				'template_pagefooter' => $_params['pagefootertemplate'],
                'template_description' => $_params['description']
            );
            // Insert template
            $this->add_this($data);
            $catpdf_core->message = array(
                'type' => 'updated',
                'message' => __('Template saved.')
            );
        } else {
            $catpdf_core->message = array(
                'type' => 'error',
                'message' => __('Please provide template name.')
            );
        }
    }
    /*
     * Update template database entry
     */
    public function update_template() {
		global $catpdf_core,$_params;
        if ($_params['templatename'] != '') {
            $data = array(
                'template_name' => $_params['templatename'],
                'template_description' => $_params['description'],
                'template_body' => $_params['bodytemplate'],
				'template_pageheader' => $_params['pageheadertemplate'],
				'template_pagefooter' => $_params['pagefootertemplate'],
                'template_loop' => $_params['looptemplate']
            );
            $this->update_this($data);
            $catpdf_core->message = array(
                'type' => 'updated',
                'message' => __('Template updated.')
            );
        } else {
            $catpdf_core->message = array(
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