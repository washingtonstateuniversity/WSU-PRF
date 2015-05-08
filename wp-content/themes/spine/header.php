<!DOCTYPE html>
<!-- paulirish.com/2008/conditional-stylesheets-vs-css-hacks-answer-neither/ -->
<!--[if IE 8]>
<html class="no-js lt-ie9" <?php language_attributes(); ?>> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" <?php language_attributes(); ?>> <!--<![endif]-->
<head>

<meta http-equiv="Content-Type" content="<?php bloginfo( 'html_type' ); ?>; charset=<?php bloginfo( 'charset' ); ?>" />
<title><?php hybrid_document_title(); ?></title>
<meta name="viewport" content="width=device-width,initial-scale=1" />
<link rel="profile" href="http://gmpg.org/xfn/11" />
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
	<script src="<?php echo get_template_directory_uri() . '/foundation/javascripts/vendor/custom.modernizr.js'; ?>"></script>
<?php wp_head(); // wp_head ?>

</head>

<body class="<?php hybrid_body_class(); ?>" itemscope itemtype="http://schema.org/WebPage">

	<div id="container">

		<?php get_template_part( 'menu', 'primary' ); // Loads the menu-primary.php template. ?>

		<header id="header">

			<hgroup id="branding">
				<h1 id="site-title">
					<a href="<?php echo esc_url( home_url() ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
					<?php $logo_url = hybrid_get_setting( 'logo_upload' ); if( empty( $logo_url ) ) : ?>
						<?php bloginfo( 'name' ); ?>
					<?php else: ?>
						<img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" />
					<?php endif; ?>
					</a>
				</h1>
				<h2 id="site-description"><?php bloginfo( 'description' ); ?></h2>
			</hgroup><!-- #branding -->
<hr>
		</header><!-- #header -->

		<?php if ( get_header_image() ) echo '<div id="custom-header"><img class="header-image" src="' . esc_url( get_header_image() ) . '" alt="" /></div>'; ?>

		<?php get_template_part( 'menu', 'secondary' ); // Loads the menu-secondary.php template. ?>

		<div id="main">

			<?php if ( current_theme_supports( 'breadcrumb-trail' ) ) breadcrumb_trail( array( 'container' => 'nav', 'separator' => '>', 'before' => __( 'You are here:', 'spine2' ) ) ); ?>