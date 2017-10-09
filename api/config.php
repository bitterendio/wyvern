<?php

/*
|--------------------------------------------------------------------------
| API: Config
|--------------------------------------------------------------------------
|
| /config/ - get collection of wordpress configs
|          - /wp-json/wyvern/v1/config
|
*/

add_action( 'rest_api_init', function () {
    // {api base url}/config/
    register_rest_route( 'wyvern/v1', '/config/', [
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'wyvern_get_config',
        'args' => [],
    ] );
} );

if ( !function_exists('wyvern_get_config') )
{
    function wyvern_get_config($data)
    {
        return wyvern_theme_config();
    }
}