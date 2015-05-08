<?php if ( has_nav_menu( 'secondary' ) ) {
	include_once 'inc/navbar-walker.php';
	wp_nav_menu(
		array(
			'theme_location'  => 'secondary',
			'depth'           => 2,
			'container'       => 'div',
			'container_id'    => 'menu-secondary',
			'container_class' => '',
			'menu_id'         => '',
			'menu_class'      => '',
			'fallback_cb'     => '',
			'items_wrap'      => '<div class="wrap"><div class="section-container horizontal-nav" data-section="horizontal-nav">%3$s</div></div>',
			'walker'          => new NavBarWalker()
		)
	);

} ?>