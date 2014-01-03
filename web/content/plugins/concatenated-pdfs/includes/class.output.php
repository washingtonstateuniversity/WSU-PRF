<?php

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
		
		$title=$options['title'];
		$filename       = str_replace('%dd', date('d'), $title);
		$filename       = str_replace('%mm', date('m'), $filename);
		$filename       = str_replace('%yyyy', date('Y'), $filename);
		$filename       = str_replace('%template', $template->template_name, $filename);
		
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
        $options   = get_option('catpdf_options');
        $head_html = '<!DOCTYPE html>';
        $head_html .= '<html>';
        $head_html .= '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';
        $title = 'Post PDF Download';
		$pageheader="";
		$pagefooter="";
		$pagew=$this->pointtopixelConvertion(CPDF_Adapter::$PAPER_SIZES[$_params['papersize']][2]);
		$pageh=$this->pointtopixelConvertion(CPDF_Adapter::$PAPER_SIZES[$_params['papersize']][3]);
        if (!empty($this->template) && isset($options['title']) && $options['title'] !== '') {
            $template    = $this->template;
            $title       = $this->buildFileName($template,$options);
            $this->title = $title;
            $pageheader  = $this->filter_shortcodes('pageheader').$pagew."::".$pageh;
			$pagefooter  = '<img src="../../content/themes/cbn/img/wsuaa-logo.png" id="logo" /><div id="page_numbers">{pg#} of {pgT}</div>';//$this->filter_shortcodes('pagefooter');
        } else {
            $title       = CATPDF_BASE_NAME . '-' . date('m-d-Y');
            $this->title = $title;
        }
        $head_html .= '<title>' . $title . '</title>';
        if (isset($options['enablecss']) && $options['enablecss'] == 'on') {
            $head_html .= '<link type="text/css" rel="stylesheet" href="' . get_stylesheet_uri() . '">';
        }
		
		
		
		
        $head_html .= '<link type="text/css" rel="stylesheet" href="' . PDF_STYLE . '">';
        $head_html_tag = '</head>';
		
		$unit="px";
		$bodycolor="#F0F0F0";		//@@!!OPTION REPLACE
		
		$topMargin="15";			//@@!!OPTION REPLACE
		$headHeight="155";			//@@!!OPTION REPLACE
		$headSep="15";				//@@!!OPTION REPLACE

		$bottomMargin="15";			//@@!!OPTION REPLACE
		$footHeight="125";			//@@!!OPTION REPLACE
		$footSep="10";				//@@!!OPTION REPLACE
		$footSkip=($footHeight+$footSep);//equal to bottom:{VAL}px

		$pageHeadMargin= ($topMargin+$headHeight+$headSep);
		$pageFootMargin=($bottomMargin+$footSkip);
		$pagerightMargin="75";	//@@!!OPTION REPLACE
		$pageleftMargin="55";		//@@!!OPTION REPLACE
		
		$textBoxingWidth=$pagew-$pagerightMargin-$pageleftMargin;
		
		
		$page_padding="{$pageHeadMargin}{$unit} {$pagerightMargin}{$unit} {$pageFootMargin}{$unit} {$pageleftMargin}{$unit}";
		

        $body = '<body><div id="head_area"><div class="wrap">'.$pageheader.'</div></div><div id="foot_area"><div class="wrap">'.$pagefooter.'</div></div>';
		$indexscriptglobals='<script type="text/php">
			$GLOBALS["i"]=1;
			$GLOBALS["indexpage"]=0;
			$GLOBALS["chapters"] = array();
		</script>';
$script="";
		//replace this with a linked path to a selected css file
        $head_style = '
		<style>
			' . strip_tags($options['customcss']) . '
			html,body {
				font-family: sans-serif;
				text-align: justify;
				background-color:'.$bodycolor.';
				position: relative;
			}
			@page{}
			#head_area{ position:fixed;left:'.$pageleftMargin.$unit.';top:'.$topMargin.$unit.';height:'.$headHeight.$unit.'; width:'.$textBoxingWidth.$unit.'; }
			#head_area .wrap{position:relative; width:100%; height:'.$headHeight.$unit.';}
			#foot_area{ position:fixed;left:'.$pageleftMargin.$unit.';bottom:'.$bottomMargin.$unit.';height:'.$footHeight.$unit.';width:'.$textBoxingWidth.$unit.';}
			#foot_area .wrap{position:relative; width:100%; height:'.$footHeight.$unit.';}
			body {padding:'.$page_padding.';}/*note that the body is used as our sudo "page" it is our saffal base*/
			i.page-break {page-break-after: always;border: 0;}
		</style>';

        $this->head  = $head_html.$head_style.$head_html_tag.$body.$indexscriptglobals.$script;

		$indexer = '
<script type="text/php">
	$bs = $GLOBALS["backside"];

	$count=$pdf->get_page_number();
	$chapters=$GLOBALS["chapters"];
	//var_dump($pdf->get_cpdf());
	
	$o=1;
	$p=1;
	foreach($pdf->get_cpdf()->objects as $obj){
		foreach ($chapters as $chapter => $page) {
			if(isset($pdf->get_cpdf()->objects[$o]["c"])){
				$content = $pdf->get_cpdf()->objects[$o]["c"];
				//var_dump($content);
				
				$chapter_str	= "Chapter ".$chapter." ";
				$pagenumber_str	= " p: ".$page["page"];
				$text_str		= $page["text"]." ";
				if(strpos($content,\'{chapter\') !== false){
					$content = str_replace( \'{chapter\'.$chapter.\'}\' , $chapter_str, $content );
					$content = str_replace( \'{page\'.$chapter.\'}\' , $pagenumber_str, $content );
					$content = str_replace( \'{text\'.$chapter.\'}\' , $text_str, $content );
				}
				if(strpos($content,\'{pg#}\') !== false){
					$content = str_replace( \'{pg#}\' , $p."", $content );
					$content = str_replace( \'{pgT}\' , $count."", $content );
					$p++;
				}
				$pdf->get_cpdf()->objects[$o]["c"]=$content;
				
			}
		} 
		$o++;
	}
	$pdf->page_script(\'$indexpage=$GLOBALS["indexpage"]; if ($PAGE_NUM==$indexpage ) { $pdf->add_object($GLOBALS["backside"],"add"); $pdf->stop_object($GLOBALS["backside"]); }\');
</script>';
		
		
        $footer_html = $indexer.'
		</body>';
        $footer_html .= '</html>';
        $this->foot = $footer_html;
    }
    /*
     * Return html with filtered shortcodes
     * @tmp_type - string
	 * this is awakward it needs to be reworked
     */
    public function filter_shortcodes($tmp_type = '') {
        $items         = array();
        $items['body'] = array_keys(shortcode::get_template_shortcodes('body'));

        $items['loop'] = array_keys(shortcode::get_template_shortcodes('loop'));
        $template      = $this->template;
        $pattern       = get_shortcode_regex();
        if ($tmp_type == 'body') {
            $arr = $items['body'];
            $tmp = $template->template_body;
        } elseif ($tmp_type == 'loop') {
            $arr = $items['loop'];
            $tmp = $template->template_loop;
        } elseif ($tmp_type == 'pageheader') {
            $arr = $items['body'];
            $tmp = $template->template_pageheader;
        } elseif ($tmp_type == 'pagefooter') {
            $arr = $items['body'];
            $tmp = $template->template_pagefooter;
        }
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

}
?>