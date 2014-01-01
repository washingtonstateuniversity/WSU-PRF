<?php

/* things still to do
-remove the use themes templates inlue of per template css path link
-must beable to sort on optional items like tax/type etc
-cache the pdfs on md5 of (tmp-ops)+(lastpost-date)+(query)
-provide more areas to controll
-make the index
-create ruls for the bookmarking


*/


class catpdf_core {
    public $dompdf = NULL;
    public $message = array();
    public $post = array();
    public $title = '';
    public $posts;
    function __construct() {
        if (is_admin()) {
            // Initailize admin
            add_action('admin_init', array(
                &$this,
                'admin_init'
            ));
            add_action('admin_menu', array(
                &$this,
                'admin_menu'
            ));
        } else {
            $options = get_option('catpdf_options');
            if ($options['postdl'] == 'on') {
                // Initialize public functions
                add_filter('the_content', array(
                    &$this,
                    'apply_post_download_button'
                ));
            }
        }
        if (isset($_POST)) {
            $this->post = $_POST;
		
            // Check if option save is performed
            if (isset($this->post['catpdf_save_option'])) {
                // Add update option action hook
                add_action('init', array(
                    &$this,
                    'update_options'
                ));
            }
            // Check if pdf export is performed
            if (isset($this->post['catpdf_export'])) {
                // Add export hook
                add_action('init', array(
                    &$this,
                    'export'
                ));
            }
            // Check if template save is performed
            if (isset($this->post['catpdf_save'])) {
                if ($this->post['templateid'] == '') {
                    // Add save template action hook
                    add_action('init', array(
                        &$this,
                        'add_template'
                    ));
                } else {
                    // Add update template action hook
                    add_action('init', array(
                        &$this,
                        'update_template'
                    ));
                }
            }
        }
        // Check if post download is performed
        if (isset($_GET['catpdf_dl'])) {
            // Add download action hook
            add_action('init', array(
                &$this,
                'download_post'
            ));
        }
        // Check if single post download is performed
        if (isset($_GET['catpdf_post_dl'])) {
            // Add download action hook
            add_action('init', array(
                &$this,
                'download_posts'
            ));
        }
    }
    /*
     * Initailize plugin admin part
     */
    public function admin_init() {
        // Enque style and script		
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-datepicker', CONCATENATEDPDF_PLUGIN_URL . 'js/ui/jquery.ui.datepicker.js', array(
            'jquery'
        ), '1.9.0', 'all');
        wp_enqueue_style('jquery-ui-datepicker', CONCATENATEDPDF_PLUGIN_URL . 'css/ui/jquery.ui.all.css', false, '1.9.0', 'all');
        wp_enqueue_script('catpdf-js', CONCATENATEDPDF_PLUGIN_URL . 'js/catpdf.custom.js', array(
            'jquery'
        ), '', 'all');
        wp_enqueue_style('catpdfport-style', CONCATENATEDPDF_PLUGIN_URL . 'css/style.css', false, '1.9.0', 'all');
    }
    /*
     * Add plugin menu
     */
    public function admin_menu() {
        // Register menu
        add_menu_page(CONCATENATEDPDF_NAME, CONCATENATEDPDF_NAME, 'manage_options', CONCATENATEDPDF_BASE_NAME, array(
            &$this,
            'option_page'
        ), CONCATENATEDPDF_PLUGIN_URL . 'images/nav-icon.png');
        // Register sub-menu
        add_submenu_page(CONCATENATEDPDF_BASE_NAME, _('Download PDF'), _('Download PDF'), 'manage_options', 'catpdf-download-pdf', array(
            &$this,
            'download_page'
        ));
        add_submenu_page(CONCATENATEDPDF_BASE_NAME, _('Template Manager'), _('Template Manager'), 'manage_options', 'catpdf-template-manager', array(
            &$this,
            'template_manager_page'
        ));
        add_submenu_page(CONCATENATEDPDF_BASE_NAME, _('Add Template'), _('Add Template'), 'manage_options', 'catpdf-add-template', array(
            &$this,
            'add_page'
        ));
    }
    /*
     * Display "Add" page
     */
    public function add_page() {
        $data            = array();
        $data['message'] = $this->get_message();
        $this->view(CONCATENATEDPDF_PLUGIN_PATH . '/inc/views/template.php', $data);
    }
    /*
     * Display "Template Manager" page
     */
    public function template_manager_page() {
        // Include list class
        include(CONCATENATEDPDF_PLUGIN_PATH . '/inc/list_class.php');
        $wp_list_table = new template_list();
        $wp_list_table->prepare_items();
        // Check if edit action is performed
        if (isset($_GET['catpdf_action']) && $_GET['catpdf_action'] == 'edit') {
            $data['on_edit'] = $this->get_template($_GET['template']);
            $data['message'] = $this->get_message();
            // Display template form
            $this->view(CONCATENATEDPDF_PLUGIN_PATH . '/inc/views/template.php', $data);
        } else {
            ob_start();
            $wp_list_table->display();
            $data['table']   = ob_get_clean();
            $data['message'] = $this->get_message();
            // Display template list
            $this->view(CONCATENATEDPDF_PLUGIN_PATH . '/inc/views/template_manager.php', $data);
        }
    }
	
	
	

	
	
    /*
     * Display "Download" page
     */
    public function download_page() {
        $data                  = array();
        $args                  = array(
            'orderby' => 'name',
            'order' => 'ASC',
            'hierarchical' => 1,
            'hide_empty' => '0'
        );
        $options               = get_option('catpdf_options');
        // Construct category dropdown
        $select_cats           = wp_dropdown_categories(array(
            'echo' => 0,
            'hierarchical' => 1
        ));
		
		$post_types      = get_post_types(array(
            'public'   => true,
                     //'_builtin' => false
        ),'names' , 'and' );
		$select_types= '<select name="type[]" multiple="multiple" class="postform" >';
		foreach ($post_types  as $post_type ) {
			$select_types.='<option value="'. $post_type.'"  class="level-0" >'. $post_type. '</option>';
		}
		$select_types.='</select>';
		
		
        $select_cats           = str_replace("name='cat' id=", "name='cat[]' multiple='multiple' id=", $select_cats);
        $select_cats           = str_replace("<option", '<option selected="selected"', $select_cats);
        // Construct user dropdown
        $select_author         = wp_dropdown_users(array(
            'id' => 'author',
            'echo' => false
        ));
        $select_author         = str_replace("name='user' ", "name='user[]' multiple='multiple' ", $select_author);
        $select_author         = str_replace("<option", '<option selected="selected"', $select_author);
		
		$data['select_types']  = $select_types;
        $data['select_cats']   = $select_cats;
        $data['select_author'] = $select_author;
        $data['select_sizes']  = array(
            'letter', '4a0', '2a0', 'a0', 'a1', 'a2', 'a3', 'a4', 'a5', 'a6', 'a7', 'a8', 'a9', 'a10', 'b0', 'b1', 'b2', 'b3', 'b4', 'b5', 'b6', 'b7', 'b8', 'b9', 'b10', 'c0', 'c1', 'c2', 'c3', 'c4', 'c5', 'c6c6', 'c7', 'c8', 'c9', 'c10', 'ra0', 'ra1', 'ra2', 'ra3', 'ra4', 'sra0', 'sra1', 'sra2', 'sra3', 'sra4', 'legal', 'ledger', 'tabloid', 'executive', 'folio', 'commerical #10 envelope', 'catalog #10 1/2 envelope', '8.5x11', '8.5x14', '11x17'
        );
        $data['select_ors']    = array(
            'portrait', 'landscape'
        );
        $data['option_url']    = $tool_url;
        $data['templates']     = $this->get_template();
        $data['message']       = $this->get_message();
        // Display export form
        $this->view(CONCATENATEDPDF_PLUGIN_PATH . '/inc/views/export.php', $data);
    }
    /*
     * Display "Option" page
     */
    public function option_page() {
        // Get options
        $options           = get_option('catpdf_options');
        $data['options']   = $options;
        // Get templates
        $data['templates'] = $this->get_template();
        // Display option form
        $this->view(CONCATENATEDPDF_PLUGIN_PATH . '/inc/views/options.php', $data);
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
		create_by mediumint(9) NOT NULL,
		create_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
	    UNIQUE KEY id (template_id)
		);";
        // Import wordpress database library
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        // Save version
        add_option('catpdf_db_version', CONCATENATEDPDF_VERSION);
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
                'template_body' => $default_template['body']
            );
            // Insert template
            $this->add_this($data);
        }
    }
    /*
     * Return default template structure
     */
    private function custruct_default_template($type = 'all') {
        $temp         = array();
        $temp['name'] = 'Default';
        if ($type == 'single') {
            // Construct template loop
            $looptemplate = '<biv class="post single">';
            $looptemplate .= '<h2>[title]</h2>';
            $looptemplate .= '<div class="meta"><p>Posted on <strong>[date]</strong> by <strong>[author]</strong></p></div>';
            $looptemplate .= '<p>[content]</p>';
            $looptemplate .= '<div class="taxonomy">[category label="Posted in:"] | [tags label="Tagged:"] | With [comments_count] comments</div>';
            $looptemplate .= '</biv>';
            // Construct template body
            $bodytemplate = '<div class="content-wrapper">';
            $bodytemplate .= '[loop]';
            $bodytemplate .= '</div>';
        } else {
            // Construct template loop
            $looptemplate = '<biv class="post">';
            $looptemplate .= '<h2>[title]</h2>';
            $looptemplate .= '<div class="meta"><p>Posted on <strong>[date]</strong> by <strong>[author]</strong></p></div>';
            $looptemplate .= '<p>[content]</p>';
            $looptemplate .= '<div class="taxonomy">[category label="Posted in:"] | [tags label="Tagged:"] | With [comments_count] comments</div>';
            $looptemplate .= '</biv>';
            // Construct template body
            $bodytemplate = '<div class="content-wrapper">';
            $bodytemplate .= '<div class="pdf-header">';
            $bodytemplate .= '<h1>Post List</h1>';
            $bodytemplate .= '<h2>[site_title]</h2>';
            $bodytemplate .= '<h3>[site_tagline]</h3>';
            $bodytemplate .= '[from_date label="From:"] [to_date label="To:"]';
            $bodytemplate .= '</div>';
            $bodytemplate .= '<div>[loop]</div>';
            $bodytemplate .= '</div>';
        }
        $temp['loop'] = $looptemplate;
        $temp['body'] = $bodytemplate;
        return $temp;
    }
    /*
     * Returns download button link
     */
    public function apply_post_download_button($content) {
        if ($GLOBALS['post']->post_type == 'post') {
            $id   = $GLOBALS['post']->ID;
            $url  = add_query_arg('catpdf_dl', $id);
            $link = '<a href="' . $url . '"><img src="' . CONCATENATEDPDF_PLUGIN_URL . 'images/download-icon.png"></a>';
            return $content . $link;
        } else {
            return $content;
        }
    }
    /*
     * Return default template
     */
    public function get_default_template() {
        if (isset($_GET['catpdf_dl'])) {
            $default_template = $this->custruct_default_template('single');
        } else {
            $default_template = $this->custruct_default_template();
        }
        $arr = array();
        $arr = array(
            'template_name' => 'Default',
            'template_loop' => $default_template['loop'],
            'template_body' => $default_template['body']
        );
        return (object) $arr;
    }
    /*
     * Insert to template table
     * @arr - array
     */
    public function add_this($arr = array()) {
        global $wpdb, $current_user;
        // Get user info
        get_currentuserinfo();
        $user               = $current_user;
        // Insert data
        $arr['create_date'] = current_time('mysql');
        $arr['create_by']   = $user->ID;
        $table_name         = $wpdb->prefix . "catpdf_template";
        $rows_affected      = $wpdb->insert($table_name, $arr);
    }
    /*
     * Update entry in template table
     * @data - array
     */
    public function update_this($data = array()) {
        global $wpdb;
        $where         = array(
            'template_id' => $this->post['templateid']
        );
        $table_name    = $wpdb->prefix . "catpdf_template";
        $rows_affected = $wpdb->update($table_name, $data, $where);
    }
    /*
     * Check if entry already exist
     * @column - string
     * @value - string
     */
    private function _is_exist($column = '', $value = '') {
        global $wpdb;
        $table_name = $wpdb->prefix . "catpdf_template";
        $result     = $wpdb->get_results("SELECT * FROM " . $table_name . " WHERE " . $column . " = '" . $value . "'");
        if (count($result) > 0) {
            return true;
        } else {
            return false;
        }
    }
    /*
     * Return template data
     * @id - string
     */
    public function get_template($id = NULL) {
        global $wpdb;
        $table_name = $wpdb->prefix . "catpdf_template";
        if ($id !== NULL) {
            $sql      = $wpdb->prepare("SELECT * FROM " . $table_name . " WHERE template_id = %d;", $id);
            $template = $wpdb->get_row($sql);
        } else {
            $template = $wpdb->get_results("SELECT * FROM " . $table_name);
        }
        return $template;
    }
    /*
     * Add template
     */
    public function add_template() {
        if ($this->post['templatename'] != '') {
            $data = array(
                'template_name' => $this->post['templatename'],
                'template_loop' => $this->post['looptemplate'],
                'template_body' => $this->post['bodytemplate'],
                'template_description' => $this->post['description']
            );
            // Insert template
            $this->add_this($data);
            $this->message = array(
                'type' => 'updated',
                'message' => __('Template saved.')
            );
        } else {
            $this->message = array(
                'type' => 'error',
                'message' => __('Please provide template name.')
            );
        }
    }
    /*
     * Update template database entry
     */
    public function update_template() {
        if ($this->post['templatename'] != '') {
            $data = array(
                'template_name' => $this->post['templatename'],
                'template_description' => $this->post['description'],
                'template_body' => $this->post['bodytemplate'],
                'template_loop' => $this->post['looptemplate']
            );
            $this->update_this($data);
            $this->message = array(
                'type' => 'updated',
                'message' => __('Template updated.')
            );
        } else {
            $this->message = array(
                'type' => 'error',
                'message' => __('Please provide template name.')
            );
        }
    }
    /*
     * Delete template entry
     */
    public function delete_template($id) {
        global $wpdb;
        $table_name = $wpdb->prefix . "catpdf_template";
        $wpdb->query("DELETE FROM " . $table_name . " WHERE template_id = " . $id);
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
		
				
		
		$coverLetter = "<div>
<h1 class='CoverTitle'>Cover Letter</h1>
</div>
<i class='page-break'></i>";

		$index = "<div>
<h1 class='CoverTitle'>index</h1>
</div>
<i class='page-break'></i>";



		$appendix = "
		<div>
<h1 class='CoverTitle'>appendix</h1>
</div>
<i class='page-break'></i>";

		
		
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
        if (!empty($this->template) && isset($options['title']) && $options['title'] !== '') {
            $template    = $this->template;
            $title       = $options['title'];
            $title       = str_replace('%dd', date('d'), $title);
            $title       = str_replace('%mm', date('m'), $title);
            $title       = str_replace('%yyyy', date('Y'), $title);
            $title       = str_replace('%template', $template->template_name, $title);
            $this->title = $title;
        } else {
            $title       = CONCATENATEDPDF_BASE_NAME . '-' . date('m-d-Y');
            $this->title = $title;
        }
        $head_html .= '<title>' . $title . '</title>';
        if (isset($options['enablecss']) && $options['enablecss'] == 'on') {
            $head_html .= '<link type="text/css" rel="stylesheet" href="' . get_stylesheet_uri() . '">';
        }
        $head_html .= '<link type="text/css" rel="stylesheet" href="' . PDF_STYLE . '">';
        $head_html .= '</head>';
		
		
		$bodycolor="#f5f3e9";
		$opHeaderHeight="125 px";
		$headerHeight=(int)(trim(str_replace('px','',$opHeaderHeight)))*0.75;//point convertion
		
		
		$topPad="{$headerHeight}px";
		$bottomPad="25px";
		$leftPad="0px";
		$rightPad="0px";
		
		$padding="{$topPad} {$rightPad} {$bottomPad} {$leftPad}";
		
		
		
		
		//replace this with a linked path to a selected css file
        $head_html .= '<style>' . strip_tags($options['customcss']) . '
		html,body {
			font-family: sans-serif;
			text-align: justify;
			background-color:'.$bodycolor.';
		}
		body {padding:'.$padding.';}
		
		
		</style>';
        $head_html .= '<body>

    <script type="text/php">
	if ( isset($pdf) ) {

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
		

			$pageText1 =  " Page " ;
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
		// Close the object (stop capture)
		$pdf->close_object();
		
		// Add the object to every page. You can
		// also specify "odd" or "even"
		$pdf->add_object($footer, "all");
			
		// Open the object: all drawing commands will
		// go to the object instead of the current page
		$footerImg = $pdf->open_object();	
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
		$pdf->add_object($footerImg, "all");
		
		//Header
		$pageHeaderText =  "This is the header." ;
		$t_y = 0 + '.($headerHeight/3).';
		$t_x = 0 + 15;// + Font_Metrics::get_text_width($pageHeaderText, $font, $size);
		$pdf->page_text($t_x, $t_y, $pageHeaderText, $font, $size);
		
		/*if($pagenum>1){	}*/
	
	} 
/**/
    </script>';
//http://stackoverflow.com/a/14089936/746758 look to this		



		
        $this->head  = $head_html;
        $footer_html = '</body>';
        $footer_html .= '</html>';
        $this->foot = $footer_html;
    }
    /*
     * Return html with filtered shortcodes
     * @tmp_type - string
     */
    public function filter_shortcodes($tmp_type = '') {
        $items         = array();
        $items['body'] = array(
            'loop',
            'site_title',
            'site_tagline',
            'site url',
            'date_today',
            'from date',
            'to date',
            'categories',
            'post_count'
        );
        $items['loop'] = array(
            'title',
            'excerpt',
            'content',
            'permalink',
            'date',
            'author',
            'author_photo',
            'author_description',
            'status',
            'featured_image',
            'category',
            'tags',
            'comments_count'
        );
        $template      = $this->template;
        $pattern       = get_shortcode_regex();
        if ($tmp_type == 'body') {
            $arr = $items['body'];
            $tmp = $template->template_body;
        } else {
            $arr = $items['loop'];
            $tmp = $template->template_loop;
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