<?php

/*
|--------------------------------------------------------------------------
| Custom
|--------------------------------------------------------------------------
|
| Great place to filter theme's behavior and place custom functions
|
*/

/**
 * Add custom route
 */
if (!function_exists('wyvern_custom_routes'))
{
    function wyvern_custom_routes($routes)
    {
        $routes[] = [
            'path'      => '/wyvern/',
            'meta'      => [
                'slug'      => 'wyvern',
                'template'  => 'wyvern',
                'type'      => 'page',
                'wp_title'  => 'Wyvern' . ' · ' . get_bloginfo('name'),
                'content'   => nl2br(file_get_contents(__DIR__ . '/../README.md')),
            ],
        ];

        return $routes;
    }
}

add_filter( 'wyvern_theme_routes', 'wyvern_custom_routes' );

if (!function_exists('wyvern_custom_menu'))
{
    function wyvern_custom_menu($menu)
    {
        // If the API response is only message, replace menu with custom
        if (isset($menu['msg']) && count($menu) === 1) {
            return [
                [
                    'title' => 'Wyvern',
                    'url' => '/wyvern/',
                ]
            ];
        }

        return $menu;
    }
}

add_filter( 'wyvern_get_menu', 'wyvern_custom_menu' );
