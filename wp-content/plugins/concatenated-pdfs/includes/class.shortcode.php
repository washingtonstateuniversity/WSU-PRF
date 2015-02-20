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
        if ( is_admin() || ( isset($_GET['catpdf']) && $_GET['catpdf']=="run" ) ) {
            $this->register_template_shortcodes();
        } else {
            add_shortcode('catpdf', array( $this, 'apply_download_button' ));
			add_shortcode('catpdf_skip', array( $this, 'catpdf_skip_func' ));
        }
    }
	
	/**
     * All possible shortcodes for this plugin
	 *
	 * @return array
	 */
	public static function build_shortcodes(){ //this is a temp way
		$shortcodes = array(
			'catpdf_skip'=>array('dis'=>__('do not print to pdf the contents')),
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
			'index_row_chapter_text'=>array('dis'=>__('chapter text of an index item')),
			'index_row_chapter_number'=>array('dis'=>__('chapter number of an index item')),
			'index_row_text'=>array('dis'=>__('text of an index item')),
			'index_row_segment'=>array('dis'=>__('segment of an index item')),
			'index_row_page_start'=>array('dis'=>__('starting page # of an index item')),
			'index_row_page_end'=>array('dis'=>__('ending page # of an index item')),
		);
		return $shortcodes;
	}



    /**
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
	
	
	public function catpdf_skip_func( $atts, $content = null ) {
		global $producing_pdf;
		return $producing_pdf?"":do_shortcode($content);
	}

	
	
	
	public static function get_template_section_shortcodes($template='body'){
		//would be pulled from a reg
		$registered_codes = array(
			'body' => array(
					'catpdf_skip','loop','site_title','site_tagline','site_url','date_today',
					'from_date','to_date','categories','post_count','page_numbers'
				),
			'loop' => array(
					'catpdf_skip','title','excerpt','content','permalink',
					'date','author','author_photo','author_description',
					'status','featured_image','category','tags','comments_count','version_count'
				),
			'pageheader' => array(
				'catpdf_skip','site_title','site_tagline','site_url','date_today','title',
				'from_date','to_date','categories','post_count','page_numbers'
			),
			'pagefooter' => array(
				'catpdf_skip','site_title','site_tagline','site_url','date_today','title',
				'from_date','to_date','categories','post_count','page_numbers'
			),
			'index' => array(
				'index_loop',
			),
			'index_loop' => array(
				'index_row',
			),
			'index_row' => array(
				'index_row_chapter_text','index_row_chapter_number','index_row_text','index_row_segment','index_row_page_start','index_row_page_end',
			),
		);
		if (isset( $registered_codes[$template] ) ){
			return $registered_codes[$template];
		}
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
    /**
     * Return html with filtered shortcodes
     * @tmp_type - string
	 * needs to be reworked
	 * also move to class.shortcuts
     */
    public function filter_shortcodes($tmp_type=NULL,$html=null) {
		global $catpdf_templates;
		if($tmp_type==NULL){
			return false;
		}

        $template      = $catpdf_templates->get_default_template();
		
        $pattern       = get_shortcode_regex();

		$arr = array_keys(shortcode::get_template_shortcodes(!empty($tmp_type)?$tmp_type:'body')); //? was ? isset($items[$tmp_type])?$items[$tmp_type]:$items['body'] into get_template_shortcodes
		//var_dump($template);
		//var_dump($tmp_type);
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
	
/*******************
 * Functions
 *******************/	

    /**
     * Display download button
	 * 
     * @param array $atts
	 *
	 * @return string
     */
    public function apply_download_button($atts) {
		global $post;
        $link                  = '';
        $text                  = (isset($atts['text'])) ? $atts['text'] : 'Download';
		$target                = (isset($atts['target'])) ? $atts['target'] : '_blank';
		$atts['catpdf']="run";
		
		if($atts['all_type']!="true"){
			$atts['catpdf_dl']= $post->ID;
			unset($atts['all_type']);
		}

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
	
	
	
	
    /**
     * Return page numbering block
	 * 
     * @param array $atts
	 *
	 * @return string
     */
    public function page_numbers_func($atts) {
		global $in_catpdf_shortcode;
		$in_catpdf_shortcode=true;
		extract(shortcode_atts(array(
			'label' => '{PTx}',
			'separator' => '{P#S}'
		), $atts));
		$block='<div id="page_numbers"><span id="pn_text">'.$label.'</span><span id="pn_number">{P#}'.$separator.'{PT#}</span></div>'."\n";

		
		/* the best corse maybe to dynamicly fill in the numbers
		http://asserttrue.blogspot.com/2011/04/script-for-putting-page-numbers-on-pdf.html#
		var inch = 72;
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
		}
		*/
		$in_catpdf_shortcode=false;
        return $block;
    }	
	
	
    /**
     * Return page numbering block
	 * 
     * @param array $atts
	 *
	 * @return string
     */
    public function index_func($atts) {
		global $in_catpdf_shortcode;
		$in_catpdf_shortcode=true;
		$block='[index_row]';
		$in_catpdf_shortcode=false;
        return $block;
    }	
	public function index_loop_func($atts) {
		global $posts,$catpdf_output,$catpdf_templates,$current_index_row,$chapters,$in_catpdf_shortcode;
		$in_catpdf_shortcode=true;
		$c=1;
		foreach($chapters as $chapter){
			$current_index_row=array(
				"chapter_text"=>( isset( $chapter["chapter"] ) && !empty( $chapter["chapter"] ) ) ? $chapter["chapter"] : "",
				"chapter_number"=>$c,
				"text"=>$chapter["text"],
				"segment"=>"",
				"page_start"=>$chapter["page_start"],
				"page_end"=>$chapter["page_end"],
				"show_ch_num"=>$chapter["show_ch_num"],
			);
			$block.=$this->filter_shortcodes("index_row",$catpdf_templates->resolve_template("index-table-row.php"));
			$c++;
		}
		var_dump($chapters);
		var_dump($block);
		$in_catpdf_shortcode=false;
		return $block;
	}
	

    /**
     * Return chapter
	 * 
     * @param array $atts
	 *
	 * @return string
     */	
	public function index_row_chapter_text_func($atts) {
		global $current_index_row,$in_catpdf_shortcode;
		$in_catpdf_shortcode=true;
		extract(shortcode_atts(array(
            'text' => ''
        ), $atts));
		$block = ( isset($current_index_row["chapter_text"]) && !empty($current_index_row["chapter_text"]) ) ? $current_index_row["chapter_text"] : $text;
		$in_catpdf_shortcode=false;
		return $block;
	}
	
    /**
     * Return chapter
	 * 
     * @param array $atts
	 *
	 * @return string
     */	
	public function index_row_chapter_number_func($atts) {
		global $current_index_row,$in_catpdf_shortcode;
		$in_catpdf_shortcode=true;
		$block = $current_index_row["show_ch_num"]=="true"?$current_index_row["chapter_number"]:"";
		$in_catpdf_shortcode=false;
		return $block;
	}

    /**
     * Return row text
	 * 
     * @param array $atts
	 *
	 * @return string
     */	
	public function index_row_text_func($atts) {
		global $current_index_row,$in_catpdf_shortcode;
		$in_catpdf_shortcode=true;
		$block = $current_index_row["text"];
		$in_catpdf_shortcode=false;
		return $block;
	}
	
    /**
     * Return row segment
	 * 
     * @param array $atts
	 *
	 * @return string
     */	
	public function index_row_segment_func($atts) {
		global $current_index_row,$in_catpdf_shortcode;
		$in_catpdf_shortcode=true;
		$block = $current_index_row["segment"];
		$in_catpdf_shortcode=false;
		return $block;
	}
	
    /**
     * Return row page number
	 * 
     * @param array $atts
	 *
	 * @return string
     */	
	public function index_row_page_start_func($atts) {
		global $current_index_row,$in_catpdf_shortcode;
		$in_catpdf_shortcode=true;
		$block = $current_index_row["page_start"];
		$in_catpdf_shortcode=false;
		return $block;
	}
	
    /**
     * Return row page number
	 * 
     * @param array $atts
	 *
	 * @return string
     */	
	public function index_row_page_end_func($atts) {
		global $current_index_row,$in_catpdf_shortcode;
		$in_catpdf_shortcode=true;
		$block = $current_index_row["page_end"];
		$in_catpdf_shortcode=false;
		return $block;
	}




	public function get_indexer($title,$chapter="",$show_num="true"){
        global $catpdf_output;
		return '
			<script type="text/php">
				'.$catpdf_output->get_pdf_php_globals().'
				if($indexable){
					$count = $PAGE_COUNT;
					$parts=array(
						"page_start"=>$pages+1,
						"page_end"=>$pages+1+$count,
						"text"=>"'.$title.'",
						"chapter"=>"'.$chapter.'",
						"show_ch_num"=>"'.$show_num.'"
					);
					$chapters[$interation]=$parts;
					$interation++;
				}
				$repeater=$count;
			</script>
			'."\n";
	}



    /**
     * Return post content
	 *
	 * @return string
     */
    public function content_func() {
        global $post,$in_catpdf_shortcode;
		$in_catpdf_shortcode=true;
        $item = '';
        $title = $post->post_title;
        $item = $this->get_indexer($title).do_shortcode($post->post_content);
		$in_catpdf_shortcode=false;
        return $item;
    }
    /**
     * Return post excerpt
	 *
	 * @return string
     */
    public function excerpt_func() {
        global $post,$in_catpdf_shortcode;
		$in_catpdf_shortcode=true;
		$in_catpdf_shortcode=false;
        return $post->post_excerpt;
    }
    /**
     * Return post tags list
	 * 
     * @param array $atts
	 *
	 * @return string
     */	
	public function version_count_func(){
		global $post,$in_catpdf_shortcode;
		$in_catpdf_shortcode=true;
		//setup_postdata($active_post);
		$revisions=wp_get_post_revisions($post->ID);
		$in_catpdf_shortcode=false;
		return count($revisions);
	}
    /**
     * Return post tags list
	 * 
     * @param array $atts
	 *
	 * @return string
     */
    public function tags_func($atts) {
        global $post,$in_catpdf_shortcode;
		$in_catpdf_shortcode=true;
        extract(shortcode_atts(array(
            'delimiter' => ',',
            'label' => ''
        ), $atts));
        $item = '';
        //$post = $this->single;
        //setup_postdata($active_post);
        $posttags = get_the_tags();
        if ($posttags) {
            foreach ($posttags as $tag) {
                $item .= ucwords($tag->name) . $delimiter;
            }
            $item = substr($item, 0, -strlen($delimiter));
            $item = $label . $item;
        }
		$in_catpdf_shortcode=false;
        return $item;
    }
    /**
     * Return post category list
	 * 
     * @param array $atts
	 *
	 * @return string
     */
    public function category_func($atts) {
        global $post,$in_catpdf_shortcode;
		$in_catpdf_shortcode=true;
        extract(shortcode_atts(array(
            'delimiter' => ',',
            'label' => ''
        ), $atts));
        $item = '';
        //$post = $this->single;
        //setup_postdata($active_post);
        $cat_arr = (array) get_the_category($post->ID);
        if (count($cat_arr) > 0) {
            foreach ($cat_arr as $arr) {
                $item .= ucwords($arr->name) . $delimiter;
            }
            $item = substr($item, 0, -strlen($delimiter));
            $item = $label . $item;
        }
		$in_catpdf_shortcode=false;
        return $item;
    }
    /**
     * Return post featured image
	 * 
     * @param array $atts
	 *
	 * @return string
     */
    public function featured_image_func($atts) {
        global $post,$in_catpdf_shortcode;
		$in_catpdf_shortcode=true;
        //$post = $this->single;
        extract(shortcode_atts(array(
            'size' => 'thumbnail'
        ), $atts));
        $item = '';
        //setup_postdata($active_post);
        $item = get_the_post_thumbnail($post->ID, $size);
		$in_catpdf_shortcode=false;
        return $item;
    }
    /**
     * Return post status
	 *
	 * @return string
     */
    public function status_func() {
        global $post,$in_catpdf_shortcode;
		$in_catpdf_shortcode=true;
		$in_catpdf_shortcode=false;
        return $post->post_status;
    }
    /**
     * Return post author description
	 *
	 * @return string
     */
    public function author_description_func() {
        global $post,$in_catpdf_shortcode;
		$in_catpdf_shortcode=true;
        //$post = $this->single;
        //setup_postdata($active_post);
        $item = get_the_author_description();
		$in_catpdf_shortcode=false;
        return $item;
    }
    /**
     * Return post author photo
	 * 
     * @param array $atts
	 *
	 * @return string
     */
    public function author_photo_func($atts) {
        global $post,$in_catpdf_shortcode;
		$in_catpdf_shortcode=true;
        extract(shortcode_atts(array(
            'size' => '96'
        ), $atts));
        //$post = $this->single;
        //setup_postdata($active_post);
        $item = get_avatar($post->post_author, $size);
		$in_catpdf_shortcode=false;
        return $item;
    }
    /**
     * Return post author
	 *
	 * @return string
	 */
    public function author_func() {
        global $post,$in_catpdf_shortcode;
		$in_catpdf_shortcode=true;
		$in_catpdf_shortcode=false;
        return $post->post_author;
    }
    /**
     * Return post date
	 * 
     * @param array $atts
	 *
	 * @return string
     */
    public function date_func($atts) {
        global $post,$in_catpdf_shortcode;
		$in_catpdf_shortcode=true;
        extract(shortcode_atts(array(
            'format' => 'F d,Y'
        ), $atts));
       	//$post = $this->single;
        //setup_postdata($active_post);
        $item = date($format, strtotime($post->post_modified));
		$in_catpdf_shortcode=false;
        return $item;
    }
    /**
     * Return post permalink
	 *
	 * @return string
     */
    public function permalink_func() {
		global $post,$in_catpdf_shortcode;
		$in_catpdf_shortcode=true;
        //$post = $this->single;
        $item = get_permalink($post->ID);
		$in_catpdf_shortcode=false;
        return $item;
    }
    /**
     * Return post title
	 *
	 * @return string
     */
    public function title_func() {
        global $post,$in_catpdf_shortcode;
		$in_catpdf_shortcode=true;
        return $post->post_title;
		$in_catpdf_shortcode=false;
    }
    /**
     * Return comment count
	 *
	 * @return string
     */
    public function comments_count_func() {
        global $post,$in_catpdf_shortcode;
		$in_catpdf_shortcode=true;
        //$post = $this->single;
        //setup_postdata($active_post);
        $num = get_comments_number(0, 1, '%');
		$in_catpdf_shortcode=false;
        return $num;
    }
    /**
     * Return loop html
	 *
	 * @return string
     */
    public function loop_func() {
        global $catpdf_templates,$catpdf_output,$post,$in_catpdf_shortcode;
		$in_catpdf_shortcode=true;
        $item = '';
        //$this->single = $post;
		$postHtml = $this->filter_shortcodes('loop',$catpdf_templates->resolve_template('concat-loop.php'));
		$in_catpdf_shortcode=false;
        return $postHtml;
    }
    /**
     * Return found post count
	 *
	 * @return string
     */
    public function post_count_func() {
        global $catpdf_core,$in_catpdf_shortcode;
		$in_catpdf_shortcode=true;
        $item = count($catpdf_core->posts);
		$in_catpdf_shortcode=false;
        return $item;
    }
    /**
     * Return active categories
	 * 
     * @param array $atts
	 *
	 * @return string
     */
    public function categories_func($atts) {
        global $structure,$in_catpdf_shortcode;
		$in_catpdf_shortcode=true;
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
		$in_catpdf_shortcode=false;
        return substr($item, 0, -strlen($delimiter));
    }
    /**
     * Return site title
	 *
	 * @return string
     */
    public function site_title_func() {
		global $in_catpdf_shortcode;
		$in_catpdf_shortcode=true;
        $item = get_bloginfo('name');
		$in_catpdf_shortcode=false;
        return $item;
    }
    /**
     * Return site tagline
	 * 
	 * @return string
     */
    public function site_tagline_func() {
		global $in_catpdf_shortcode;
		$in_catpdf_shortcode=true;
        $item = get_bloginfo('description');
		$in_catpdf_shortcode=false;
        return $item;
    }
    /**
     * Return site url
	 * 
     * @param array $atts
	 * 
	 * @return string
     */
    public function site_url_func($atts) {
		global $in_catpdf_shortcode;
		$in_catpdf_shortcode=true;
		extract(shortcode_atts(array(
            'link' => false,
			'text' => ''
        ), $atts));
        $url = get_bloginfo('url');
		$text = $text=='' ? $url : $text;
		$item = !$link ? $url : "<a href='{$url}'>{$text}</a>";
		$in_catpdf_shortcode=false;
        return $item;
    }
    /**
     * Return today's date
	 * 
     * @param array $atts
	 * 
	 * @return string
     */
    public function date_today_func($atts) {
		global $in_catpdf_shortcode;
		$in_catpdf_shortcode=true;
        extract(shortcode_atts(array(
            'format' => 'F d,Y'
        ), $atts));
        $item = date($format);
		$in_catpdf_shortcode=false;
        return $item;
    }
    /**
     * Return filter from date
	 * 
     * @param array $atts
	 * 
	 * @return string
     */
    public function from_date_func($atts) {
        global $structure,$in_catpdf_shortcode;
		$in_catpdf_shortcode=true;
        extract(shortcode_atts(array(
            'format' => 'F d,Y',
            'label' => ''
        ), $atts));
        $item = '';
        if (isset($structure->post['from']) && $structure->post['from'] != '') {
            $item = $label . ' ' . date($format, strtotime($structure->post['from']));
        }
		$in_catpdf_shortcode=false;
        return $item;
    }
    /**
     * Return filter to date
	 * 
     * @param array $atts
	 * 
	 * @return string
     */
    public function to_date_func($atts) {
        global $structure,$in_catpdf_shortcode;
		$in_catpdf_shortcode=true;
        extract(shortcode_atts(array(
            'format' => 'F d,Y',
            'label' => ''
        ), $atts));
        $item = '';
        if (isset($structure->post['to']) && $structure->post['to'] != '') {
            $item = $label . ' ' . date($format, strtotime($structure->post['to']));
        }
		$in_catpdf_shortcode=false;
        return $item;
    }
}
?>