<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
class catpdf_templates {
    public $title = '';
	public $current_template = NULL;
	public $current_style = '';
	
    function __construct() {
		global $_params;
    }

	public function get_default_render_order(){
		$sections = array(
			'content'=>"",
			'appendix'=>"",
			'index'=>"",
			'cover'=>"",
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

	/**
     * @todo set this up to accept inserted parts
	 */
	public function get_template_sections(){
		return $this->get_default_template_sections();
	}
	
	public function get_section_content(){	
		global $catpdf_output,$shortcode,$_params,$post,$posts,$producing_pdf;
		foreach($posts as $posting){
			$producing_pdf=true;
			$post     = $posting;
			$content  = $shortcode->filter_shortcodes('loop', $this->resolve_template('concat-loop.php') );
			$html     = "\n<div id='catpdf_content'>\n{$content}\n</div>\n";
			$catpdf_output->create_section_pdf("content",$html,$posting->post_title);
			$producing_pdf=false;
		}
	}	
	public function get_section_cover(){
		global $catpdf_output,$shortcode,$_params,$post,$posts;
		$cover    = $shortcode->filter_shortcodes('body',$this->resolve_template("cover.php"));
		$html     = "\n<div id='catpdf_cover'>\n{$cover}\n</div>\n";	
		$catpdf_output->create_section_pdf("cover",$html);
	}
	
	public function get_section_index(){
		global $catpdf_output,$shortcode,$_params,$post,$posts;
		$index    = "\n".'<script type="text/php">$GLOBALS["indexpage"]=$pdf->get_page_number(); $GLOBALS["backside"]=$pdf->open_object();</script>'."\n";
		$index   .= $shortcode->filter_shortcodes('index',$this->resolve_template("index-table.php"));
		$index   .= "\n".'<script type="text/php">$pdf->close_object(); </script>'."\n";
		$html     = "\n<div id='catpdf_index'>\n{$index}\n</div>\n";
		$catpdf_output->create_section_pdf("index",$html);
	}	
	public function get_section_appendix(){	
		global $catpdf_output,$shortcode,$_params,$post,$posts;
		$appendix = $shortcode->get_indexer(__(""),__("Appendix "),"false").$this->resolve_template("appendix.php");
		$html     = "\n<div id='catpdf_appendix'>\n{$appendix}\n</div>\n";
		$catpdf_output->create_section_pdf("appendix",$html);
	}
	
	

	
	
	
	
	
	
	
	
	/**
     * return template object
	 * 
	 * @return object
	 */
	public function get_current_tempate($type=NULL){
		if($this->current_template==NULL){
			$this->set_current_tempate($type);
		}
		return $this->current_template;
	}
	
	
	public function get_styles(){
		$path = get_stylesheet_directory() .'/concatenated-pdfs/';
		$data = array();
		$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::SELF_FIRST);
		foreach ($files as $file){
			$file_name = array_pop ( explode("/",strval($file)) );
			if ($file_name!="." && $file_name!=".."  && is_dir($file) === true){
				$data[] = $file_name;
			}
		}
		return $data;
	}
	
	public function set_style($style=NULL){
		global $_params,$catpdf_templates,$catpdf_data;
		if($style==NULL){
			$options   = $catpdf_data->get_options();
			if( isset($options['style']) && $options['style']!="" ){
				$this->current_style=$options['style'];
			}
		}else{
			if( in_array( $style, $this->get_styles() ) ){
				$this->current_style=$style;
			}
		}
		
	}
	public function get_style($style=NULL){
		global $_params,$catpdf_templates,$catpdf_data;
		if($this->current_style==""){
			$this->set_style( isset($_params['style'])?$_params['style']:null );
		}
		return $this->current_style;
	}	
	
	public function get_style_css(){
		$style = $this->current_style;
		$style_url = "";
		$path = get_stylesheet_directory() .'/concatenated-pdfs/';
		if(file_exists($path.$style.'/style.css')){	
			$style_url = get_stylesheet_directory_uri().'/concatenated-pdfs/' .$style .'/style.css';
		}
		return $style_url;
	}		
	
	
	
	/**
     * set template object
	 */
	public function set_current_tempate($type=NULL){
		global $_params,$catpdf_templates,$catpdf_data;
		
		
		$this->get_style();
		
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
		$style = $this->current_style;
		$overridden_template = get_stylesheet_directory() .'/concatenated-pdfs/'.( $style!="" && $style !="default" ? "$style/" : "" ).$file;
		if ( file_exists($overridden_template) ) {
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

    /**
     * Return default template structure
	 *
	 * @param string $type
	 * 
	 * @return array
     */
    public function construct_default_template($type = 'concat') {
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
    /**
     * Return default template
	 *
	 * @return object
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

}
?>