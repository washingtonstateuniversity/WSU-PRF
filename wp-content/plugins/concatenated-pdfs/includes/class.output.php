<?php
/*
	Still needs a good refactor
	- oh where to start
	noted below
*/
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class catpdf_output {

	public $template = NULL;
    public $post = array();
    public $title = '';
	public $head = NULL;
	
	public $header_part = NULL;
	public $footer_part = NULL;
	
    function __construct() {
		
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




	public function prep_output_objects(){
		global $catpdf_templates,$_params,$catpdf_data,$posts,$post_query_arr,$shortcode;
		$id		= isset($_params['catpdf_dl'])?$_params['catpdf_dl']:NULL;
		//var_dump($post);
		$posts 	= ($id>0) ? array(get_post($id)) : get_posts($post_query_arr) ;
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
	
	/* maybe there should be a utilities class? */
	public function pixeltopointConvertion($px){
		$point = $px * 72 / 96;
		return $point;
	}
	public function pointtopixelConvertion($point){
		$px = $point * 96 / 72;
		return $px;
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

		$pagew=$this->pointtopixelConvertion(CPDF_Adapter::$PAPER_SIZES[$_params['papersize']][2]);
		$pageh=$this->pointtopixelConvertion(CPDF_Adapter::$PAPER_SIZES[$_params['papersize']][3]);
        
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
		$options   = $catpdf_data->get_options();
		$head_html_style_sheets = "<link type='text/css' rel='stylesheet' href='" . PDF_STYLE . "'/>\n";
        if (isset($options['single']['enablecss']) && $options['single']['enablecss'] == 'on') {
            //$head_html_style_sheets .= "<link type='text/css' rel='stylesheet' href='" . get_stylesheet_uri() . "'/>\n";
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
		global $dompdf;
		$this->title = $this->buildFileName(NULL,$options);

		/* there should be a base html template? */
		$head_html = "<!DOCTYPE html>\n";
        $head_html .= "<html>\n";
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
	$pages+=$PAGE_COUNT;
	$chapters[$interation-1]["page_end"]=$pages;
	$bs = $GLOBALS["backside"]; // work to remove
	$pdf->page_script(\'$pages++;\');
	$count=$PAGE_COUNT;
	//$chapters=$GLOBALS["chapters"];
	$o=1;
	$p=1;
	foreach($pdf->get_cpdf()->objects as $obj){
		if(isset($pdf->get_cpdf()->objects[$o]["c"])){
			$content = $pdf->get_cpdf()->objects[$o]["c"];
			// using short var names as the dompdf will understand the 
			// plachole length which is a problem when trying to format
			if(strpos($content,\'{P#}\') !== false){
				$pn_text_str="PAGE";
				$pn_sep_str="/";
				if(strpos($content,\'{PTx}\') !== false){$content = str_replace( \'{PTx}\', $pn_text_str, $content );}
				if(strpos($content,\'{P#}\') !== false){$content = str_replace( \'{P#}\', $p."", $content );}
				if(strpos($content,\'{PT#}\') !== false){$content = str_replace( \'{PT#}\', $count."", $content );}
				if(strpos($content,\'{P#S}\') !== false){$content = str_replace( \'{P#S}\', $pn_sep_str, $content );}
				$p++;
			}
			$pdf->get_cpdf()->objects[$o]["c"]=$content;
			$superContent.=$content;
		}
		$o++;
	}
	
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
					.$endScript
					."</body>\n"
					."</html>\n";		
    }


	public function create_section_pdf($code,$html,$sub_name=""){
		global $_params,$catpdf_output,$inner_pdf,$section,$chapters,$repeater,$pages,$interation,$indexable;
		
		$size = (isset($_params['papersize'])) ? urldecode($_params['papersize']) : 'letter';
		$orientation = (isset($_params['orientation'])) ? urldecode($_params['orientation']) : 'portrait';
		$_name=preg_replace('/[^a-z0-9]/i', '_', $sub_name);
		$filename = trim($catpdf_output->buildFileName(null,null))."-".($_name!=""?"-$_name-":"").md5( implode(',',$_params) ) . ".pdf";

		$html=$this->head.
				$this->header_part.
				$html.
				($code!="cover"?$this->footer_part:"").
				$this->foot;

		var_dump('--------'.$code.'--------');
		//if($code=="index")var_dump($html);
		
		$dompdf = new DOMPDF();
		$dompdf->set_paper($size,$orientation);
		
		//prime any globals that will be used in the dompdf render phase
		$repeater = NULL;
		$inner_pdf=$code;
		$section=$code;
		$indexable=($code!="cover"&&$code!="index");
		$this->logHtmlOutput($html);
		//start the render
		$dompdf->load_html($html);
		$dompdf->render();
		$pdf = $dompdf->output();//store it for output

		var_dump('$pages: '.$pages);
		var_dump('$interation: '.$interation);
		var_dump('$repeater: '.$repeater);
		var_dump($chapters);
		
		

		$part_name = $code.'--'.$filename;
		$this->cachePdf( $part_name, $pdf, true );
		return $part_name;	
	}



	public function sendPdf($file,$prettyname=NULL){
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
	public function cachePdf($file,$contents,$fragment=false){
		$file = ($fragment?CATPDF_MERGING_PATH:CATPDF_CACHE_PATH).trim(trim($file,'/'));
		return file_put_contents($file, $contents);
	}
	public function is_cached($filename){
		$file = CATPDF_CACHE_PATH.trim(trim($filename,'/'));
		return file_exists($file);
	}
	public function merge_pdfs($mergeList,$output_file){
		if(count($mergeList)>1){
			$PDFMerger = new PDFMerger;
			foreach($mergeList as $file){
				$PDFMerger->addPDF(CATPDF_MERGING_PATH.$file, 'all');//'1, 3, 4'//'1-2'
			}
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





}
?>