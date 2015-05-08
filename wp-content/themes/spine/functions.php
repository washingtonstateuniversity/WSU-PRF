<?php
/**
 * The functions file is used to initialize everything in the theme.  It controls how the theme is loaded and
 * sets up the supported features, default actions, and default filters.  If making customizations, users
 * should create a child theme and make changes to its functions.php file (not this one).  Friends don't let
 * friends modify parent theme files. ;)
 *
 * Child themes should do their setup on the 'after_setup_theme' hook with a priority of 11 if they want to
 * override parent theme features.  Use a priority of 9 if wanting to run before the parent theme.
 *
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU
 * General Public License as published by the Free Software Foundation; either version 2 of the License,
 * or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without
 * even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * You should have received a copy of the GNU General Public License along with this program; if not, write
 * to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 *
 * @package    HybridBase
 * @subpackage Functions
 * @version    0.1.0
 * @since      0.1.0
 * @author     Justin Tadlock <justin@justintadlock.com>
 * @copyright  Copyright (c) 2013, Justin Tadlock
 * @link       http://themehybrid.com/themes/hybrid-base
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

/* Load the core theme framework. */
require_once( trailingslashit( get_template_directory() ) . 'hybrid-core/hybrid.php' );
new Hybrid();

/* Do theme setup on the 'after_setup_theme' hook. */
add_action( 'after_setup_theme', 'spine2_theme_setup' );

/**
 * Theme setup function.  This function adds support for theme features and defines the default theme
 * actions and filters.
 *
 * @since  0.1.0
 * @access public
 * @return void
 */
function spine2_theme_setup() {

	/** Theme constants */
	define ( 'SPINE2_JS_URL', trailingslashit( get_template_directory_uri() . '/js' ) );

	define ( 'SPINE2_INC_DIR', trailingslashit( get_template_directory() . '/inc' ) );

	define ( 'SPINE2_DIR', dirname( __FILE__ ) );

	define( 'SPINE2_VERSION', '0.1' );

	define( 'SPINE2_FOUNDATION_JS_URL', trailingslashit( get_template_directory_uri() . '/foundation/javascripts/foundation') );
	define( 'SPINE2_VENDOR_JS_URL', trailingslashit( get_template_directory_uri() . '/foundation/javascripts/vendor') );

	// Include Spine Pagination
	include_once SPINE2_INC_DIR . 'pagination.php';

	// Load Customizer settings
	include_once SPINE2_INC_DIR . 'spine-customizer.php';

	include_once SPINE2_INC_DIR . 'gallery-shortcode.php';

	include_once SPINE2_INC_DIR . 'meta.php';

	add_action( 'customize_register', 'spine2_customize_register' );

	/* Get action/filter hook prefix. */
	$prefix = hybrid_get_prefix();

	/* Register menus. */
	add_theme_support(
		'hybrid-core-menus',
		array( 'primary', 'secondary', 'subsidiary' )
	);

	/* Register sidebars. */
	add_theme_support(
		'hybrid-core-sidebars',
		array( 'primary', 'secondary', 'subsidiary' )
	);

	/* Load scripts. */
	add_theme_support(
		'hybrid-core-scripts',
		array( 'comment-reply' )
	);

	/* Load styles.
	add_theme_support(
		'hybrid-core-styles', 
		array( '25px', 'gallery', 'parent', 'style' ) 
	);
	*/

	/* Load widgets. */
	add_theme_support( 'hybrid-core-widgets' );

	/* Load shortcodes. */
	add_theme_support( 'hybrid-core-shortcodes' );

	/* Enable custom template hierarchy. */
	add_theme_support( 'hybrid-core-template-hierarchy' );

	/* Enable theme layouts (need to add stylesheet support). */
	add_theme_support(
		'theme-layouts',
		array( '1c', '2c-l', '2c-r', '3c-l', '3c-r','3c-c' ),
		array( 'default' => '1c', 'customizer' => true )
	);

	/* Allow per-post stylesheets. */
	add_theme_support( 'post-stylesheets' );

	/* Support pagination instead of prev/next links. */
	//add_theme_support( 'loop-pagination' );

	/* The best thumbnail/image script ever. */
	add_theme_support( 'get-the-image' );

	/** Custom thumbnail size */
	add_image_size('featured', 640, 132, true);

	/* Use breadcrumbs. */
	//add_theme_support( 'breadcrumb-trail' );

	/* Nicer [gallery] shortcode implementation. */
	//add_theme_support( 'cleaner-gallery' );

	/* Better captions for themes to style. */
	//add_theme_support( 'cleaner-caption' );

	/* Automatically add feed links to <head>. */
	add_theme_support( 'automatic-feed-links' );

	/* Add support for a custom header image. */
	$args = array(
		'header-text' => false,
		'flex-width'    => true,
		'width'         => 1000,
		'flex-height'    => true,
		'height'        => 300,
		//'default-image' => get_template_directory_uri() . '/images/header.jpg',
	);
	add_theme_support( 'custom-header', $args );

	add_theme_support('featured-header');

	/* Custom background. */
	add_theme_support(
		'custom-background',
		array( 'default-color' => 'ffffff' )
	);

	/* Post formats. */
	add_theme_support(
		'post-formats',
		array( 'aside', 'audio', 'chat', 'image', 'gallery', 'link', 'quote', 'status', 'video' )
	);

	/** Add theme settings */
	add_theme_support( 'hybrid-core-theme-settings', array( 'about', 'footer' ) );

	/* Handle content width for embeds and images. */
	hybrid_set_content_width( 1280 );

	/** Hybrid Core 1.6 changes **/
	add_filter( "{$prefix}_sidebar_defaults", 'spine2_sidebar_defaults', 15, 2 );
	//add_filter( 'cleaner_gallery_defaults', 'spine2_gallery_defaults' );
	add_filter( 'the_content', 'spine2_aside_infinity', 9 );
	/****************************/

	// load the stylesheet
	add_action( 'wp_enqueue_scripts', 'spine2_load_styles' );

	/** Load the javascripts */
	add_action( 'wp_enqueue_scripts', 'spine2_load_scripts' );

	// customize the pagination markup
	add_filter( 'loop_pagination_args', 'spine2_foundation_pagination' );

	add_filter('embed_oembed_html', 'spine2_oembed_html', 99, 4);

	// Register widget areas
	add_action('widgets_init', 'spine2_register_sidebars', 11);

	// Add customizer styles to frontend
	add_action( 'wp_head', 'spine2_wp_head' );

	add_editor_style();

	add_filter( 'sidebars_widgets', 'spine2_disable_sidebars' );

	//add_filter('post_thumbnail_html', 'pdw_spine_add_thumbnail_class',10, 3 );
	add_filter( 'get_the_image', 'spine2_add_featured_img_class', 10, 1 );
}

/* === HYBRID CORE 1.6 CHANGES. === 
 *
 * The following changes are slated for Hybrid Core version 1.6 to make it easier for 
 * theme developers to build awesome HTML5 themes. The code will be removed once 1.6 
 * is released.
 */

/**
 * Content template.  This is an early version of what a content template function will look like
 * in future versions of Hybrid Core.
 *
 * @since  0.1.0
 * @access public
 * @return void
 */
function spine2_get_content_template() {

	$templates = array();
	$post_type = get_post_type();

	if ( post_type_supports( $post_type, 'post-formats' ) ) {

		$post_format = get_post_format() ? get_post_format() : 'standard';

		$templates[] = "content-{$post_type}-{$post_format}.php";
		$templates[] = "content-{$post_format}.php";
	}

	$templates[] = "content-{$post_type}.php";
	$templates[] = 'content.php';

	return locate_template( $templates, true, false );
}

/**
 * Sidebar parameter defaults.
 *
 * @since  0.1.0
 * @access public
 *
 * @param  array  $defaults
 *
 * @return array
 */
function spine2_sidebar_defaults( $defaults, $sidebar ) {

	$defaults = array(
		'before_widget' => '<section id="%1$s" class=" panel widget %2$s widget-%2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>'
	);

	if('subsidiary' === $sidebar){
		$spine = array(
			'before_widget' => '<section id="%1$s" class="widget %2$s widget-%2$s"><div class="panel"',
			'after_widget'  => '</div></section>',
		);
		array_merge($defaults,$spine);
		return $spine;
	}

	return $defaults;

}

/**
 * Gallery defaults for the Cleaner Gallery extension.
 *
 * @since  0.1.0
 * @access public
 *
 * @param  array  $defaults
 *
 * @return array
 */
function spine2_gallery_defaults( $defaults ) {

	$defaults['itemtag']    = 'figure';
	$defaults['icontag']    = 'div';
	$defaults['captiontag'] = 'figcaption';

	return $defaults;
}

/**
 * Adds an infinity character "&#8734;" to the end of the post content on 'aside' posts.  This
 * is from version 0.1.1 of the Post Format Tools extension.
 *
 * @since  0.1.0
 * @access public
 *
 * @param  string $content The post content.
 *
 * @return string $content
 */
function spine2_aside_infinity( $content ) {

	if ( has_post_format( 'aside' ) && ! is_singular() )
		$content .= ' <a class="permalink" href="' . get_permalink() . '" title="' . the_title_attribute( array( 'echo' => false ) ) . '">&#8734;</a>';

	return $content;
}

/* End Hybrid Core 1.6 section. */

function spine2_load_styles() {
	/** This loads the main theme style.css */
	wp_enqueue_style( 'main', get_stylesheet_uri() );
}

/** Customize loop pagination extension */
function spine2_foundation_pagination( $args ) {

	$args['type'] = 'list';

	return $args;
}

/**
 * Load the necessary javascript files
 */
function spine2_load_scripts() {
	/** This is the main javascript file */
	//wp_deregister_script('jquery');
	//wp_enqueue_script( 'jquery', SPINE2_VENDOR_JS_URL . 'jquery.js', array(), SPINE2_VERSION, true );
	wp_enqueue_script( 'foundation-app', SPINE2_FOUNDATION_JS_URL . 'foundation.js', array( 'jquery' ), SPINE2_VERSION, true );
	wp_enqueue_script( 'foundation-topbar', SPINE2_FOUNDATION_JS_URL . 'foundation.topbar.js', array( 'jquery', 'foundation-app' ), SPINE2_VERSION, true );
	wp_enqueue_script( 'foundation-section', SPINE2_FOUNDATION_JS_URL . 'foundation.section.js', array( 'jquery', 'foundation-app' ), SPINE2_VERSION, true );
	wp_enqueue_script( 'global', SPINE2_JS_URL . 'global.js', array( 'jquery', 'foundation-app', 'foundation-topbar' ), SPINE2_VERSION, true );

}

/**
 * Wrap videos in a div
 * @param $html
 * @param $url
 * @param $attr
 * @param $post_id
 *
 * @return string
 */
function spine2_oembed_html($html, $url, $attr, $post_id) {
	if(strstr($url,'twitter.com'))
		return $html;
	return '<div class="spine-video">' . $html . '</div>';
}


/**
 * Registers Spine extra widget areas
 */
function spine2_register_sidebars(){
	/** Register front-page widget areas */
	register_sidebar(
		array(
			'id' => 'banded-first-band',
			'name' => __( 'Front Page First Band','spine' ),
			'description' => __( 'This is the full width area at the top of the Front Page template.','spine'  ),
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget' => '</div>',
			'before_title' => '<h3 class="widget-title">',
			'after_title' => '</h3>'
		)
	);
	register_sidebar(
		array(
			'id' => 'banded-second-band-1',
			'name' => __( 'Front Page Second Band 1','spine' ),
			'description' => __( 'This is the narrow area in the middle of the Front Page template.','spine'  ),
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget' => '</div>',
			'before_title' => '<h3 class="widget-title">',
			'after_title' => '</h3>'
		)
	);
	register_sidebar(
		array(
			'id' => 'banded-second-band-2',
			'name' => __( 'Front Page Second Band 2','spine' ),
			'description' => __( 'This is the wider area in the middle of the Front Page template.','spine'  ),
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget' => '</div>',
			'before_title' => '<h3 class="widget-title">',
			'after_title' => '</h3>'
		)
	);
	register_sidebar(
		array(
			'id' => 'banded-third-band-1',
			'name' => __( 'Front Page Third Band 1','spine' ),
			'description' => __( 'This is the wider area at the bottom of the Front Page template.','spine'  ),
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget' => '</div>',
			'before_title' => '<h3 class="widget-title">',
			'after_title' => '</h3>'
		)
	);
	register_sidebar(
		array(
			'id' => 'banded-third-band-2',
			'name' => __( 'Front Page Third Band 2','spine' ),
			'description' => __( 'This is the narrow area at the bottom of the Front Page template.','spine'  ),
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget' => '</div>',
			'before_title' => '<h3 class="widget-title">',
			'after_title' => '</h3>'
		)
	);
}

/**
 * Insert customizer styles to document head
 */
function spine2_wp_head() {
	$body_color = hybrid_get_setting( 'body_color' );
	$headline_color = hybrid_get_setting( 'headline_color' );
	$link_color = hybrid_get_setting( 'link_color' );
	$link_hover_color = hybrid_get_setting( 'link_hover_color' );

	$output = "<style>" . PHP_EOL . " /* Customizer styles*/" . PHP_EOL;
	if(!empty($body_color)){
		$output .= "body { color: $body_color; } ";
	}
	if(!empty($headline_color)){
		$output .= "h1, h2, h3, h4, h5, h6 { color: $headline_color } ";
	}
	if(!empty($link_color)){
		$output .= "a { color: $link_color; } ";
	}
	if(!empty($link_hover_color)){
		$output .= "a:hover { color: $link_hover_color; } ";
	}

	echo $output .= " </style>" . PHP_EOL;
}

add_action( 'contextual_help', 'wptuts_screen_help', 10, 3 );
function wptuts_screen_help( $contextual_help, $screen_id, $screen ) {
	// The add_help_tab function for screen was introduced in WordPress 3.3.
	if ( ! method_exists( $screen, 'add_help_tab' ) )
		return $contextual_help;
	global $hook_suffix;
	// List screen properties
	$variables = '<ul style="width:50%;float:left;"> <strong>Screen variables </strong>'
			. sprintf( '<li> Screen id : %s</li>', $screen_id )
			. sprintf( '<li> Screen base : %s</li>', $screen->base )
			. sprintf( '<li>Parent base : %s</li>', $screen->parent_base )
			. sprintf( '<li> Parent file : %s</li>', $screen->parent_file )
			. sprintf( '<li> Hook suffix : %s</li>', $hook_suffix )
			. '</ul>';
	// Append global $hook_suffix to the hook stems
	$hooks = array(
		"load-$hook_suffix",
		"admin_print_styles-$hook_suffix",
		"admin_print_scripts-$hook_suffix",
		"admin_head-$hook_suffix",
		"admin_footer-$hook_suffix"
	);
	// If add_meta_boxes or add_meta_boxes_{screen_id} is used, list these too
	if ( did_action( 'add_meta_boxes_' . $screen_id ) )
		$hooks[] = 'add_meta_boxes_' . $screen_id;
	if ( did_action( 'add_meta_boxes' ) )
		$hooks[] = 'add_meta_boxes';
	// Get List HTML for the hooks
	$hooks = '<ul style="width:50%;float:left;"> <strong>Hooks </strong> <li>' . implode( '</li><li>', $hooks ) . '</li></ul>';
	// Combine $variables list with $hooks list.
	$help_content = $variables . $hooks;
	// Add help panel
	$screen->add_help_tab( array(
		'id'      => 'wptuts-screen-help',
		'title'   => 'Screen Information',
		'content' => $help_content,
	));
	return $contextual_help;
}

add_filter( 'theme_mod_header_image', 'spine2_theme_mod_header_image', 11 );

function spine2_theme_mod_header_image( $url ) {

	if(is_admin())
		return;
	if(!is_singular())
		return;
	if ( get_post_meta(get_the_id(),'spine2_hide_header_img', true) )
		$url = '';

	return $url;
}

// Replaces the excerpt "more" text by a link
function spine2_new_excerpt_more($more) {
	global $post;
	return '<a class="moretag" href="'. get_permalink($post->ID) . '"> Read the full article...</a>';
}
add_filter('excerpt_more', 'spine2_new_excerpt_more');

/**
 * Disables sidebars if viewing a one-column page.
 *
 * @since 0.1.0
 * @param array $sidebars_widgets A multidimensional array of sidebars and widgets.
 * @return array $sidebars_widgets
 */
function spine2_disable_sidebars( $sidebars_widgets ) {
	global $wp_query, $wp_customize;

	if ( current_theme_supports( 'theme-layouts' ) && !is_admin() ) {
		if ( ! isset( $wp_customize ) ) {
			if ( 'layout-2c-r' == theme_layouts_get_layout() || 'layout-2c-l' == theme_layouts_get_layout()) {
				$sidebars_widgets['secondary'] = false;
			}
		}
	}

	return $sidebars_widgets;
}

/**
 * @param $img_html
 *
 * @return string
 */
function spine2_add_featured_img_class( $img_html ) {
	/** Only do this is there's an image */
	if ( ! empty( $img_html ) )
		$img_html = '<a class="th" href="' . get_permalink( get_the_ID() ) . '" title="' . esc_attr( get_post_field( 'post_title', get_the_ID() ) ) . '">' . $img_html . '</a>';

	return $img_html;
}

	add_filter('getarchives_where', 'wse95776_archives_by_cat', 10, 2 );
	/**
	 * Filter the posts by category slug
	 * @param $where
	 * @param $r
	 *
	 * @return string
	 */
	function wse95776_archives_by_cat($where, $r){
		return "WHERE wp_posts.post_type = 'post' AND wp_posts.post_status = 'publish' AND wp_terms.slug = 'Uncategorized' AND wp_term_taxonomy.taxonomy = 'category'";
	}

	add_filter('getarchives_join', 'wse95776_archives_join',10,2);

	/**
	 * Defines the necessary joins to query the terms
	 * @param $join
	 * @param $r
	 *
	 * @return string
	 */
	function wse95776_archives_join($join, $r){
		return 'inner join wp_term_relationships on wp_posts.ID = wp_term_relationships.object_id inner join wp_term_taxonomy on wp_term_relationships.term_taxonomy_id = wp_term_taxonomy.term_taxonomy_id inner join wp_terms on wp_term_taxonomy.term_id = wp_terms.term_id';
	}