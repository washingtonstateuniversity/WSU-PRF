<?php

/* things still to do
-remove the use themes templates inlue of per template css path link
-must beable to sort on optional items like tax/type etc
-cache the pdfs on md5 of (tmp-ops)+(lastpost-date)+(query)
-provide more areas to controll
-make the index
-create ruls for the bookmarking


*/
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class catpdf_core {
    public $dompdf = NULL;
	public $shortcode = NULL;
	public $catpdf_pages = NULL;
	public $catpdf_templates = NULL;
	
    public $message = array();
    public $post = array();
    public $title = '';
    public $posts;
    function __construct() {
		global $dompdf,$shortcode,$catpdf_pages,$catpdf_templates;

		// Include dompdf //make sure to get back to pulling this in to the settings
		include(CATPDF_PLUGIN_PATH . '/dompdf/dompdf_config.inc.php');
		$dompdf = new DOMPDF(); // Instantiate dompdf library
		
		// Include shortcode class
		include(CATPDF_PLUGIN_PATH . '/includes/class.shortcode.php');
		$shortcode = new shortcode();// Instantiate shortcode class
		
		// Include functions
		include(CATPDF_PLUGIN_PATH . '/includes/functions.php');

		// Include page
		include(CATPDF_PLUGIN_PATH . '/includes/class.pages.php');
		$catpdf_pages = new catpdf_pages();// Instantiate pages class

		// Include templates
		include(CATPDF_PLUGIN_PATH . '/includes/class.templates.php');
		$catpdf_templates = new catpdf_templates();// Instantiate pages class

		
        if (!is_admin()) {
            $options = get_option('catpdf_options');
            if ($options['postdl'] == 'on') {
                // Initialize public functions
                add_filter('the_content', array( $this, 'apply_post_download_button' ));
            }
        }
       
    }
    /*
     * Initialize install
     */
    public function install_init() {
        // Add database table
        $this->_add_table();
        // Insert default datas
        $this->_insert_defaults();
    }
    /*
     * Add template table
     */
    private function _add_table() {
        global $wpdb;
        // Construct query
        $table_name = $wpdb->prefix . "catpdf_template";
        $sql        = "CREATE TABLE " . $table_name . " (
	    template_id mediumint(9) NOT NULL AUTO_INCREMENT,
	    template_name varchar(50) NOT NULL,
		template_description text,
	    template_loop text,
	    template_body text,
		template_pageheader text,
		template_pagefooter text,
		create_by mediumint(9) NOT NULL,
		create_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
	    UNIQUE KEY id (template_id)
		);";
        // Import wordpress database library
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        // Save version
        add_option('catpdf_db_version', CATPDF_VERSION);
        // Add plugin option holder
        $options = array(
            'enablecss' => 'on',
            'title' => 'Report %mm-%yyyy',
            'dltemplate' => 'def',
            'postdl' => 'off'
        );
        add_option('catpdf_options', $options, '', 'yes');
    }
	/*
     * Set option defaults
     */
    private function _insert_defaults() {
        // Check if default template exist
        if (!$this->_is_exist('template_name', 'Sample Template')) {
            // Get default template
            $default_template = $this->custruct_default_template();
            // Set up data
            $data             = array(
                'template_name' => 'Sample Template',
                'template_loop' => $default_template['loop'],
                'template_body' => $default_template['body'],
				'template_pageheader' => $default_template['pageheader'],
				'template_pagefooter' => $default_template['pagefooter'],
				
            );
            // Insert template
            $this->add_this($data);
        }
    }
    /*
     * Returns download button link
     */
    public function apply_post_download_button($content) {
        if ($GLOBALS['post']->post_type == 'post') {
            $id   = $GLOBALS['post']->ID;
            $url  = add_query_arg('catpdf_dl', $id);
            $link = '<a href="' . $url . '"><img src="' . CATPDF_PLUGIN_URL . 'images/download-icon.png"></a>';
            return $content . $link;
        } else {
            return $content;
        }
    }

    /*-------------------------------------------------------------------------*/
    /* -Option- 															   */
    /*-------------------------------------------------------------------------*/
    /*
     * Update plugin option
     */
    public function update_options() {
        $options = $this->post;
        update_option('catpdf_options', $options);
    }
    /*-------------------------------------------------------------------------*/
    /* -Export- 															   */
    /*-------------------------------------------------------------------------*/
    /*
     * Perform export pdf
     */
    public function export() {
        global $dompdf;
        $post_list   = $this->_get_data();
        $this->posts = $post_list;
        $content     = $this->custruct_template();
		$dompdf->load_html($content);
        $dompdf->set_paper($this->post['papersize'], $this->post['orientation']);
        $dompdf->render();

        $dompdf->stream(trim($this->title) . ".pdf");
    }
    /*
     * Download post pdf
     */
    public function download_posts() {
        global $dompdf;
        $param_arr   = array(
            'from' => (isset($_GET['from'])) ? urldecode($_GET['from']) : '',
            'to' => (isset($_GET['to'])) ? urldecode($_GET['to']) : '',
            'cat' => (isset($_GET['cat']) && $_GET['cat'] != '') ? explode(',', $_GET['cat']) : array(),
            'user' => (isset($_GET['author']) && $_GET['author'] != '') ? explode(',', $_GET['author']) : array(),
            'template' => (isset($_GET['template'])) ? urldecode($_GET['template']) : 'def'
        );
        $this->post  = $param_arr;
        $post_list   = $this->_get_data();
        $this->posts = $post_list;
        $content     = $this->custruct_template();
        $dompdf->load_html($content);
        $dompdf->set_paper((isset($_GET['paper_size'])) ? urldecode($_GET['paper_size']) : 'letter', (isset($_GET['paper_orientation'])) ? urldecode($_GET['paper_orientation']) : 'portrait');
        $dompdf->render();
        $dompdf->stream(trim($this->title) . ".pdf");
    }
    /*
     * Download single post pdf
     */
    public function download_post() {
        global $dompdf;
        $id          = $_GET['catpdf_dl'];
        $post        = $this->_get_data($id);
        $this->posts = $post;
        $single      = $post[0];
        $filenmae    = preg_replace('/[^a-z0-9]/i', '_', $single->post_title);
        $content     = $this->custruct_template('single');
        $dompdf->load_html($content);
        $dompdf->set_paper('letter', 'portrait');
        $dompdf->render();
        $dompdf->stream(trim($filenmae) . ".pdf");
    }
    /*
     * Return pdf content
     * @type - string
     */
    public function custruct_template($type = 'all') {
        global $catpdf_core;
        if ($type == 'all') {
            $curr_temp = $this->post['template'];
        } else {
            $options   = get_option('catpdf_options');
            $curr_temp = $options['dltemplate'];
        }
        if ($curr_temp == 'def') {
            $template = $catpdf_core->get_default_template();
        } else {
            $template = $catpdf_core->get_template($curr_temp);
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
        $options   = get_option('catpdf_options');
        $head_html = '<!DOCTYPE html>';
        $head_html .= '<html>';
        $head_html .= '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';
        $title = 'Post PDF Download';
		$pageheader="";
		$pagefooter="";
        if (!empty($this->template) && isset($options['title']) && $options['title'] !== '') {
            $template    = $this->template;
            $title       = $options['title'];
            $title       = str_replace('%dd', date('d'), $title);
            $title       = str_replace('%mm', date('m'), $title);
            $title       = str_replace('%yyyy', date('Y'), $title);
            $title       = str_replace('%template', $template->template_name, $title);
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
		$bottomPad="25px";
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
    /*
     * Get post data
     * @id - int
	 * It's worth noting that any out put here will print into the pdf.  If the PDF can't be 
	 * read then look at it in a text editor like Notepad, where you will see the php errors
     */
    public function _get_data($id = NULL) {
 		$post = $this->post;
        $args = array(
            'post_type' => $post['type'],
            'posts_per_page' => -1,
            'order' => 'DESC'
        );
        if ($id !== NULL) {
            $args['p'] = $id;
        }
        if (isset($post['user']) && count($post['user']) > 0) {
            $au_str = '';
            foreach ($post['user'] as $au) {
                $au_str .= $au . ',';
            }
            $args['author'] = substr($au_str, 0, -1);
        }
        if (isset($post['status']) && count($post['status']) > 0) {
            $status_str = '';
            foreach ($post['status'] as $status) {
                $status_str .= $status . ',';
            }
            $args['post_status'] = substr($status_str, 0, -1);
        }
        if (isset($post['cat']) && count($post['cat']) > 0) {
            $cat_str = '';
            foreach ($post['cat'] as $cat) {
                $cat_str .= $cat . ',';
            }
            $args['cat'] = substr($cat_str, 0, -1);
        }
        add_filter('posts_where', array(
            &$this,
            'filter_where'
        ));
        $result = new WP_Query($args);
		
        return $result->posts;

    }
    /*
     * Return query filter
     * @where - string
     */
    public function filter_where($where = '') {
        $post = $this->post;
        if (isset($post['from']) && $post['from'] != '') {
            $from = date('Y-m-d', strtotime($post['from']));
            $where .= ' AND DATE_FORMAT( post_date , "%Y-%m-%d" ) >= "' . $from . '"';
        }
        if (isset($post['to']) && $post['to'] != '') {
            $to = date('Y-m-d', strtotime($post['to']));
            $where .= ' AND DATE_FORMAT( post_date , "%Y-%m-%d" ) <= "' . $to . '"';
        }
        return $where;
    }
    /*-------------------------------------------------------------------------*/
    /* -General- 															   */
    /*-------------------------------------------------------------------------*/
    /*
     * Return falsh message
     */
    public function get_message() {
        if (!empty($this->message)) {
            $arr = $this->message;
            return '<div id="message" class="' . $arr['type'] . '"><p>' . $arr['message'] . '</p></div>';
        }
    }
    /*
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