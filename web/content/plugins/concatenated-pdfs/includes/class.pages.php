<?php
/*
	Still needs a good refactor
	- actions should be moved and ?page should be detected?
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class catpdf_pages {
    public $dompdf = NULL;
    public $message = array();
    public $title = '';
    function __construct() {
		global $_params;
        if (is_admin()) {
			if (isset($_params)) {
				if (isset($_params['catpdf_save_option'])) {// Check if option save is performed
					add_action('init', array( $this, 'update_options' ));// Add update option action hook
				}
				if (isset($_params['catpdf_export'])) {// Check if pdf export is performed
					add_action('init', array( $this, 'export' ));// Add export hook
				}
			}
			add_action('admin_init', array( $this, 'admin_init' ));
			add_action('admin_menu', array( $this, 'admin_menu' ));
		}
        if (isset($_GET['catpdf_dl'])) {// Check if post download is performed
            add_action('init', array( $this, 'download_post' ));// Add download action hook
        }
        if (isset($_GET['catpdf_post_dl'])) {// Check if single post download is performed
            add_action('init', array( $this, 'download_posts' ));// Add download action hook
        }
    }
    /*
     * Initailize plugin admin part
     */
    public function admin_init() {
		global $wp_scripts;
        // Enque style and script		
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-datepicker', CATPDF_URL.'js/ui/jquery.ui.datepicker.js', array('jquery'), '1.9.0', 'all');
		wp_enqueue_style('jquery-ui-datepicker', CATPDF_URL.'css/ui/jquery.ui.all.css', false, '1.9.0', 'all');
		
        wp_enqueue_script('jquery-ui-tabs', CATPDF_URL.'js/ui/jquery.ui.tabs.js', array('jquery'), '1.9.0', 'all');		
		wp_enqueue_style('jquery-ui-tabs', CATPDF_URL.'css/ui/jquery.ui.all.css', false, '1.9.0', 'all');
		// get registered script object for jquery-ui
		$ui = $wp_scripts->query('jquery-ui-core');
	 
		// tell WordPress to load the Smoothness theme from Google CDN
		$protocol = is_ssl() ? 'https' : 'http';
		$url = "$protocol://ajax.googleapis.com/ajax/libs/jqueryui/{$ui->ver}/themes/smoothness/jquery-ui.min.css";
		wp_enqueue_style('jquery-ui-smoothness', $url, false, null);

        wp_enqueue_script('catpdf-js', CATPDF_URL . 'js/catpdf.custom.js', array('jquery'), '', 'all');
        wp_enqueue_style('catpdfport-style', CATPDF_URL . 'css/style.css', false, '1.9.0', 'all');
    }
    /*
     * Add plugin menu
     */
    public function admin_menu() {
        // Register menu
        add_menu_page(CATPDF_NAME, CATPDF_NAME, 'manage_options', CATPDF_BASE_NAME, array( $this, 'option_page' ), CATPDF_URL . 'images/nav-icon.png');
        // Register sub-menu
        add_submenu_page(CATPDF_BASE_NAME, _('Download PDF'), _('Download PDF'), 'manage_options', 'catpdf-download-pdf', array( $this, 'download_page' ));
        add_submenu_page(CATPDF_BASE_NAME, _('Template Manager'), _('Template Manager'), 'manage_options', 'catpdf-template-manager', array( $this, 'template_manager_page' ));
        add_submenu_page(CATPDF_BASE_NAME, _('Add Template'), _('Add Template'), 'manage_options', 'catpdf-add-template', array( $this, 'add_page' ));
    }
    /*
     * Display "Add" page
     */
    public function add_page() { // short forward
		global $catpdf_templates,$shortcode;
        $data            = array();
        $data['message'] = $this->get_message();
		$body_templateShortCodes= $shortcode->get_template_shortcodes('body');
		$data['body_templateShortCodes']=$body_templateShortCodes;
		$loop_templateShortCodes= $shortcode->get_template_shortcodes('loop');
		$data['loop_templateShortCodes']=$loop_templateShortCodes;
        $this->view(CATPDF_PATH . '/includes/views/template.php', $data);
    }

	
    /*
     * Display "Template Manager" page
     */
    public function template_manager_page() {
		global $catpdf_templates,$shortcode;
        // Include list class
        include(CATPDF_PATH . '/includes/class.list.php');
        $wp_list_table = new template_list();
        $wp_list_table->prepare_items();
		
		$body_templateShortCodes= $shortcode->get_template_shortcodes('body');
		$data['body_templateShortCodes']=$body_templateShortCodes;
		$loop_templateShortCodes= $shortcode->get_template_shortcodes('loop');
		$data['loop_templateShortCodes']=$loop_templateShortCodes;
        // Check if edit action is performed
        if (isset($_GET['catpdf_action']) && $_GET['catpdf_action'] == 'edit') {
            $data['on_edit'] = $catpdf_templates->get_template($_GET['template']);
            $data['message'] = $this->get_message();
            // Display template form
            $this->view(CATPDF_PATH . '/includes/views/template.php', $data);
        } else {
            ob_start();
            $wp_list_table->display();
            $data['table']   = ob_get_clean();
            $data['message'] = $this->get_message();
            // Display template list
            $this->view(CATPDF_PATH . '/includes/views/template_manager.php', $data);
        }
    }
    /*
     * Display "Download" page
     */
    public function download_page() {
		global $catpdf_templates;
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


		$select_tags= '<select name="tags[]" multiple="multiple" class="postform" >';
		foreach ($post_types  as $post_type ) {
			$select_tags.='<option value="'. $post_type.'"  class="level-0" >'. $post_type. '</option>';
		}
		$select_tags.='</select>';

		
		
        $select_cats           = str_replace("name='cat' id=", "name='cat[]' multiple='multiple' id=", $select_cats);
        $select_cats           = str_replace("<option", '<option ', $select_cats);
        // Construct user dropdown
        $select_author         = wp_dropdown_users(array(
            'id' => 'author',
            'echo' => false
        ));
        $select_author         = str_replace("name='user' ", "name='user[]' multiple='multiple' ", $select_author);
        $select_author         = str_replace("<option", '<option ', $select_author);
		
		$data['select_tags']  = $select_tags;
		$data['select_types']  = $select_types;
        $data['select_cats']   = $select_cats;
        $data['select_author'] = $select_author;
        $data['select_sizes']  = array(
            'letter', '4a0', '2a0', 'a0', 'a1', 'a2', 'a3', 'a4', 'a5', 'a6', 'a7', 'a8', 'a9', 'a10', 'b0', 'b1', 'b2', 'b3', 'b4', 'b5', 'b6', 'b7', 'b8', 'b9', 'b10', 'c0', 'c1', 'c2', 'c3', 'c4', 'c5', 'c6c6', 'c7', 'c8', 'c9', 'c10', 'ra0', 'ra1', 'ra2', 'ra3', 'ra4', 'sra0', 'sra1', 'sra2', 'sra3', 'sra4', 'legal', 'ledger', 'tabloid', 'executive', 'folio', 'commerical #10 envelope', 'catalog #10 1/2 envelope', '8.5x11', '8.5x14', '11x17'
        );
        $data['select_ors']    = array(
            'portrait', 'landscape'
        );
        $data['option_url']    = "";//$tool_url;
        $data['templates']     = $catpdf_templates->get_template();
        $data['message']       = $this->get_message();
        // Display export form
        $this->view(CATPDF_PATH . '/includes/views/export.php', $data);
    }
	
    /*-------------------------------------------------------------------------*/
    /* -Option- 															   */
    /*-------------------------------------------------------------------------*/
    /*
     * Update plugin option
     */
    public function update_options() {
		global $_params;
        $options = $_params;
        update_option('catpdf_options', $options);
    }
    /*
     * Display "Option" page
     */
    public function option_page() {
		global $catpdf_templates,$catpdf_data;
        // Set options
        $data['options']   = $catpdf_data->get_options();
        // Get templates
        $data['templates'] = $catpdf_templates->get_template();
        // Display option form
        $this->view(CATPDF_PATH . '/includes/views/options.php', $data);
    }
    /*-------------------------------------------------------------------------*/
    /* -Export- 															   */
    /*-------------------------------------------------------------------------*/
    /*
     * Perform export pdf
     */
    public function export() {
        global $dompdf, $catpdf_output, $_params;
        
		$file = date("Now") . ".pdf";//need to fix this
		//would have saved recorde of publication to pull from
		$cached=false;//add check
        if(!$cached){
			$dompdf->set_paper($_params['papersize'], $_params['orientation']);
			$content     = $catpdf_output->custruct_template();
			$dompdf->load_html($content);
			$dompdf->render();
			$pdf = $dompdf->output();//store it for output
			//$dompdf->stream();
			$prettyname = trim($catpdf_output->title) . ".pdf";
			if( $catpdf_output->cachePdf($file,$pdf) ){
				$catpdf_output->sendPdf($file,$prettyname);
			}else{
				//send off error message	
			}
		}else{
			$catpdf_output->sendPdf($file);
		}
    }
    /*
     * Download post pdf
     */
    public function download_posts() {
        global $dompdf, $catpdf_output,$post;
        $param_arr   = array(
            'from' => (isset($_GET['from'])) ? urldecode($_GET['from']) : '',
            'to' => (isset($_GET['to'])) ? urldecode($_GET['to']) : '',
            'cat' => (isset($_GET['cat']) && $_GET['cat'] != '') ? explode(',', $_GET['cat']) : array(),
            'user' => (isset($_GET['author']) && $_GET['author'] != '') ? explode(',', $_GET['author']) : array(),
            'template' => (isset($_GET['template'])) ? urldecode($_GET['template']) : 'def'
        );
        $post  = $param_arr;
        
		$dompdf->set_paper((isset($_GET['paper_size'])) ? urldecode($_GET['paper_size']) : 'letter', (isset($_GET['paper_orientation'])) ? urldecode($_GET['paper_orientation']) : 'portrait');
		$content     = $catpdf_output->custruct_template();
		
        $dompdf->load_html($content);
        
        $dompdf->render();
        $dompdf->stream(trim($catpdf_output->title) . ".pdf");
    }
    /*
     * Download single post pdf
     */
    public function download_post() {
        global $dompdf, $catpdf_output,$post;
        $id          = $_GET['catpdf_dl'];
        $post        = $catpdf_output->query_posts($id);

        $single      = $post[0];
        $filenmae    = preg_replace('/[^a-z0-9]/i', '_', $single->post_title);
        $content     = $catpdf_output->custruct_template('single');
		$dompdf->set_paper('letter', 'portrait');
        $dompdf->load_html($content);
        
        $dompdf->render();
        $dompdf->stream(trim($filenmae) . ".pdf");
    }



    /*-------------------------------------------------------------------------*/
    /* -General- 															   */
    /*-------------------------------------------------------------------------*/
    /*
     * Return falsh message
     */
    public function get_message() {
		global $catpdf_core;
        if (!empty($catpdf_core->message)) {
            $arr = $catpdf_core->message;
			$message = "<div id='message' class='{$arr['type']}'><p>{$arr['message']}</p></div>";
			$catpdf_core->message=NULL;
            return $message;
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