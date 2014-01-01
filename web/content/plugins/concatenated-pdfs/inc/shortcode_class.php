<?php
class shortcode {
    public $single;
    function __construct() {
        if (is_admin() || isset($_GET['catpdf_dl']) || isset($_GET['catpdf_post_dl'])) {
            $this->register_tempalte_shortcodes();
        } else {
            add_shortcode('catpdf', array(
                &$this,
                'apply_download_button'
            ));
        }
    }
    /*
    
    * Register template shortcodes
    
    */
    public function register_tempalte_shortcodes() {
        add_shortcode('site_title', array(
            &$this,
            'site_title_func'
        ));
        add_shortcode('site_tagline', array(
            &$this,
            'site_tagline_func'
        ));
        add_shortcode('site_url', array(
            &$this,
            'site_url_func'
        ));
        add_shortcode('date_today', array(
            &$this,
            'date_today_func'
        ));
        add_shortcode('from_date', array(
            &$this,
            'from_date_func'
        ));
        add_shortcode('to_date', array(
            &$this,
            'to_date_func'
        ));
        add_shortcode('categories', array(
            &$this,
            'categories_func'
        ));
        add_shortcode('post_count', array(
            &$this,
            'post_count_func'
        ));
        add_shortcode('loop', array(
            &$this,
            'loop_func'
        ));
        add_shortcode('title', array(
            &$this,
            'title_func'
        ));
        add_shortcode('excerpt', array(
            &$this,
            'excerpt_func'
        ));
        add_shortcode('content', array(
            &$this,
            'content_func'
        ));
        add_shortcode('permalink', array(
            &$this,
            'permalink_func'
        ));
        add_shortcode('date', array(
            &$this,
            'date_func'
        ));
        add_shortcode('author', array(
            &$this,
            'author_func'
        ));
        add_shortcode('author_photo', array(
            &$this,
            'author_photo_func'
        ));
        add_shortcode('author_description', array(
            &$this,
            'author_description_func'
        ));
        add_shortcode('status', array(
            &$this,
            'status_func'
        ));
        add_shortcode('featured_image', array(
            &$this,
            'featured_image_func'
        ));
        add_shortcode('category', array(
            &$this,
            'category_func'
        ));
        add_shortcode('tags', array(
            &$this,
            'tags_func'
        ));
        add_shortcode('comments_count', array(
            &$this,
            'comments_count_func'
        ));
    }
    /*
    
    * Display download button
    
    */
    public function apply_download_button($atts) {
        $link                  = '';
        $text                  = (isset($atts['text'])) ? $atts['text'] : 'Download';
        $atts['catpdf_post_dl'] = 'true';
        if (count($atts) > 0) {
            foreach ($atts as $key => $att) {
                $atts[$key] = urlencode($att);
            }
        }
        if (isset($atts['text'])) {
            unset($atts['text']);
        }
        $dllink = add_query_arg($atts);
        $link   = sprintf('<a href="%1$s">%2$s</a>', $dllink, $text);
        return $link;
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
        global $catpdf_core;
        $item = '';
        if (count($catpdf_core->posts) > 0) {
            foreach ($catpdf_core->posts as $post) {
                $this->single = $post;
                $item .= $catpdf_core->filter_shortcodes('loop');
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
    public function site_url_func() {
        $item = get_bloginfo('url');
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