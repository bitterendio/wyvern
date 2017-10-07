<?php

/*
|--------------------------------------------------------------------------
| Sidebars
|--------------------------------------------------------------------------
|
| Register sidebars for widgets.
|
*/

register_sidebar([
    'name'          => __( 'Primary sidebar', 'rest' ),
    'id'            => 'primary-sidebar',
    'description'   => '',
    'class'         => '',
    'before_widget' => '<li id="%1$s" class="widget %2$s">',
    'after_widget'  => '</li>',
    'before_title'  => '<h2 class="widgettitle">',
    'after_title'   => '</h2>'
]);