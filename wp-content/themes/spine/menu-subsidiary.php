<?php if ( has_nav_menu( 'subsidiary' ) ) {

	wp_nav_menu(
		array(
			'theme_location'  => 'subsidiary',
			'depth'           =>  1,
			'container'       => 'nav',
			'container_id'    => 'menu-subsidiary',
			'container_class' => 'menu',
			'menu_id'         => 'menu-subsidiary-items',
			'menu_class'      => 'menu-items',
			'fallback_cb'     => '',
			'items_wrap'      => '<div class="wrap"><ul id="%1$s" class="%2$s">%3$s</ul></div>'
		)
	);

} ?>