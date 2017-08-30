<?php

/*
|--------------------------------------------------------------------------
| API: Menus
|--------------------------------------------------------------------------
|
| /menu/<id|name|slug>       - get menu by id, name or slug
|       - /wp-json/wyvern/v1/menu/25
|       - /wp-json/wyvern/v1/menu/primary-menu
| /menu/location/<location>  - get menu by location
|       - /wp-json/wyvern/v1/menu/location/primary/
|
*/

add_action( 'rest_api_init', function () {
    // {api base url}/menu/location/<location>
    register_rest_route( 'wyvern/v1', '/menu/location/(?P<location>\S+)', [
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'wyvern_get_menu_items_by_location',
        'args' => [
            'location'
        ],
    ] );

    // {api base url}/menu/<id|name|slug>
    register_rest_route( 'wyvern/v1', '/menu/(?P<id>\S+)', [
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'wyvern_get_menu_items_by_id',
        'args' => [
            'id'
        ],
    ] );
} );

if ( !function_exists('wyvern_get_menu_items_by_id') )
{
    function wyvern_get_menu_items_by_id($data)
    {
        // Check if location was specified
        if ( !isset($data['id']) )
            return ['msg' => __('Menu was not specified')];

        // Array of menu items, otherwise false
        $source = wp_get_nav_menu_items($data['id']);

        if ( $source === false )
            return ['msg' => __('Menu has no items')];

        return apply_filters( 'wyvern_get_menu', $source );
    }
}


if ( !function_exists('wyvern_get_menu_items_by_location') )
{
    function wyvern_get_menu_items_by_location($data)
    {
        // Check if location was specified
        if ( !isset($data['location']) )
            return ['msg' => __('Menu location was not specified')];

        // Get available locations
        $available_locations = get_nav_menu_locations();

        if ( !isset($available_locations[$data['location']]) )
            return ['msg' => __('Specified location has no menu attached to it')];

        // Array of menu items, otherwise false
        $source = wp_get_nav_menu_items($available_locations[$data['location']]);

        if ( $source === false )
            return ['msg' => __('Menu has no items')];

        return apply_filters( 'wyvern_get_menu', $source );
    }
}
