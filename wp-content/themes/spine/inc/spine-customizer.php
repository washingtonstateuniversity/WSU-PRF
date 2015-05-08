<?php
/**
 * Functions for registering and setting theme settings that tie into the WordPress theme customizer.
 * This file loads additional classes and adds settings to the customizer for the built-in Hybrid Core
 * settings.
 *
 * @package    HybridCore
 * @subpackage Functions
 * @author     Justin Tadlock <justin@justintadlock.com>
 * @copyright  Copyright (c) 2008 - 2012, Justin Tadlock
 * @link       http://themehybrid.com/hybrid-core
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

/* Load custom control classes. */
add_action( 'customize_register', 'spine2_load_customize_controls', 1 );

/* Register custom sections, settings, and controls. */
add_action( 'customize_register', 'spine2_customize_register' );

/* Add the footer content Ajax to the correct hooks. */
add_action( 'wp_ajax_spine2_customize_footer_content', 'spine2_customize_colors_ajax' );
add_action( 'wp_ajax_nopriv_spine2_customize_footer_content', 'spine2_customize_colors_ajax' );

/**
 * Loads framework-specific customize control classes.  Customize control classes extend the WordPress
 * WP_Customize_Control class to create unique classes that can be used within the framework.
 *
 * @since 1.4.0
 * @access private
 */
function spine2_load_customize_controls() {

	/* Loads the logo customize control class. */
	require_once( 'customize-control-image-upload-reloaded.php' );
}

/**
 * Registers custom sections, settings, and controls for the $wp_customize instance.
 *
 * @since 1.4.0
 * @access private
 * @param object $wp_customize
 */
function spine2_customize_register( $wp_customize ) {

	/* Get the theme prefix. */
	$prefix = hybrid_get_prefix();


		/* Add the color scheme section. */
/*		$wp_customize->add_section(
			'spine-scheme',
			array(
				'title'      => esc_html__( 'Color Scheme', 'spine' ),
				'priority'   => 200,
				'capability' => 'edit_theme_options'
			)
		);*/

		/*Add the ' color scheme ' setting.
		$wp_customize->add_setting(
			"{$prefix}_theme_settings[color_scheme_select]",
			array(
				'default'              => 'default',
				'type'                 => 'option',
				'capability'           => 'edit_theme_options',
				'sanitize_callback'    => 'spine2_customize_sanitize',
				'sanitize_js_callback' => 'spine2_customize_sanitize',
				'transport'            => 'postMessage',
			)
		);

	$schemes = array(
		'default' => __('Default', 'spine'),
		'blue' => __('Blue', 'spine'),
		'red' => __('Red', 'spine'),
		'green' => __('Green', 'spine'),
	);



$wp_customize->add_control( 'spine_color_scheme', array(
'label' => __( 'Color Scheme', 'spine' ),
'section'=> 'spine-scheme',
'settings'=> "{$prefix}_theme_settings[color_scheme_select]",
'type'=> 'radio',
'choices'=> $schemes
) );
*/
	$wp_customize->add_setting(
		"{$prefix}_theme_settings[headline_color]",
		array(
			'default'              => '#2795b6',
			'type'                 => 'option',
			'capability'           => 'edit_theme_options',
			'sanitize_callback'    => 'spine2_customize_sanitize',
			'sanitize_js_callback' => 'spine2_customize_sanitize',
			'transport'            => 'postMessage',
		)
	);

$wp_customize->add_control(
	new WP_Customize_Color_Control($wp_customize,'headline_color',
	array('label' => __('Headline color','spine'),
	'section' => 'colors',
	'settings'=> "{$prefix}_theme_settings[headline_color]",
	)
	)
);

	$wp_customize->add_setting(
		"{$prefix}_theme_settings[body_color]",
		array(
			'default'              => '#222',
			'type'                 => 'option',
			'capability'           => 'edit_theme_options',
			'sanitize_callback'    => 'spine2_customize_sanitize',
			'sanitize_js_callback' => 'spine2_customize_sanitize',
			'transport'            => 'postMessage',
		)
	);

	$wp_customize->add_control(
		new WP_Customize_Color_Control($wp_customize,'body_color',
			array('label' => __('Body Text color','spine'),
						'section' => 'colors',
						'settings'=> "{$prefix}_theme_settings[body_color]",
			)
		)
	);

	$wp_customize->add_setting(
		"{$prefix}_theme_settings[link_color]",
		array(
			'default'              => '#2ba6cb',
			'type'                 => 'option',
			'capability'           => 'edit_theme_options',
			'sanitize_callback'    => 'spine2_customize_sanitize',
			'sanitize_js_callback' => 'spine2_customize_sanitize',
			'transport'            => 'postMessage',
		)
	);

	$wp_customize->add_control(
		new WP_Customize_Color_Control($wp_customize,'link_color',
			array('label' => __('Link Text color','spine'),
						'section' => 'colors',
						'settings'=> "{$prefix}_theme_settings[link_color]",
			)
		)
	);

	$wp_customize->add_setting(
		"{$prefix}_theme_settings[link_hover_color]",
		array(
			'default'              => '#2795b6',
			'type'                 => 'option',
			'capability'           => 'edit_theme_options',
			'sanitize_callback'    => 'spine2_customize_sanitize',
			'sanitize_js_callback' => 'spine2_customize_sanitize',
			'transport'            => 'postMessage',
		)
	);

	$wp_customize->add_control(
		new WP_Customize_Color_Control($wp_customize,'link_hover_color',
			array('label' => __('Link Hover Text color','spine'),
						'section' => 'colors',
						'settings'=> "{$prefix}_theme_settings[link_hover_color]",
			)
		)
	);

	$wp_customize->add_setting(
		"{$prefix}_theme_settings[logo_upload]",
		array(
			'default'              => '',
			'type'                 => 'option',
			'capability'           => 'edit_theme_options',
			'transport'            => 'postMessage',
		)
	);

	$wp_customize->add_control(
		new My_Customize_Image_Reloaded_Control(
			$wp_customize,
			'logo_upload',
			array(
				'label' => 'Logo Upload',
				'section' => 'title_tagline',
				'settings' => "{$prefix}_theme_settings[logo_upload]"
			)
		)
	);

	/* If viewing the customize preview screen, add a script to show a live preview. */
		if ( $wp_customize->is_preview() && !is_admin() )
			add_action( 'wp_footer', 'spine2_customize_preview_script', 21 );

}

/**
 * Sanitizes the footer content on the customize screen.  Users with the 'unfiltered_html' cap can post
 * anything.  For other users, wp_filter_post_kses() is ran over the setting.
 *
 * @since 1.4.0
 * @access public
 * @param mixed $setting The current setting passed to sanitize.
 * @param object $object The setting object passed via WP_Customize_Setting.
 * @return mixed $setting
 */
function spine2_customize_sanitize( $setting, $object ) {

	/* Get the theme prefix. */
	$prefix = hybrid_get_prefix();

	/* Make sure we kill evil scripts from users without the 'unfiltered_html' cap. */
	if ( "{$prefix}_theme_settings[footer_insert]" == $object->id && !current_user_can( 'unfiltered_html' )  )
		$setting = stripslashes( wp_filter_post_kses( addslashes( $setting ) ) );

	/* Return the sanitized setting and apply filters. */
	return apply_filters( "{$prefix}_customize_sanitize", $setting, $object );
}

/**
 * Handles changing settings for the live preview of the theme.
 *
 * @since 1.4.0
 * @access private
 */
function spine2_customize_preview_script() {
	?>
<script type="text/javascript">
	( function( $ ) {
		wp.customize('<?php echo hybrid_get_prefix(); ?>_theme_settings[body_color]',function( value ) {
			value.bind(function(to) {
				$('body').css('color', to );
			});
		});
		wp.customize('<?php echo hybrid_get_prefix(); ?>_theme_settings[headline_color]',function( value ) {
			value.bind(function(to) {
				$('h1, h2, h2 a, h3, h4, h5, h6').css('color', to );
			});
		});
		wp.customize('<?php echo hybrid_get_prefix(); ?>_theme_settings[link_color]',function( value ) {
			value.bind(function(to) {
				$('a:link,a:visited').css('color', to );
			});
		});

		wp.customize('<?php echo hybrid_get_prefix(); ?>_theme_settings[logo_upload]',function( value ) {
			value.bind(function(to) {
				$('#site-title img').attr('src', to);
			});
		});
	} )( jQuery )
</script>
<?php
}