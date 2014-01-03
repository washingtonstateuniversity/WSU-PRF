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
		global $_params;
        $options   = get_option('catpdf_options');
        $head_html = '<!DOCTYPE html>';
        $head_html .= '<html>';
        $head_html .= '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';
        $title = 'Post PDF Download';
		$pageheader="";
		$pagefooter="";
        if (!empty($this->template) && isset($options['title']) && $options['title'] !== '') {
            $template    = $this->template;
            $title       = $this->buildFileName($template,$options);
            $this->title = $title;
            $pageheader  = $this->filter_shortcodes('pageheader');
			$pagefooter  = $this->filter_shortcodes('pagefooter');
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
		
		
		$bodycolor="#f5f3e9";
		$opHeaderHeight="75px";
		$headerHeight=(int)(trim(str_replace('px','',$opHeaderHeight)))*0.75;//point convertion
		
		$opFooterHeight="50px";
		
		
		$body_topPad="{$opHeaderHeight}";//should be building this
		$body_bottomPad="{$opFooterHeight}";
		$body_leftPad="0px";
		$body_rightPad="0px";
		
		$body_vertpad=((int)(trim(str_replace('px','',$body_topPad))) + (int)(trim(str_replace('px','',$body_bottomPad))));
		$body_horpad=((int)(trim(str_replace('px','',$body_leftPad))) + (int)(trim(str_replace('px','',$body_rightPad))));
		
		$body_padding="{$body_topPad} {$body_rightPad} {$body_bottomPad} {$body_leftPad}";
		

        $body = '<body><div id="head_area">'.$pageheader.'</div><div id="foot_area">'.$pagefooter.'<img src="../../content/themes/cbn/img/wsuaa-logo.png" id="logo" /></div>';
		$indexscriptglobals='<script type="text/php">
			$font = Font_Metrics::get_font("helvetica", "bold");
			$GLOBALS["i"]=1;
			$GLOBALS["indexpage"]=0;
			$GLOBALS["chapters"] = array();
		</script>';
        $script = '<script type="text/php">
			if ( isset($pdf) ) {
				$header = "'.addslashes($pageheader).'";
				$footertext = "'.addslashes($pagefooter).'";
				$w = $pdf->get_width();
				$h = $pdf->get_height();
				$font = Font_Metrics::get_font("Arial, Helvetica, sans-serif", "normal");
				$size = 12;
				
					/*
					$pdf->page_text($w-150, 0, $w." :: ".$h, $font, $size);
					
					
					// Open the object: all drawing commands will
					// go to the object instead of the current page
					$footer = $pdf->open_object();
						$pnum=$pdf->get_page_number();
						$pcount=$pdf->get_page_count();	
					
						$pageText1 =  " {$footertext} " ;
						$y1 = $h - 34;
						$x1 = $w - 15 - Font_Metrics::get_text_width($pageText1, $font, $size);
						$pdf->text($x1, $y1, $pageText1, $font, $size);
						
						$pageText =  $PAGE_NUM." of ". $PAGE_COUNT;
						$y = $h - 20;
						$x = $w - 15 - Font_Metrics::get_text_width($pageText, $font, $size);
						$pdf->text($x, $y, $pageText, $font, $size);
			
						// Draw a line along the bottom
						$line_height=1;
						$color = array(125,125,125);
						$y = $h - 2 * $line_height - 24;
						$pdf->line(16, $y, $w - 16, $y, $color, 1);
	
						//image
						$w = $pdf->get_width();
						$h = $pdf->get_height();
						// Add a logo
						$img_w = 25; 
						$img_h = 25; 		
						$pdf->image("../../content/themes/cbn/img/wsuaa-logo.png", "png", $h-$img_h, 75, $img_w, $img_h);
						
					// Close the object (stop capture)
					$pdf->close_object();
					
					// Add the object to every page. You can
					// also specify "odd" or "even"
					$pdf->add_object($footer, "all");	
	*/
					
					
					//Header
					/*$pageHeaderText =  "This is the header. {$header}" ;
					$t_y = 0 + '.($headerHeight/3).';
					$t_x = 0 + 15;// + Font_Metrics::get_text_width($pageHeaderText, $font, $size);
					$pdf->page_text($t_x, $t_y, $pageHeaderText, $font, $size);*/
				/*if($pagenum>1){}	
					*/
			
			} 
			</script>';//http://stackoverflow.com/a/14089936/746758 look to this		

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
			#head_area{ position:fixed; top:0px;height:'.$opHeaderHeight.'; border:1px solid #494949;}
			#foot_area{ position:fixed; bottom:0px;height:'.$opFooterHeight.'; border:1px solid #494949;}
			body {padding:'.$body_padding.';}
			i.page-break {
			  page-break-after: always;
			  border: 0;
			}
			#logo{ height:50px; top:12.5px; left:12.5px;  }
		</style>';

		
        $this->head  = $head_html.$head_style.$head_html_tag.$body.$indexscriptglobals.$script;
		
		
		
		
		$indexer = '
<script type="text/php">
	$bs = $GLOBALS["backside"];
	//var_dump($bs);
	
	$font = Font_Metrics::get_font("Arial, Helvetica, sans-serif", "normal");
	$size = 12;

	
	$chapters=$GLOBALS["chapters"];
	//var_dump($pdf->get_cpdf());
	
	$o=1;
	foreach($pdf->get_cpdf()->objects as $obj){
		foreach ($chapters as $chapter => $page) {
			if(isset($pdf->get_cpdf()->objects[$o]["c"])){
				$content = $pdf->get_cpdf()->objects[$o]["c"];
				//var_dump($content);
				
				$chapter_str	= "Chapter ".$chapter." ";
				$pagenumber_str	= " p: ".$page["page"];
				$text_str		= $page["text"]." ";

				$content = str_replace( \'{%%chapter\'.$chapter.\'%%}\' , $chapter_str , $content );
				$content = str_replace( \'{%%page\'.$chapter.\'%%}\' , $pagenumber_str , $content );
				$content = str_replace( \'{%%text\'.$chapter.\'%%}\' , $text_str , $content );

				$pdf->get_cpdf()->objects[$o]["c"]=$content;



				
				
			}
		} 
		$o++;
	}
	
	
	
	$pdf->page_script(\'$indexpage=$GLOBALS["indexpage"]; if ($PAGE_NUM==$indexpage ) { $pdf->add_object($GLOBALS["backside"],"add"); $pdf->stop_object($GLOBALS["backside"]); }\');
</script> ';
		
		
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