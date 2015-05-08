<?php
include_once 'inc/topbar-walker.php';
if ( has_nav_menu( 'primary' ) ) :


 $title = '<ul class="title-area"><li class="name"><h1><a href="' . home_url('/') . '">' . get_bloginfo( 'name' ) . '</a></h1></li><li class="toggle-topbar menu-icon"><a href="#"><span>' . __('Menu','spine2') . '</a></a></li></ul>';
 wp_nav_menu( array(
	'container' => 'nav',
	'theme_location' => 'primary',
	'container_class' => 'top-bar',
	'menu_class' => '',
	'menu_id' => 'menu-primary-items',
	'items_wrap' => $title . '<section class="top-bar-section"><ul class="right"><li class="divider"></li>%3$s</ul></section>',
	'walker' => new Foundation_Walker(),
	'fallback_cb' => '' ) );



 endif;