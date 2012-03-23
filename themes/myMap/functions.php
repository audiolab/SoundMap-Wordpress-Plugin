<?php
// This theme uses wp_nav_menu() in one location.
register_nav_menu( 'primary', __( 'Primary Menu', 'mymap' ) );


register_sidebar( array(
	'name' => __( 'Main Sidebar', 'mymap' ),
	'id' => 'sidebar-1',
	'before_widget' => '<aside id="%1$s" class="widget %2$s">',
	'after_widget' => "</aside>",
	'before_title' => '<h3 class="widget-title">',
	'after_title' => '</h3>',
) );


?>