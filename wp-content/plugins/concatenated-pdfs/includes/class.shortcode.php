<?php
/*
	Still needs a good refactor
	noted inline
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class shortcode {
    public $single;
	public $current_index_row=array();
	
	
    function __construct() {
        if (is_admin() || isset($_GET['catpdf_dl']) || isset($_GET['catpdf_post_dl'])) {
            $this->register_template_shortcodes();
        } else {
            add_shortcode('catpdf', array( $this, 'apply_download_button' ));
        }
    }
	
	/*
    * Return array
	* @attr
	*/
	public static function build_shortcodes(){ //this is a temp way
		$shortcodes = array(
			'loop'=> array('dis'=>__('Loop')),
			'site_title'=> array('dis'=>__('Site Title')),
			'site_tagline'=>array('dis'=> __('Site Tagline')),
			'site_url'=> array('dis'=>__('Site URL')),
			'date_today'=> array('dis'=>__('Date Today')),
			'from_date'=> array('dis'=>__('Date(From)')),
			'to_date'=> array('dis'=>__('Date(To)')),
			'categories'=> array('dis'=>__('Categories')),
			'post_count'=>array('dis'=> __('Post Count')),
			'title'=> array('dis'=>__('Title')),
			'excerpt'=> array('dis'=>__('Excerpt')),
			'content'=> array('dis'=>__('Content')),
			'permalink'=> array('dis'=>__('Permalink')),
			'date'=> array('dis'=>__('Date')),
			'author'=> array('dis'=>__('Author')),
			'author_photo'=> array('dis'=>__('Author Photo')),
			'author_description'=> array('dis'=>__('Author Description')),
			'status'=> array('dis'=>__('Status')),
			'featured_image'=> array('dis'=>__('Featured Image')),
			'category'=> array('dis'=>__('Category')),
			'tags'=> array('dis'=>__('Tags')),
			'comments_count'=> array('dis'=>__('Comments Count')),
			'version_count'=> array('dis'=>__('Number of versions')),
			'page_numbers'=> array('dis'=>__('Page Numbering block')),
			'index_loop'=>array('dis'=>__('The loop of the index items')),
			'index_row'=>array('dis'=>__('An index item')),
			'index_row_chapter'=>array('dis'=>__('chapter of an index item')),
			'index_row_text'=>array('dis'=>__('text of an index item')),
			'index_row_segment'=>array('dis'=>__('segment of an index item')),
			'index_row_page'=>array('dis'=>__('page # of an index item')),
		);
		return $shortcodes;
	}



    /*
    * Register template shortcodes
	* should be a little more robust here... 
    */
    public function register_template_shortcodes() {
        $shortcodes = shortcode::build_shortcodes();
		foreach($shortcodes as $code=>$props){
			$_func = $code.'_func';
			if( method_exists($this,$_func) ){
				add_shortcode($code, array( $this, $_func ));
			}
		}
    }
	
	public static function get_template_section_shortcodes($template='body'){
		//would be pulled from a reg
		$registered_codes = array(
			'body' => array(
					'loop','site_title','site_tagline','site_url','date_today',
					'from_date','to_date','categories','post_count','page_numbers'
				),
			'loop' => array(
					'title','excerpt','content','permalink',
					'date','author','author_photo','author_description',
					'status','featured_image','category','tags','comments_count','version_count'
				),
			'pageheader' => array(
				'site_title','site_tagline','site_url','date_today','title',
				'from_date','to_date','categories','post_count','page_numbers'
			),
			'pagefooter' => array(
				'site_title','site_tagline','site_url','date_today','title',
				'from_date','to_date','categories','post_count','page_numbers'
			),
			'index' => array(
				'index_loop',
			),
			'index_loop' => array(
				'index_row',
			),
			'index_row' => array(
				'index_row_chapter','index_row_text','index_row_segment','index_row_page',
			),
		);
		if (isset( $registered_codes[$template] ) ) return $registered_codes[$template];
		return array();
	}

	public static function get_template_shortcodes($template='body'){
		$shortcodes = shortcode::build_shortcodes();
		$usingCodes = shortcode::get_template_section_shortcodes($template);
		$returning = array();
		foreach($shortcodes as $code=>$props){
			if(in_array($code,$usingCodes)){
				$returning[$code]= $props['dis'];
			}
		}
		return $returning;
	}
	
	
/******************
* Functions
*******************/	

    /*
    * Display download button
    */
    public function apply_download_button($atts) {
        $link                  = '';
        $text                  = (isset($atts['text'])) ? $atts['text'] : 'Download';
		$target                = (isset($atts['target'])) ? $atts['target'] : '_blank';
        $atts['catpdf_post_dl']= (isset($atts['catpdf_post_dl'])) ? $atts['catpdf_post_dl'] : 'true';
        if (count($atts) > 0) {
            foreach ($atts as $key => $att) {
                $atts[$key] = urlencode($att);
            }
        }
        if (isset($atts['text'])) {
            unset($atts['text']);
        }
        if (isset($atts['target'])) {
            unset($atts['target']);
        }		

        $dllink = add_query_arg($atts);
        $link   = sprintf('<a href="%1$s" target="%3$s" title="%2$s">%2$s</a>'."\n", $dllink, $text, $target);
        return $link;
    }
	
	
	
	
    /*
    * Return page numbering block
    */
    public function page_numbers_func($atts) {
		extract(shortcode_atts(array(
			'label' => '{PTx}',
			'separator' => '{P#S}'
		), $atts));
		$block='<div id="page_numbers"><span id="pn_text">'.$label.'</span><span id="pn_number">{P#}'.$separator.'{PT#}</span></div>'."\n";
        return $block;
    }	
	
	
    /*
    * Return page numbering block
    */
    public function index_func($atts) {
		$block='[index_row]';
	/*	
						'index_loop'=>array('dis'=>__('The loop of the index items')),
			'index_row'=>array('dis'=>__('An index item')),
			'index_row_chapter'=>array('dis'=>__('chapter of an index item')),
			'index_row_text'=>array('dis'=>__('text of an index item')),
			'index_row_segment'=>array('dis'=>__('segment of an index item')),
			'index_row_page'=>array('dis'=>__('page # of an index item')),		
		
<?php
$index.= "";
$c=1;
foreach($posts as $post){
?>
<table class='indexed_chapter'>
  <tbody>
    <tr>
      <td class='chapter' width='15%' align='right' cellspacing='0' cellpadding='0' >{chapter{$c}}</td>
      <td class='text' width='25%' align='left' cellspacing='0' cellpadding='0' >{text{$c}}</td>
      <td class='segment' align='right' cellspacing='0' cellpadding='0' ></td>
      <td class='pagenumber' width='5%' align='left' cellspacing='0' cellpadding='0' >{page{$c}}</td>
    </tr>
  </tbody>
</table>
<?php
	
}
?>
		
		*/
		
		
        return $block;
    }	
	public function index_loop_func($atts) {
		global $posts,$catpdf_output,$catpdf_templates,$current_index_row;
		$c=1;
		foreach($posts as $post){
			$current_index_row=array(
				"chapter"=>"chapter ${c}",
				"text"=>"text ${c}",
				"segment"=>"segment ${c}",
				"page"=>"page ${c}",
			);
			$block.=$catpdf_output->filter_shortcodes("index_row",$catpdf_templates->resolve_template("index-table-row.php"));
			$c++;
		}
		//var_dump($block);
		return $block;
	}
	


	public function index_row_chapter_func($atts) {
		global $current_index_row;
		$block = $current_index_row["chapter"];
		return $block;
	}

	public function index_row_text_func($atts) {
		global $current_index_row;
		$block = $current_index_row["text"];
		return $block;
	}

	public function index_row_segment_func($atts) {
		global $current_index_row;
		$block = $current_index_row["segment"];
		return $block;
	}

	public function index_row_page_func($atts) {
		global $current_index_row;
		$block = $current_index_row["page"];
		return $block;
	}

    /*
    * Return post content
    */
    public function content_func() {
        global $post;
        $item = '';
        $post = $this->single;
        setup_postdata($post);
        $item = get_the_content();
		$title = get_the_title();
			$indexerscript='
<script type="text/php"> if(isset($GLOBALS["i"])){ $i=$GLOBALS["i"]; if(!isset($GLOBALS["chapters"][$i])){ $GLOBALS["chapters"][$i]["page"] = $pdf->get_page_number();  $GLOBALS["chapters"][$i]["text"] = "'.$title.'"; $GLOBALS["i"]=$i+1;} } </script>
'."\n";
			$indexedcontent=$indexerscript.$item;
			$item=$indexedcontent;
        return $item;
    }
    /*
    * Return post excerpt
    */
    public function excerpt_func() {
        global $post;
        $item = '';
        $post = $this->single;
        setup_postdata($post);
        $item = get_the_excerpt();
        return $item;
    }
    /*
    * Return post tags list
    * @atts - array
    */	
	public function version_count_func(){
		global $post;
		setup_postdata($post);
		$revisions=wp_get_post_revisions(get_the_ID());
		return count($revisions);
	}
    /*
    * Return post tags list
    * @atts - array
    */
    public function tags_func($atts) {
        global $post;
        extract(shortcode_atts(array(
            'delimiter' => ',',
            'label' => ''
        ), $atts));
        $item = '';
        $post = $this->single;
        setup_postdata($post);
        $posttags = get_the_tags();
        if ($posttags) {
            foreach ($posttags as $tag) {
                $item .= ucwords($tag->name) . $delimiter;
            }
            $item = substr($item, 0, -strlen($delimiter));
            $item = $label . $item;
        }
        return $item;
    }
    /*
    * Return post category list
    * @atts - array
    */
    public function category_func($atts) {
        global $post;
        extract(shortcode_atts(array(
            'delimiter' => ',',
            'label' => ''
        ), $atts));
        $item = '';
        $post = $this->single;
        setup_postdata($post);
        $cat_arr = (array) get_the_category(get_the_ID());
        if (count($cat_arr) > 0) {
            foreach ($cat_arr as $arr) {
                $item .= ucwords($arr->name) . $delimiter;
            }
            $item = substr($item, 0, -strlen($delimiter));
            $item = $label . $item;
        }
        return $item;
    }
    /*
    * Return post featured image
    * @atts - array
    */
    public function featured_image_func($atts) {
        global $post;
        $post = $this->single;
        extract(shortcode_atts(array(
            'size' => 'thumbnail'
        ), $atts));
        $item = '';
        setup_postdata($post);
        $item = get_the_post_thumbnail(get_the_ID(), $size);
        return $item;
    }
    /*
    * Return post status
    */
    public function status_func() {
        global $post;
        $post = $this->single;
        setup_postdata($post);
        $item = get_post_status(get_the_ID());
        return $item;
    }
    /*
    * Return post author description
    */
    public function author_description_func() {
        global $post;
        $post = $this->single;
        setup_postdata($post);
        $item = get_the_author_description();
        return $item;
    }
    /*
    * Return post author photo
    * @atts - array
    */
    public function author_photo_func($atts) {
        global $post;
        extract(shortcode_atts(array(
            'size' => '96'
        ), $atts));
        $post = $this->single;
        setup_postdata($post);
        $item = get_avatar(get_the_author_ID(), $size);
        return $item;
    }
    /*
    * Return post author
	*/
    public function author_func() {
        global $post;
        $post = $this->single;
        setup_postdata($post);
        $item = get_the_author();
        return $item;
    }
    /*
    * Return post date
    * @atts - array
    */
    public function date_func($atts) {
        global $post;
        extract(shortcode_atts(array(
            'format' => 'F d,Y'
        ), $atts));
        $post = $this->single;
        setup_postdata($post);
        $item = date($format, strtotime(get_the_date()));
        return $item;
    }
    /*
    * Return post permalink
    */
    public function permalink_func() {
        $post = $this->single;
        $item = get_permalink(get_the_ID());
        return $item;
    }
    /*
    * Return post title
    */
    public function title_func() {
        global $post;
        $post = $this->single;
        setup_postdata($post);
        $item = get_the_title();
        return $item;
    }
    /*
    * Return comment count
    */
    public function comments_count_func() {
        global $post, $structure;
        $post = $this->single;
        setup_postdata($post);
        $num = get_comments_number(0, 1, '%');
        return $num;
    }
    /*
    * Return loop html
    */
    public function loop_func() {
        global $catpdf_output,$posts;
        $item = '';
        if (count($posts) > 0) {
            foreach ($posts as $post) {
                $this->single = $post;
				$postHtml = $catpdf_output->filter_shortcodes('loop');
                $item .= $postHtml;
            }
        }
        return $item;
    }
    /*
    * Return found post count
    */
    public function post_count_func() {
        global $catpdf_core;
        $item = count($catpdf_core->posts);
        return $item;
    }
    /*
    * Return active categories
    * @atts - array
    */
    public function categories_func($atts) {
        global $structure;
        extract(shortcode_atts(array(
            'delimiter' => ','
        ), $atts));
        $item = '';
        if (isset($structure->post['cat']) && ($structure->post['cat']) > 0) {
            foreach ($structure->post['cat'] as $row) {
                $cat_arr = get_category($row);
                $item .= $cat_arr->cat_name . $delimiter;
            }
        }
        return substr($item, 0, -strlen($delimiter));
    }
    /*
    * Return site title
    */
    public function site_title_func() {
        $item = get_bloginfo('name');
        return $item;
    }
    /*
    * Return site tagline
    */
    public function site_tagline_func() {
        $item = get_bloginfo('description');
        return $item;
    }
    /*
    * Return site url
    */
    public function site_url_func($atts) {
		extract(shortcode_atts(array(
            'link' => false,
			'text' => ''
        ), $atts));
        $url = get_bloginfo('url');
		$text = $text=='' ? $url : $text;
		$item = !$link ? $url : "<a href='{$url}'>{$text}</a>";
        return $item;
    }
    /*
    * Return today's date
    */
    public function date_today_func($atts) {
        extract(shortcode_atts(array(
            'format' => 'F d,Y'
        ), $atts));
        $item = date($format);
        return $item;
    }
    /*
    * Return filter from date
    */
    public function from_date_func($atts) {
        global $structure;
        extract(shortcode_atts(array(
            'format' => 'F d,Y',
            'label' => ''
        ), $atts));
        $item = '';
        if (isset($structure->post['from']) && $structure->post['from'] != '') {
            $item = $label . ' ' . date($format, strtotime($structure->post['from']));
        }
        return $item;
    }
    /*
    * Return filter to date
    */
    public function to_date_func($atts) {
        global $structure;
        extract(shortcode_atts(array(
            'format' => 'F d,Y',
            'label' => ''
        ), $atts));
        $item = '';
        if (isset($structure->post['to']) && $structure->post['to'] != '') {
            $item = $label . ' ' . date($format, strtotime($structure->post['to']));
        }
        return $item;
    }
}
?>