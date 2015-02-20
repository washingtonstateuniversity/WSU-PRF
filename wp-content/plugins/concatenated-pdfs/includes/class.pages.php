<?php
/*
	Still needs a good refactor
	- actions should be moved and ?page should be detected?
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class catpdf_pages {
    public $dompdf = NULL;
    public $message = array();
    public $title = '';
	public $post_query_arr = NULL;


    function __construct() {
		global $_params;
        if (is_admin()) {
			if (isset($_params)) {
				if (isset($_params['catpdf_save_option'])) {// Check if option save is performed
					add_action('init', array( $this, 'update_options' ));// Add update option action hook
				}
				if (isset($_params['catpdf_export'])) {// Check if pdf export is performed
					add_action('init', array( $this, 'export' ));// Add export hook
				}
			}
			add_action('admin_init', array( $this, 'admin_init' ));
			add_action('admin_menu', array( $this, 'admin_menu' ));
		}
        if ( isset($_params['catpdf']) && $_params['catpdf'] == "run") {// Check if single post download is performed
            add_action('init', array( $this, 'download_posts' ));// Add download action hook
        }
    }
	
    /**
     * Initailize plugin admin part
     */
    public function admin_init() {
		global $wp_scripts;
        // Enque style and script		
        wp_enqueue_script('jquery-ui-core');
		wp_enqueue_script('jquery-effects-core'); 
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_script('jquery-ui-tabs');
		// get registered script object for jquery-ui
		$ui = $wp_scripts->query('jquery-ui-core');
	 
		// tell WordPress to load the Smoothness theme from Google CDN
		$protocol = is_ssl() ? 'https' : 'http';
		$url = "$protocol://ajax.googleapis.com/ajax/libs/jqueryui/{$ui->ver}/themes/smoothness/jquery-ui.min.css";
		wp_enqueue_style('jquery-ui-smoothness', $url, false, null);

        wp_enqueue_script('catpdf-js', CATPDF_URL . 'js/catpdf.custom.js', array('jquery'), '', 'all');
        wp_enqueue_style('catpdfport-style', CATPDF_URL . 'css/style.css', false, '1.9.0', 'all');
    }
    /**
     * Add plugin menu
     */
    public function admin_menu() {
        // Register menu
        add_menu_page(CATPDF_NAME, CATPDF_NAME, 'manage_options', CATPDF_BASE_NAME, array( $this, 'option_page' ), CATPDF_URL . 'images/nav-icon.png');
        // Register sub-menu
        add_submenu_page(CATPDF_BASE_NAME, _('Download PDF'), _('Download Builder'), 'manage_options', 'catpdf-download-pdf', array( $this, 'download_page' ));

    }

    /**
     * Display "Download" page
     */
    public function download_page() {
		global $catpdf_templates,$catpdf_data;
        $data                  = array();
        $args                  = array(
            'orderby' => 'name',
            'order' => 'ASC',
            'hierarchical' => 1,
            'hide_empty' => '0'
        );
        $options               = get_option('catpdf_options');

		
		$post_types      = get_post_types(array(
            'public'   => true,
                     //'_builtin' => false
        ),'names' , 'and' );
		$select_types= '<select name="type[]" multiple="multiple" class="postform" >';
		foreach ($post_types  as $post_type ) {
			$select_types.='<option value="'. $post_type.'"  class="level-0" >'. $post_type. '</option>';
		}
		$select_types.='</select>';


		$args = array();
		$tags = get_tags( $args );
		if(!empty($tags)){
			$select_tags= '<select name="tags[]" multiple="multiple" class="tagform" >';
			
			foreach ( $tags  as $tag ) {
				$select_tags.='<option value="'. $tag->term_id.'"  class="level-0" >'. $tag->name. '</option>';
			}
			$select_tags.='</select><input class="all-btn sept-mar" type="button" value="Select All" />';
		}else{
			$select_tags="<h5>Currently there are no taged posts.</h5>";
		}
		
        /*$select_cats           = str_replace("name='cat' id=", "name='cat[]' multiple='multiple' id=", $select_cats);
        $select_cats           = str_replace("<option", '<option ', $select_cats);*/
		
		$args = array();
		$cats = get_categories( $args );
		if(!empty($cats)){
			$select_cats= '<select name="cat[]" multiple="multiple" class="catform" >';
			
			foreach ( $cats  as $cat ) {
				$select_cats.='<option value="'. $cat->term_id.'" title="'. $cat->name. '" class="level-0" >'. $cat->name. '</option>';
			}
			$select_cats.='</select><input class="all-btn sept-mar" type="button" value="Select All" />';
		}else{
			$select_cats="<h5>Currently there are no categorized posts.</h5>";
		}
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

		$data['styles'] = array('default' => "default") + $catpdf_templates->get_styles();
        $data['select_sizes']  = array('letter' => $catpdf_data->paper_sizes['letter']) + $catpdf_data->paper_sizes;
        $data['select_ors']    = $catpdf_data->paper_orientation;
		$data['media_types']   = $catpdf_data->media_types;
        $data['option_url']    = "";//$tool_url;
        $data['templates']     = array();//$catpdf_templates->get_template();
        $data['message']       = $this->get_message();
        // Display export form
        $this->view(CATPDF_PATH . '/includes/views/export.php', $data);
    }

    /**
     * Update plugin option
     */
    public function update_options() {
		global $_params;
        $options = $_params;
        update_option('catpdf_options', json_encode($options));
    }

    /**
     * Display "Option" page
     */
    public function option_page() {
		global $catpdf_templates,$catpdf_data;
        // Set options
		$options = $catpdf_data->get_options();

        $data['options']   = $options;
		$data['dompdf_options'] = $catpdf_data->get_options();
		$data['sizes']   = array('letter' => $catpdf_data->paper_sizes['letter']) + $catpdf_data->paper_sizes;
		$data['media_types'] = $catpdf_data->media_types;
		$data['styles'] = array('default' => "default") + $catpdf_templates->get_styles();
        // Get templates
        $data['templates'] = array();//$catpdf_templates->get_template();
        // Display option form
        $this->view(CATPDF_PATH . '/includes/views/options.php', $data);
    }


    /**
     * Download post pdf
     */
    public function download_posts() {
        global $dompdf,$_params,$catpdf_output,$post,$post_query_arr,$catpdf_templates,$producing_pdf;
        $param_arr   = array(
            'from' => (isset($_params['from'])) ? urldecode($_params['from']) : '',
            'to' => (isset($_params['to'])) ? urldecode($_params['to']) : '',
            'category' => (isset($_params['cat']) && $_params['cat'] != '') ? explode(',', $_params['cat']) : array(),
            'user' => (isset($_params['user']) && $_params['user'] != '') ? explode(',', $_params['user']) : array(),
            'template' => (isset($_params['template'])) ? urldecode($_params['template']) : 'default',
			'post_type' => (isset($_params['type'])) ? urldecode($_params['type']) : 'post',
			'post_status' => (isset($_params['status'])) ? urldecode($_params['status']) : 'published'
        );
		
		$todo_list = array();
		if(isset($_params['sections']) && !empty($_params['sections'])){
			$todo_list = array_map('trim', explode(',', $_params['sections']));
		}
		
		
        $post_query_arr  = $param_arr;
		$_params['papersize']= isset($_params['papersize']) && !empty($_params['papersize']) ? $_params['papersize'] : "letter";
		$_params['orientation']= isset($_params['orientation']) && !empty($_params['orientation']) ? $_params['orientation'] : "portrait";

		$filename = trim($catpdf_output->buildFileName(null,null))."-".md5( implode(',',$_params) ) . ".pdf";
		
		if(!$catpdf_output->is_cached($filename) || isset($_params['dyno'])){
			$catpdf_templates->get_style();
			$catpdf_output->prep_output_objects();
			$catpdf_output->prep_pageheader();
			$catpdf_output->prep_pagefooter();
			$catpdf_output->_html_structure();
			
			//render all of the pdf's by section and then store the marker
			$renderedList = array();
			$template_sections = $catpdf_templates->get_default_render_order();
			foreach($template_sections as $code=>$section){
				if(!empty($todo_list) && !in_array($code,$todo_list)){
					continue;	
				}
				$producing_pdf=true;
				$part_name = call_user_func( array( $catpdf_templates, 'get_section_'.$code ) );
				$producing_pdf=false;
				$renderedList[$code]=$part_name;
			}
			
			//take the rendered output and ordering in the way the book will be put together
			$oupout_order = $catpdf_templates->get_default_template_sections();
			$merge_list = array();
			foreach($oupout_order as $code=>$section){
				if( isset($renderedList[$code]) && !empty($renderedList[$code]) ){
					if(is_array($renderedList[$code])){
						$i=0;
						foreach($renderedList[$code] as $item){
							$key=$code.$i;
							$merge_list[$key]=$item;	
							$i++;
						}
					}else{
						$merge_list[$code]=$renderedList[$code];
					}
				}
			}

			var_dump($template_sections);
			var_dump($renderedList);
			var_dump($merge_list);

			if($catpdf_output->merge_pdfs($merge_list,$filename)){
				die("before send");
				$catpdf_output->sendPdf($filename);
			}
		}else{
			$catpdf_output->sendPdf($filename);	
		}
    }



    /*-------------------------------------------------------------------------*/
    /* -General- 															   */
    /*-------------------------------------------------------------------------*/
    /**
     * Return falsh message
     */
    public function get_message() {
		global $catpdf_core;
        if (!empty($catpdf_core->message)) {
            $arr = $catpdf_core->message;
			$message = "<div id='message' class='{$arr['type']}'><p>{$arr['message']}</p></div>";
			$catpdf_core->message=NULL;
            return $message;
        }
    }
    /**
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