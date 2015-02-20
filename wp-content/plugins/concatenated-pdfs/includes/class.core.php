<?php


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class catpdf_core {
    public $dompdf = NULL;
	public $PDFMerger = NULL;
	public $shortcode = NULL;
	public $catpdf_pages = NULL;
	public $catpdf_templates = NULL;
	public $catpdf_output = NULL;
	public $catpdf_data = NULL;
	
    public $message = array();
    public $post = array();
    public $title = '';
    public $posts;
	public $_params;
    function __construct() {
		global $dompdf,$shortcode,$catpdf_pages,$catpdf_templates,$catpdf_output,$catpdf_data,$_params;
		$_params = $_REQUEST;

		// Include data
		include(CATPDF_PATH . '/includes/class.data.php');
		$catpdf_data = new catpdf_data();
		$options = $catpdf_data->get_options();
		
		if($options["postdl"] == 1){
			// Include dompdf //make sure to get back to pulling this in to the settings
			include(CATPDF_PATH . '/includes/dompdf_config.php');
			$dompdf = new DOMPDF();
	
			include(CATPDF_PATH . '/includes/PDFMerger/PDFMerger.php');
			$PDFMerger = new PDFMerger;
		}
		
		
		// Include shortcode class
		include(CATPDF_PATH . '/includes/class.shortcode.php');
		$shortcode = new shortcode();
		
		// Include functions
		include(CATPDF_PATH . '/includes/functions.php');

		// Include page
		include(CATPDF_PATH . '/includes/class.pages.php');
		$catpdf_pages = new catpdf_pages();

		// Include templates
		include(CATPDF_PATH . '/includes/class.templates.php');
		$catpdf_templates = new catpdf_templates();
		
		// Include output
		include(CATPDF_PATH . '/includes/class.output.php');
		$catpdf_output = new catpdf_output();

		
		if($options["postdl"] == 1){
			if (!is_admin()) {
				 // Initialize public functions
				 add_filter('the_content', array( $this, 'apply_post_download_button' ));
			}else{
				add_action( 'add_meta_boxes', array( $this, 'add_pdf_meta_boxes' ) );	
				add_action( 'save_post', array( $this, 'save' ) );
			}
		}
    }
	
	/**
	 * Initialize install.
	 *
	 * Basicly we are just going to make folders if needed
	 */
	public function install_init() {
		if (!file_exists (CATPDF_LOG_PATH) && !mkdir(CATPDF_LOG_PATH, 0777, true)) {
			die('Failed to create folders...');
		}
		if (!file_exists (CATPDF_MERGING_PATH) && !mkdir(CATPDF_MERGING_PATH, 0777, true)) {
			die('Failed to create folders...');
		}
	}
	
	/**
	 * Add meta boxes used to capture pieces of information for the profile.
	 *
	 * @param string $post_type
	 */
	public function add_pdf_meta_boxes( $post_type ){
		$post_types      = get_post_types( array( 'public'   => true  ), 'names', 'and' );
		
		//$post_types = array('post', 'page');     //limit meta box to certain post types
		if ( in_array( $post_type, $post_types )) {
			add_meta_box(
				CATPDF_KEY.'_pdf_config_map', 
				'PDF Config',
				array( $this, 'display_pdf_config_map_meta_box' ),
				$post_type,
				'side',
				'default'
			);
		}	
	}

	/**
     *
	 * Display a meta box of the captured html.  This is just displaying the post content, so it's 
	 * not really the meta of the post, but it'll work for our needs.
	 *
	 * @global class $scrape_core
	 *
	 * @param WP_Post $post The full post object being edited.
	 */
	public function display_pdf_config_map_meta_box( $post ) {
		global $shortcode;
		// Use get_post_meta to retrieve an existing value from the database.
		$value = (array)json_decode(get_post_meta( $post->ID, CATPDF_KEY.'_post_pdf_config', true ));
		$selected = isset($value["generation_allowed"])&&!empty($value["generation_allowed"])?$value["generation_allowed"]:"yes";
		?>
		<p>Allowed to generate PDF's?</p>
		<div class="html radio_buttons">
			<select name="<?=CATPDF_KEY.'_pdf_config[generation_allowed]'?>"/>
				<option value="yes" <?=selected("yes", $selected)?>>Yes</option>
				<option value="no" <?=selected("no", $selected)?>>No</option>
			</select>
		</div>
		<p class="description">Is PDF generation allowed for this post?</p>
		
		<hr/>
		<p>Auto generate pdf's?</p>
		<div class="html radio_buttons">
			<select name="<?=CATPDF_KEY.'_pdf_config[auto_generate]'?>"/>
				<option value="yes" <?=selected("yes", (isset($value["auto_generate"])&&!empty($value["auto_generate"])?$value["auto_generate"]:"yes"))?>>Yes</option>
				<option value="no" <?=selected("no", (isset($value["auto_generate"])&&!empty($value["auto_generate"])?$value["auto_generate"]:"yes"))?>>No</option>
			</select>
		</div>
		<p class="description">Should there be PDF generated for this post?</p>
		<hr/>
		<p> Pre view the PDF</p>
		<p class="description"><b>NOTE:</b> The preview is for this page only, meaning that there will be no cover, index, or anything of that nature.  Only the content as if it was in the middle of the docment.</p>
		<?=$shortcode->apply_download_button(array('text'=>'Preview Download Link','catpdf_dl'=>$post->ID,'target'=>'_blank' ))?>
		<?php
	}
	/**
	 * Save the meta when the post is saved.
	 *
	 * @param int $post_id The ID of the post being saved.
	 */
	public function save( $post_id ) {
		
		/**
     	 * We need to verify this came from the our screen and with proper authorization,
		 * because save_post can be triggered at other times.
		add this in later
		
		// Check if our nonce is set.
		if ( ! isset( $_POST['myplugin_inner_custom_box_nonce'] ) ){
			return $post_id;
		}

		$nonce = $_POST['myplugin_inner_custom_box_nonce'];

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $nonce, 'myplugin_inner_custom_box' ) )
			return $post_id;

		// If this is an autosave, our form has not been submitted,
                //     so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
			return $post_id;

		// Check the user's permissions.
		if ( 'page' == $_POST['post_type'] ) {

			if ( ! current_user_can( 'edit_page', $post_id ) )
				return $post_id;
	
		} else {

			if ( ! current_user_can( 'edit_post', $post_id ) )
				return $post_id;
		}
 		*/
		/* OK, its safe for us to save the data now. */

		// Sanitize the user input.
		$pdfConfig = $_POST[CATPDF_KEY.'_pdf_config'];

		// Update the meta field.
		update_post_meta( $post_id, CATPDF_KEY.'_post_pdf_config', json_encode($pdfConfig) );
	}
		
				
    /**
     * Returns download button link
	 *
	 * @return string
     */
    public function apply_post_download_button($content) {
        /*if ($GLOBALS['post']->post_type == 'post') {
            $id   = $GLOBALS['post']->ID;
            $url  = add_query_arg('catpdf_dl', $id);
            $link = '<a href="' . $url . '"><img src="' . CATPDF_URL . 'images/download-icon.png"></a>';
            return $content . $link;
        }*/
		return $content;
    }



}
?>