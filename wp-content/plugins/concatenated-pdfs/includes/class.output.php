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
		file_put_contents (CATPDF_PATH . '/pdfhtml.html',$html);
	}



	//temp remove asap
	public function getTitle($post){
		setup_postdata($post);
		$item = get_the_title();
		return $item;
	}







    /**
     * Return pdf content
     * @type - string
	 * @TODO move out to template class
     */
    public function construct_template($type = NULL) {
        global $catpdf_templates,$_params,$catpdf_data,$posts,$post;
		$id		= isset($_params['catpdf_dl'])?$_params['catpdf_dl']:NULL;
		
		$posts 	= ($id>0) ? get_posts($post) : array(get_post($id));
		var_dump($posts);
		
        $this->template = $catpdf_templates->get_current_tempate($type);
		//var_dump($this->template);
		
		$template_sections = $catpdf_templates->get_default_render_order();
		//var_dump($template_sections);
		
		$html = "";
		$i=1;
		$c=count($template_sections);
		foreach($template_sections as $code=>$section){
			$sectionhtml= call_user_func( array( $catpdf_templates, 'get_section_'.$code ) );
			//var_dump($sectionhtml);
			$html.= ($sectionhtml?$sectionhtml:"").($i<$c?"\n\n<i class='page-break'></i>\n\n":"");
			$i++;
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
		
	
	
    /**
     * Return html structure
     */
    public function _html_structure() {
		global $_params, $dompdf, $catpdf_data;
		
		if (empty($this->template)) return false; 
		
        $options   = $catpdf_data->get_options();
		$unit="px";
		$bodycolor="#F0F0F0";		//@@!!OPTION REPLACE
		
		$topMargin="15";			//@@!!OPTION REPLACE
		$headHeight="50";			//@@!!OPTION REPLACE
		$headSep="15";				//@@!!OPTION REPLACE

		$bottomMargin="15";			//@@!!OPTION REPLACE
		$footHeight="45";			//@@!!OPTION REPLACE
		$footSep="10";				//@@!!OPTION REPLACE
		$pagerightMargin="15";	//@@!!OPTION REPLACE
		$pageleftMargin="15";		//@@!!OPTION REPLACE
		
		
		
		$pagew=$this->pointtopixelConvertion(CPDF_Adapter::$PAPER_SIZES[$_params['papersize']][2]);
		$pageh=$this->pointtopixelConvertion(CPDF_Adapter::$PAPER_SIZES[$_params['papersize']][3]);
        
		$template    = $this->template;
		
		$this->title = $this->buildFileName($template,$options);
		$pageheader  = $this->filter_shortcodes('pageheader');
		$pagefooter  = $this->filter_shortcodes('pagefooter');//$this->filter_shortcodes('pagefooter');

		
		/* there should be a base html template? */
		$head_html = "<!DOCTYPE html>\n";
        $head_html .= "<html>\n";
        $head_html .= "<meta charset='UTF-8' />\n";
        $head_html .= '<title>' . $this->title . "</title>\n";
		
		$head_html_style_sheets = "";
		
        if (isset($options['single']['enablecss']) && $options['single']['enablecss'] == 'on') {
            $head_html_style_sheets .= "<link type='text/css' rel='stylesheet' href='" . get_stylesheet_uri() . "'/>\n";
        }
        $head_html_style_sheets .= "<link type='text/css' rel='stylesheet' href='" . PDF_STYLE . "'/>\n";
		
		
		
		
        $head_html_closing_tag = "</head>\n";
		
		//calculated values needed for the pdf
		$footSkip=($footHeight+$footSep);//equal to bottom:{VAL}px

		$pageHeadMargin= ($topMargin+$headHeight+$headSep);
		$pageFootMargin=($bottomMargin+$footSkip);
		$textBoxingWidth=$pagew-$pagerightMargin-$pageleftMargin;
		
		$page_padding="{$pageHeadMargin}{$unit} {$pagerightMargin}{$unit} {$pageFootMargin}{$unit} {$pageleftMargin}{$unit}";
		
		$bodyOpenTag = "<body>\n";
		$header_section = "<div id='head_area'>\n<div class='wrap'>\n${pageheader}</div>\n</div>\n";
		$footer_section = "<div id='foot_area'>\n<div class='wrap'>\n${pagefooter}</div>\n</div>\n";
		
		//sets up the globals for the rendered inline php 
		$indexscriptglobals="\n".'<script type="text/php"> $GLOBALS["i"]=1; $GLOBALS["indexpage"]=0; $GLOBALS["chapters"] = array(); </script>'."\n";
		$script="";
		
		//set up the base style that make it easy to fomate it.
        $head_style = '<!-- built from the php and are important to try not to write over if possible -->
	<style>
		html,body { /*background-color:'.$bodycolor.';*/ position: relative; }
		/*@page{}*/
		#head_area{ left:'.$pageleftMargin.$unit.'; top:'.$topMargin.$unit.'; height:'.$headHeight.$unit.'; /*width:'.$textBoxingWidth.$unit.';*/ }
		#head_area .wrap{ height:'.$headHeight.$unit.';}
		#foot_area{ left:'.$pageleftMargin.$unit.'; bottom:'.$bottomMargin.$unit.'; height:'.$footHeight.$unit.'; /*width:'.$textBoxingWidth.$unit.';*/ }
		#foot_area .wrap{ height:'.$footHeight.$unit.'; }
		body {padding:'.$page_padding.';} /*note that the body is used as our sudo "page" it is our saffal base*/
		
		' . strip_tags($options['single']['customcss']) . ' 
	</style>';
        $this->head  = $head_html
						.$head_html_style_sheets
						.$head_style
						.$head_html_closing_tag
						.$bodyOpenTag
						.$header_section
						.$footer_section
						.$indexscriptglobals
						.$script;
		$indexer = '
<script type="text/php">
	$bs = $GLOBALS["backside"]; // work to remove

	$count=$pdf->get_page_number();
	$chapters=$GLOBALS["chapters"];
	$o=1;
	$p=1;
	foreach($pdf->get_cpdf()->objects as $obj){
		foreach ($chapters as $chapter => $page) {
			if(isset($pdf->get_cpdf()->objects[$o]["c"])){
				$content = $pdf->get_cpdf()->objects[$o]["c"];
				$chapter_str	= "Chapter ".$chapter." ";
				$pagenumber_str	= " p: ".$page["page"];
				$text_str		= $page["text"]." ";
				if(strpos($content,\'{chapter\') !== false){
					$content = str_replace( \'{chapter\'.$chapter.\'}\' , $chapter_str, $content );
					$content = str_replace( \'{page\'.$chapter.\'}\' , $pagenumber_str, $content );
					$content = str_replace( \'{text\'.$chapter.\'}\' , $text_str, $content );
				}
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
			}
		} 
		$o++;
	}
	//page_script seems to need to be oneline?
	$pdf->page_script(\'$indexpage=$GLOBALS["indexpage"]; if ($PAGE_NUM==$indexpage ) { $pdf->add_object($GLOBALS["backside"],"add"); $pdf->stop_object($GLOBALS["backside"]); }\');
</script>'."\n";

$bodyCloseTag='<script type="text/javascript">
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
        $bodyCloseTag.="</body>\n";
		$htmlCloseTag="</html>\n";
					
		$bottomHtml = $indexer
					.$bodyCloseTag
					.$htmlCloseTag;		
        $this->foot = $bottomHtml;
    }
    /**
     * Return html with filtered shortcodes
     * @tmp_type - string
	 * needs to be reworked
	 * also move to class.shortcuts
     */
    public function filter_shortcodes($tmp_type=NULL,$html=null) {
		if($tmp_type==NULL) return false;

        $template      = $this->template;
		//var_dump($template);
        $pattern       = get_shortcode_regex();

		$arr = array_keys(shortcode::get_template_shortcodes(!empty($tmp_type)?$tmp_type:'body')); //? was ? isset($items[$tmp_type])?$items[$tmp_type]:$items['body'] into get_template_shortcodes
		if($html==null){
			$tmp_sec = "template_{$tmp_type}";
			$tmp = $template->$tmp_sec;
		}else{
			$tmp = $html;	
		}
		//var_dump($tmp_type);
		//var_dump($arr);
        preg_match_all('/' . $pattern . '/s', $tmp, $matches);
        $html = $tmp;
        foreach ($arr as $code) {
            if (is_array($matches) && in_array($code, $matches[2])) {
                foreach ($matches[0] as $match) {
                    $html = str_replace($match, do_shortcode($match), $html);
                }
            }
        }
		//var_dump($html);
		//if(!in_array($tmp_type,array('pageheader','pagefooter')))die();
        return $html;
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
	public function cachePdf($file,$contents){
		$file = CATPDF_CACHE_PATH.trim(trim($file,'/'));
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
				$PDFMerger->addPDF(CATPDF_CACHE_PATH.'merging_stage/'.$file, 'all');//'1, 3, 4'//'1-2'
			}
			$PDFMerger->merge('file', CATPDF_CACHE_PATH.trim(trim($output_file),'/'));
		}else{
			if (!copy(CATPDF_CACHE_PATH.'merging_stage/'.$mergeList[0], CATPDF_CACHE_PATH.trim(trim($output_file),'/')) ) {
				echo "failed to copy ".CATPDF_CACHE_PATH.'merging_stage/'.$mergeList[0]." to ". CATPDF_CACHE_PATH.'/'.$output_file."...\n";
			}
		}
	}





}
?>