<?php

/*
|--------------------------------------------------------------------------
| Custom
|--------------------------------------------------------------------------
|
| Great place to filter theme's behavior and place custom functions
|
*/

if (!function_exists('wyvern_custom_routes'))
{
    function wyvern_custom_routes($routes)
    {
        /*
        $routes[] = [
            'path'      => '/myslug/:param',
            'meta'      => [
                'slug'      => 'myslug',
                'template'  => 'mytemplate',
                'type'      => 'page',
                'wp_title'  => 'My custom title' . ' · ' . get_bloginfo('name')
            ],
            'props'     => true,
        ];
        */

        return $routes;
    }
}

add_filter( 'rest_theme_routes', 'wyvern_custom_routes' );
