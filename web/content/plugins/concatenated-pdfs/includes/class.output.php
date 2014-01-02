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


    /*
     * Return pdf content
     * @type - string
     */
    public function custruct_template($type = 'all') {
        global $catpdf_templates,$_params,$catpdf_data,$posts;
		$id          = isset($_GET['catpdf_dl'])?$_GET['catpdf_dl']:null;
		$posts = $catpdf_data->query_posts($id);
		
        if ($type == 'all') {
            $curr_temp = $_params['template'];
        } else {
            $options   = get_option('catpdf_options');
            $curr_temp = $options['dltemplate'];
        }
        if ($curr_temp == 'def') {
            $template = $catpdf_templates->get_default_template();
        } else {
            $template = $catpdf_templates->get_template($curr_temp);
        }
        $this->template = $template;
        $this->_html_structure();
        $html = $this->filter_shortcodes('body');

		$coverLetter 	= "<div><h1 class='CoverTitle'>Cover Letter</h1></div><i class='page-break'></i>";
		$index 			= "<div><h1 class='CoverTitle'>index</h1></div><i class='page-break'></i>";
		$appendix 		= "<div><h1 class='CoverTitle'>appendix</h1></div><i class='page-break'></i>";

        $html = $this->head . $coverLetter.$index.$html."<i class='page-break'></i>".$appendix.$this->foot;
        return $html;
    }
    /*
     * Return html structure
     */
    private function _html_structure() {
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
		$opHeaderHeight="125px";
		$headerHeight=(int)(trim(str_replace('px','',$opHeaderHeight)))*0.75;//point convertion
		
		
		$topPad="{$opHeaderHeight}";
		$bottomPad="20px";
		$leftPad="0px";
		$rightPad="0px";
		
		$padding="{$topPad} {$rightPad} {$bottomPad} {$leftPad}";
		

        $body = '<body>';

        $script = '<script type="text/php">
			if ( isset($pdf) ) {
				$header = "'.addslashes($pageheader).'";
				$footer = "'.addslashes($pagefooter).'";
				$w = $pdf->get_width();
				$h = $pdf->get_height();
				$font = Font_Metrics::get_font("Arial, Helvetica, sans-serif", "normal");
				$size = 12;
				$pnum=$pdf->get_page_number()-1;
				$pcount=$pdf->get_page_count()-1;	
					
				$pdf->page_text($w-150, 0, $w." :: ".$h, $font, $size);
				
				
				// Open the object: all drawing commands will
				// go to the object instead of the current page
				$footer = $pdf->open_object();
					$pageText1 =  " {$footer} " ;
					$y1 = $h - 34;
					$x1 = $w - 15 - Font_Metrics::get_text_width($pageText1, $font, $size);
					$pdf->text($x1, $y1, $pageText1, $font, $size);
					
					$pageText = $pnum . " of " . $pcount;
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

				
				
				//Header
				$pageHeaderText =  "This is the header. {$header}" ;
				$t_y = 0 + '.($headerHeight/3).';
				$t_x = 0 + 15;// + Font_Metrics::get_text_width($pageHeaderText, $font, $size);
				$pdf->page_text($t_x, $t_y, $pageHeaderText, $font, $size);
				
				/*if($pagenum>1){	}*/
			
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
			}
			body {padding:'.$padding.';}
		</style>';

		
        $this->head  = $head_html.$head_style.$head_html_tag.$body.$script;
        $footer_html = '</body>';
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