<?php
/**
 * Menu endpoint
 */
add_action( 'rest_api_init', function () {
    register_rest_route( 'api', '/menu/(?P<location>\S+)', [
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'wyvern_get_menu_items',
        'args' => [
            'location'
        ],
    ] );
} );

if ( !function_exists('wyvern_get_menu_items') )
{
    function wyvern_get_menu_items($data)
    {
        if ( !isset($data['location']) )
            return ['msg' => __('Menu location was not specified')];

        $source = wp_get_nav_menu_items($data['location']);

        if ( count($source) == 0 )
            return ['msg' => __('Menu has no items')];

        return $source;
    }
}