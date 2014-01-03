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







    /*
     * Return pdf content
     * @type - string
	 * @TODO move out to template class
     */
    public function custruct_template($type = NULL) {
        global $catpdf_templates,$_params,$catpdf_data,$posts;
		$id		= isset($_GET['catpdf_dl'])?$_GET['catpdf_dl']:NULL;
		$posts 	= $catpdf_data->query_posts($id);

        $this->template = $catpdf_templates->get_current_tempate($type);		
		$template_sections = $catpdf_templates->get_template_sections();
		$html = "";
		$i=1;
		$c=count($template_sections);
		foreach($template_sections as $code=>$section){
			$sectionhtml=call_user_func(array($catpdf_templates, 'get_section_'.$code));
			//var_dump($sectionhtml);
			$html.= ($sectionhtml?$sectionhtml:"").($i<$c?"<i class='page-break'></i>":"");
			$i++;
		}
		//"<i class='page-break'></i>"

        $html = $this->head . $html .$this->foot;
		$this->logHtmlOutput($html);
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
		
	
	
    /*
     * Return html structure
     */
    public function _html_structure() {
		global $_params, $dompdf;
		
		if (empty($this->template)) return false; 
		
        $options   = get_option('catpdf_options');
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
		$this->title       = $this->buildFileName($template,$options);
		$pageheader  = $this->filter_shortcodes('pageheader');
		$pagefooter  = $this->filter_shortcodes('pagefooter');//$this->filter_shortcodes('pagefooter');

		
		/* there should be a base html template? */
		$head_html = '<!DOCTYPE html>';
        $head_html .= '<html>';
        $head_html .= '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';
        $head_html .= '<title>' . $this->title . '</title>';
        if (isset($options['enablecss']) && $options['enablecss'] == 'on') {
            $head_html .= '<link type="text/css" rel="stylesheet" href="' . get_stylesheet_uri() . '">';
        }
        $head_html .= '<link type="text/css" rel="stylesheet" href="' . PDF_STYLE . '">';
        $head_html_tag = '</head>';
		
		//calculated values needed for the pdf
		$footSkip=($footHeight+$footSep);//equal to bottom:{VAL}px

		$pageHeadMargin= ($topMargin+$headHeight+$headSep);
		$pageFootMargin=($bottomMargin+$footSkip);
		$textBoxingWidth=$pagew-$pagerightMargin-$pageleftMargin;
		
		$page_padding="{$pageHeadMargin}{$unit} {$pagerightMargin}{$unit} {$pageFootMargin}{$unit} {$pageleftMargin}{$unit}";
		
		$bodyOpenTag = '<body>';
		$header_section = '<div id="head_area"><div class="wrap">'.$pageheader.'</div></div>';
		$footer_section = '<div id="foot_area"><div class="wrap">'.$pagefooter.'</div></div>';
		
		//sets up the globals for the rendered inline php 
		$indexscriptglobals='<script type="text/php">
			$GLOBALS["i"]=1;
			$GLOBALS["indexpage"]=0;
			$GLOBALS["chapters"] = array();
		</script>';
$script="";
		
		//set up the base style that make it easy to fomate it.
        $head_style = '<!-- built from the php and are important to 
							try not to write over if possiable -->
		<style>
			html,body { background-color:'.$bodycolor.'; position: relative; }
			@page{}
			#head_area{ position:fixed;left:'.$pageleftMargin.$unit.';top:'.$topMargin.$unit.';height:'.$headHeight.$unit.'; width:'.$textBoxingWidth.$unit.'; }
			#head_area .wrap{position:relative; width:100%; height:'.$headHeight.$unit.';}
			#foot_area{ position:fixed;left:'.$pageleftMargin.$unit.';bottom:'.$bottomMargin.$unit.';height:'.$footHeight.$unit.';width:'.$textBoxingWidth.$unit.';}
			#foot_area .wrap{position:relative; width:100%; height:'.$footHeight.$unit.';}
			body {padding:'.$page_padding.';}/*note that the body is used as our sudo "page" it is our saffal base*/
			i.page-break {page-break-after: always;border: 0;}
			' . strip_tags($options['customcss']) . '
		</style>';

        $this->head  = $head_html
						.$head_style
						.$head_html_tag
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
</script>';
        $bodyCloseTag='</body>';
		$htmlCloseTag='</html>';
					
		$bottomHtml = $indexer
					.$bodyCloseTag
					.$htmlCloseTag;		
        $this->foot = $bottomHtml;
    }
    /*
     * Return html with filtered shortcodes
     * @tmp_type - string
	 * needs to be reworked
	 * also move to class.shortcuts
     */
    public function filter_shortcodes($tmp_type=NULL) {
		if($tmp_type==NULL) return false;
        $items         = array(
			'body' => array_keys(shortcode::get_template_shortcodes('body')),
			'loop' => array_keys(shortcode::get_template_shortcodes('loop')),
		);
        $template      = $this->template;
        $pattern       = get_shortcode_regex();

		$arr = isset($items[$tmp_type])?$items[$tmp_type]:$items['body'];
		$tmp_sec = "template_{$tmp_type}";
		$tmp = $template->$tmp_sec;

        preg_match_all('/' . $pattern . '/s', $tmp, $matches);
        $html = $tmp;
        foreach ($arr as $code) {
            if (is_array($matches) && in_array($code, $matches[2])) {
                foreach ($matches[0] as $match) {
                    $html = str_replace($match, do_shortcode($match), $html);
                }
            }
        }
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
		$file = CATPDF_CACHE_PATH.$file;
		return file_put_contents($file, $contents);
	}	






}
?>