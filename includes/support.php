<?php

/*
|--------------------------------------------------------------------------
| Theme support
|--------------------------------------------------------------------------
|
| title-tag: allows theme to put content in <title> tag
| menus: allows theme to have menus
|
*/

add_theme_support( 'title-tag' );
add_theme_support( 'menus' );
add_theme_support( 'post-thumbnails' );

add_action( 'send_headers', function() {
    if ( ! did_action('rest_api_init') ) {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Expose-Headers: Link");
        header("Access-Control-Allow-Methods: HEAD");
    }
} );