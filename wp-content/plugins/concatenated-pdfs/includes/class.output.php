<?php
/*
	Still needs a good refactor
	- oh where to start
	noted below
*/
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class catpdf_output {

	public $rendered_sections = array();

	public $template = NULL;
    public $post = array();
    public $title = '';
	public $head = NULL;
	
	public $header_part = NULL;
	public $footer_part = NULL;
	
	public $build_type='single';
	
    function __construct() {
		
    }

	public function get_build_type(){
		return $this->build_type;
	}
	public function set_build_type($build_type){
		$this->build_type=$build_type;
	}




	//should also let you call from a template level one
	public function buildFileName($template,$options){
		$filename="";
		if(isset($options['title'])){
			$title		=$options['title'];
			$filename	= str_replace('%dd', date('d'), $title);
			$filename	= str_replace('%mm', date('m'), $filename);
			$filename	= str_replace('%yyyy', date('Y'), $filename);
			$filename	= str_replace('%template', $template->template_name, $filename);
		}else{
			$filename=CATPDF_BASE_NAME . '-' . date('m-d-Y');	
		}
		return $filename;
	}


	public function logHtmlOutput($html){
		file_put_contents (CATPDF_LOG_PATH . '/pdfhtml-'.date('m-d-Y--H-i-s').'.html',$html);
	}

	public function get_posts_children($parent_id,$post_query_arr){
		
		// grab the posts children
		$posts = get_posts( array_merge($post_query_arr,array('order' => 'ASC','numberposts' => -1,'post_parent' => $parent_id, 'suppress_filters' => false )));
		$layered= array();
		// now grab the grand children
		foreach( $posts as $post ){
			$children = $this->get_posts_children($post->ID,$post_query_arr);
			$layered = array_merge($layered,array_merge(array($post), $children));
		}
		// merge in the direct descendants we found earlier
		//$layered = array_merge($layered,$posts);
		return $layered;
	}



	public function prep_output_objects(){
		global $catpdf_templates,$_params,$catpdf_data,$posts,$post_query_arr,$shortcode;
		$id		= isset($_params['catpdf_dl'])?$_params['catpdf_dl']:NULL;
		//var_dump($post);
		$posts 	= ($id>0) ? array(get_post($id)) : $this->get_posts_children(0,$post_query_arr) ;
	}

	public function prep_pageheader(){
		global $catpdf_templates,$catpdf_output,$shortcode;
		$pageheader  = $shortcode->filter_shortcodes('pageheader',$catpdf_templates->resolve_template("pageheadertemplate.php"));
		$header_section = "<div id='head_area'>\n<div class='wrap'>\n${pageheader}</div>\n</div>\n";
		$catpdf_output->header_part = $header_section;
	}
	public function prep_pagefooter(){
		global $catpdf_templates,$catpdf_output,$shortcode;
		$pagefooter  = $shortcode->filter_shortcodes('pagefooter',$catpdf_templates->resolve_template("pagefootertemplate.php"));
		$footer_section = "<div id='foot_area'>\n<div class='wrap'>\n${pagefooter}</div>\n</div>\n";					
		$catpdf_output->footer_part = $footer_section;
	}



    /**
     * Return pdf content
     * @type - string
	 * @TODO move out to template class
     */
    public function construct_template($type = NULL) {
        global $catpdf_templates,$_params,$catpdf_data,$posts,$post;
		$id		= isset($_params['catpdf_dl'])?$_params['catpdf_dl']:NULL;
		
		//var_dump($post);
		$posts 	= ($id>0) ? array(get_post($id)) : get_posts($post) ;
		//var_dump($posts);
		
        $this->template = $catpdf_templates->get_current_tempate($type);
		//var_dump($this->template);
		
		$template_sections = $catpdf_templates->get_default_render_order();
		//var_dump($template_sections);
		
		$html = "";
		$i=1;
		$c=count($template_sections);
		foreach($template_sections as $code=>$section){
			if($code=="content"){
				$sectionhtml= call_user_func( array( $catpdf_templates, 'get_section_'.$code ) );
				//var_dump($sectionhtml);
				$html.= ($sectionhtml?$sectionhtml:"").($i<$c?"\n\n<i class='page-break'></i>\n\n":"");
				$i++;
			}
		}

        $html = $this->head . $html .$this->foot;
		if($log=true){//@todo
			$this->logHtmlOutput($html);
		}
        return $html;
    }
	

		
	public function get_pdf_inline_style(){
		global $_params, $catpdf_data;
		$options   = $catpdf_data->get_options();
		$unit="px";
		$bodycolor="#F0F0F0";		//@@!!OPTION REPLACE
		
		$topMargin="15";			//@@!!OPTION REPLACE
		$headHeight="50";			//@@!!OPTION REPLACE
		$headSep="15";				//@@!!OPTION REPLACE

		$bottomMargin="15";			//@@!!OPTION REPLACE
		$footHeight="45";			//@@!!OPTION REPLACE
		$footSep="10";				//@@!!OPTION REPLACE
		$pagerightMargin="15";		//@@!!OPTION REPLACE
		$pageleftMargin="15";		//@@!!OPTION REPLACE

		$pagew=$catpdf_data->pointtopixelConvertion(CPDF_Adapter::$PAPER_SIZES[$_params['papersize']][2]);
		$pageh=$catpdf_data->pointtopixelConvertion(CPDF_Adapter::$PAPER_SIZES[$_params['papersize']][3]);
        
		//calculated values needed for the pdf
		$footSkip=($footHeight+$footSep);//equal to bottom:{VAL}px
		$pageHeadMargin= ($topMargin+$headHeight+$headSep);
		$pageFootMargin=($bottomMargin+$footSkip);
		$textBoxingWidth=$pagew-$pagerightMargin-$pageleftMargin;
		
		$page_padding="{$pageHeadMargin}{$unit} {$pagerightMargin}{$unit} {$pageFootMargin}{$unit} {$pageleftMargin}{$unit}";
		//set up the base style that make it easy to fomate it.
        $head_style = '<!-- built from the php and are important to try not to write over if possible -->
<style>
	html,body { position: relative; }
	/*@page{}*/
	#head_area{ left:'.$pageleftMargin.$unit.'; top:'.$topMargin.$unit.'; height:'.$headHeight.$unit.'; /*width:'.$textBoxingWidth.$unit.';*/ }
	#head_area .wrap{ height:'.$headHeight.$unit.';}
	#foot_area{ left:'.$pageleftMargin.$unit.'; bottom:'.$bottomMargin.$unit.'; height:'.$footHeight.$unit.'; /*width:'.$textBoxingWidth.$unit.';*/ }
	#foot_area .wrap{ height:'.$footHeight.$unit.'; }
	body {padding:'.$page_padding.';} /*note that the body is used as our sudo "page" it is our saffal base*/
	
	' . strip_tags($options['single']['customcss']) . ' 
</style>';
		return $head_style;


	}

	public function build_stylesheets(){
		global $_params, $dompdf, $catpdf_data, $catpdf_templates;
		
		$build_type = $this->get_build_type();
		
		$options   = $catpdf_data->get_options();
		$head_html_style_sheets = "<link type='text/css' rel='stylesheet' href='" . PDF_STYLE . "'/>\n";
        if (isset($options[$build_type]['enablecss']) && $options[$build_type]['enablecss'] == 'true') {
            $head_html_style_sheets .= "<link type='text/css' rel='stylesheet' href='" . get_stylesheet_uri() . "'/>\n";
        }
        $get_style_css    = $catpdf_templates->get_style_css();
		if ($get_style_css!="") {
            $head_html_style_sheets .= "<link type='text/css' rel='stylesheet' href='" . $get_style_css. "'/>\n";
        }	
		return $head_html_style_sheets;
	}



	public function get_pdf_php_globals(){
		return 'global $_params,$catpdf_output,$inner_pdf,$section,$interation,$chapters,$repeater,$pages,$indexable;';	
	}
	
	
    /**
     * Return html structure
     */
    public function _html_structure() {
		global $dompdf,$catpdf_output;
		$this->title = $this->buildFileName(NULL,$options);

		/* there should be a base html template? */
		$head_html = "<!DOCTYPE html>\n";
        $head_html .= '<html dir="ltr" lang="en">'."\n";
        $head_html .= "<meta charset='UTF-8' />\n";
        $head_html .= '<title>' . $this->title . "</title>\n";

		//sets up the globals for the rendered inline php 
		$indexscriptglobals="\n".''."\n";
		$script="";

        $this->head  = $head_html
						.$this->build_stylesheets()
						.$this->get_pdf_inline_style()
						."</head>\n"
						."<body>\n"
						.$indexscriptglobals
						.$script;
						
		$indexer = '<script type="text/php">
	'.$this->get_pdf_php_globals().'
	//$repeater = $inner_pdf;
	$startingPage = $pages;
	if($interation == ""){
		$interation=1;	
	}
	$pages+=$PAGE_COUNT;
	/*if($indexable){
		$page_end=$pages;
		$chapters[$interation-1]["page_end"]=$pages;
	}*/
	$bs = $GLOBALS["backside"]; // work to remove
	$pdf->page_script(\'$pages++;\');
	$count=$PAGE_COUNT;
	//$chapters=$GLOBALS["chapters"];
	//$repeater = $superContent;
	
	//page_script seems to need to be oneline?
	$pdf->page_script(\'$indexpage=$GLOBALS["indexpage"]; if ($PAGE_NUM==$indexpage ) { $pdf->add_object($GLOBALS["backside"],"add"); $pdf->stop_object($GLOBALS["backside"]); }\');
</script><script type="text/php">
		'.$this->get_pdf_php_globals().'
		if($indexable){
			$GLOBALS["indexpage"]=$pages;
			
		}
		//$repeater = $PAGE_COUNT;
		
		</script>'."\n";

		$endScript='<script type="text/javascript">
app.beep(0);
var inch = 92;
		for (var p = 0; p < this.numPages; p++) { 
		 // put a rectangle at .5 inch, .5 inch 
		  var aRect = this.getPageBox( {nPage: p} ); 
		  aRect[0] += .5*inch;// from upper left corner of page 
		  aRect[2] = aRect[0]+.5*inch; // Make it .5 inch wide 
		  aRect[1] -= .5*inch; 
		  aRect[3] = aRect[1] - .5*inch; // and .5 inch high 
		  var f = this.addField("p."+p, "text", p, aRect ); 
		  f.textSize = 20;  // 20-pt type
		  f.textColor = color.blue; // use whatever color you want
		  f.strokeColor = color.white; 
		  f.textFont = font.Helv; 
		  f.value = String(p+1);  // page numbering is zero-based
		  f.readonly = true; 
		}</script>';
        $this->foot = $indexer
					//.$endScript
					."</body>\n"
					."</html>\n";		
    }

	public function set_section($code,$object,$segment){
		global $rendered_sections;
		if($segment!=""){
			if(!is_array($rendered_sections[$code])){
				$rendered_sections[$code]=array();	
			}
			$rendered_sections[$code][$segment]=$object;
		}else{
			$rendered_sections[$code]=$object;
		}
	}

	public function get_section($code,$segment=""){
		global $rendered_sections;
		if($segment!=""){
			return $rendered_sections[$code][$segment];
		}else{
			return $rendered_sections[$code];
		}
	}




	public function create_sections($todo_list){		
		global $_params,$catpdf_templates,$catpdf_output,$catpdf_data,$inner_pdf,$section,$chapters,$repeater,$pages,$interation,$indexable,$rendered_sections;
		
		$catpdf_templates->get_style();
		$catpdf_output->prep_output_objects();
		$catpdf_output->prep_pageheader();
		$catpdf_output->prep_pagefooter();
		$catpdf_output->_html_structure();
		
		if($interation==""){$interation=1;}//idk atm why this is needed other then it was "" at this point
		$template_sections = $catpdf_templates->get_default_render_order();
		foreach($template_sections as $code=>$section){
			if(!empty($todo_list) && !in_array($code,$todo_list)){
				continue;	
			}
			
			call_user_func( array( $catpdf_templates, 'get_section_'.$code ) );
			
		}
		
		//var_dump('$pages: '.$pages);
		//var_dump('ending $interation: '.$interation);
		//var_dump('$repeater: '.$repeater);
		//var_dump($chapters);
	}



	public function order_sections(){
		global $rendered_sections,$catpdf_templates;
		
		$renderedList = $rendered_sections;
		
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
		$rendered_sections = $merge_list;
	}





	public function create_section_pdf($code,$html,$segment=""){
		global $_params,$post,$shortcode,$catpdf_output,$catpdf_data,$inner_pdf,$section,$chapters,$repeater,$pages,$interation,$indexable,$rendered_sections;
		
		$fragment_key = $code.md5( implode(',',$_params) ).( $segment!="" ? md5( $post->post_modified ) : "" );
		$_name=preg_replace('/[^a-z0-9]/i', '_', $segment);
		$filename = trim($catpdf_output->buildFileName(null,null))."-".($_name!=""?"-$_name-":"").$fragment_key. ".pdf";

		if(!$this->is_cached($fragment_key,true)){
			$build_type = $this->get_build_type();
			$options   = $catpdf_data->get_options();
	
			$size = (isset($_params['papersize'])) ? urldecode($_params['papersize']) : $options['DOMPDF_DEFAULT_PAPER_SIZE'];
			$orientation = (isset($_params['orientation'])) ? urldecode($_params['orientation']) : $options['DOMPDF_DEFAULT_ORIENTATION'];
			

			$html=$this->head.
					$this->header_part.
					( ($code!="cover" && $code!="index") ?$this->footer_part:"").
					$html.
					($segment!=""?$shortcode->get_indexer($segment):"").
					$this->foot;
	
			//var_dump('--------'.$code.'--------');
			//if($code=="index")var_dump($html);
			//var_dump($html);die();
			$dompdf = new DOMPDF();
			$dompdf->set_paper($size,$orientation);
			
			//prime any globals that will be used in the dompdf render phase
			$repeater = NULL;
			$inner_pdf=$code;
			$section=$code;
			$indexable=($code!="cover" && $code!="index");
			//var_dump('pre render '.$code.' $interation: '.$interation);
			$this->logHtmlOutput($html);
			//start the render
			$dompdf->load_html($html);
			$dompdf->render();
			//var_dump('post render '.$code.' $interation: '.$interation);
			
			if ( $_dompdf_show_warnings ) {
				global $_dompdf_warnings;
				foreach ($_dompdf_warnings as $msg){
					echo $msg . "\n";
				}
				echo $dompdf->get_canvas()->get_cpdf()->messages;
				flush();
			}
			$pdf = $dompdf->output( array("compress" => 0) );//store it for output
			//var_dump($pdf);die();
			$this->cacheFragment($fragment_key,$pdf);
		}else{
			$pdf = $this->getFragment($fragment_key);
		}

		$part_key = $code.'--'.$filename;
		$this->set_section($code, (object)array(
			"content"=>$pdf,
			"filename"=>$part_key,
			"data"=>array()
		),$segment);
	}
	
	
	
	public function leadingChr($str, $spaces, $chr="0"){
		$base =  "";
		for($i = 0; $i < $spaces; $i++) { 
			$base .= $chr;
		}
		$charLength=(strlen($base) - strlen($str));
		return substr( $base , 0, $charLength ).$str;
	}

	public function filter_sections(){
		global $rendered_sections,$catpdf_data;
		//chr(0x200B) is used as it's a no width space, and well that is what we want

		$pn_text_str="PAGE";
		$pn_sep_str="/";
		$page_num = $catpdf_data->page_num_placeholder;

		$page_total = $catpdf_data->page_total_placeholder;
		$pn_space_count = strlen($page_num);
		$pt_space_count = strlen($page_total);

		// do the page numbering
		static $idx = 1;
		foreach($rendered_sections as $key=>$section){
			if(is_array($section)){
				foreach($section as $subkey=>$area){
					$rendered_sections[$key][$subkey]->data['firstpage']=$idx;
					$rendered_sections[$key][$subkey]->content = preg_replace_callback("/".preg_quote($page_num)."/", function ($matches) use (&$idx, $pn_space_count) {
						$replacement = '';
						foreach ($matches as $match) {
							$replacement = $this->leadingChr($idx,$pn_space_count,chr(0x200B));
							$idx++;
						}
						return $replacement;
					}, $rendered_sections[$key][$subkey]->content);
					$rendered_sections[$key][$subkey]->data['lastpage']=$idx-1;
				}
			}else{
				$rendered_sections[$key]->data['firstpage']=$idx;
				$rendered_sections[$key]->content = preg_replace_callback("/".preg_quote($page_num)."/", function ($matches) use (&$idx, $pn_space_count) {
					$replacement = '';
					foreach ($matches as $match) {
						$replacement = $this->leadingChr($idx,$pn_space_count,chr(0x200B));
						$idx++;
					}
					return $replacement;
				}, $rendered_sections[$key]->content);
				$rendered_sections[$key]->data['lastpage']=$idx-1;	
			}
		}
		$last_index = $idx-1;
		foreach($rendered_sections as $key=>$section){
			if(is_array($section)){
				foreach($section as $subkey=>$area){
					$rendered_sections[$key][$subkey]->content = str_replace( $page_total, $this->leadingChr($last_index,$pt_space_count,chr(0x200B)), $rendered_sections[$key][$subkey]->content );
				}
			}else{
				$rendered_sections[$key]->content = str_replace( $page_total, $this->leadingChr($last_index,$pt_space_count,chr(0x200B)), $rendered_sections[$key]->content );
			}
		}
	}


	public function section_to_pdf($code,$segment=""){
		$object = $this->get_section($code,$segment);
		$this->cachePdf( $object->filename, $object->content, true );
	}


	public function getFragment($file){
		$file = CATPDF_MERGING_PATH.trim(trim($file,'/'));
		return file_get_contents($file);
	}	
	public function cacheFragment($file,$contents){
		return $this->cachePdf($file,$contents,true);
	}	
	public function cachePdf($file,$contents,$fragment=false){
		$file = ($fragment?CATPDF_MERGING_PATH:CATPDF_CACHE_PATH).trim(trim($file,'/'));
		return file_put_contents($file, $contents);
	}

	public function is_cached($filename,$fragment=false){
		$file = ($fragment?CATPDF_MERGING_PATH:CATPDF_CACHE_PATH).trim(trim($filename,'/'));
		return file_exists($file);
	}
	
	public function build_pdf_sections(){
		global $rendered_sections;
		foreach($rendered_sections as $item){
			if(is_array($item)){
				foreach($item as $subitem){
					$this->cachePdf($subitem->filename,$subitem->content,true);
				}
			}else{
				$this->cachePdf($item->filename,$item->content,true);
			}
		}
	}
	
	public function merge_pdfs($output_file){
		global $rendered_sections;
		$mergeList = array();
		foreach($rendered_sections as $item){
			if(is_array($item)){
				foreach($item as $subitem){
					$mergeList[]=$subitem->filename;
				}
			}else{
				$mergeList[]=$item->filename;
			}
		}
		//var_dump($mergeList);
		if(count($mergeList)>1){
			$PDFMerger = new PDFMerger;
			foreach($mergeList as $file){
				//var_dump("--".CATPDF_MERGING_PATH.$file);
				$PDFMerger->addPDF(CATPDF_MERGING_PATH.$file, 'all');//'1, 3, 4'//'1-2'
			}
			//var_dump(CATPDF_CACHE_PATH.trim(trim($output_file),'/'));
			$PDFMerger->merge('file', CATPDF_CACHE_PATH.trim(trim($output_file),'/'));
			return true;
		}else{
			if (!copy(CATPDF_MERGING_PATH.$mergeList[0], CATPDF_CACHE_PATH.trim(trim($output_file),'/')) ) {
				echo "failed to copy ".CATPDF_MERGING_PATH.$mergeList[0]." to ". CATPDF_CACHE_PATH.'/'.$output_file."...\n";
				return false;
			}
			return true;
		}
	}


	public function sendPdf($file,$prettyname=NULL){
		global $_params;
		if(!$this->is_cached($file) || isset($_params['dyno'])){
			$todo_list = array();
			if(isset($_params['sections']) && !empty($_params['sections'])){
				$todo_list = array_map('trim', explode(',', $_params['sections']));
			}
			$this->create_sections($todo_list);
			$this->order_sections();
			$this->filter_sections();
			$this->build_pdf_sections();
			$this->merge_pdfs($file);
		}

		$name = $prettyname==NULL?$file:$prettyname;
		$file=CATPDF_CACHE_PATH.$file;
		if (file_exists($file)){
			if(false !== ($hanlder = fopen($file,"r"))){
				header('Content-Description: File Transfer');
				header('Content-Type: application/octet-stream');
				header('Content-Disposition: attachment; filename='.$name);
				header('Content-Transfer-Encoding: binary');
				header('Expires: 0');
				header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
				header('Pragma: public');
				header('Content-Length: ' . filesize($file)); //Remove
				/*//Send the content in chunks
				while(false !== ($chunk = fread($handler,4096))){
					echo $chunk;
				}*/
				ob_clean();
				flush();
				readfile($file);
				exit;
			} //out put error message
		} //out put error message
	}


}
?>