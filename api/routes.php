<?php

/*
|--------------------------------------------------------------------------
| API: Routes
|--------------------------------------------------------------------------
|
| /routes/                  - get collection of routes
|       - /wp-json/wyvern/v1/routes
|
*/

add_action( 'rest_api_init', function () {
    // {api base url}/routes/
    register_rest_route( 'wyvern/v1', '/routes/', [
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'wyvern_get_routes',
        'args' => [],
    ] );
} );

if ( !function_exists('wyvern_get_routes') )
{
    function wyvern_get_routes($data)
    {
        return rest_theme_routes();
    }
}
