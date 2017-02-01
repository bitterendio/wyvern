<?php
/**
 * Cart endpoint
 */
// Add product to cart
add_action( 'rest_api_init', function () {
    register_rest_route( 'api', '/cart/(?P<id>\d+)', [
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => 'wyvern_wc_cart_add',
        'args' => [
            'id'
        ],
    ] );
} );

if ( !function_exists('wyvern_wc_cart_add') )
{
    function wyvern_wc_cart_add($data)
    {
        if ( !isset($data['id']) )
            return ['msg' => __('Item was not specified')];

        return [
            'success' => WC()->cart->add_to_cart( $data['id'] )
        ];
    }
}

// Get cart
add_action( 'rest_api_init', function () {
    register_rest_route( 'api', '/cart/', [
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'wyvern_wc_cart',
        'args' => [],
    ] );
} );

if ( !function_exists('wyvern_wc_cart') )
{
    function wyvern_wc_cart()
    {
        return WC()->cart;
    }
}

// Empty cart
add_action( 'rest_api_init', function () {
    register_rest_route( 'api', '/empty-cart/', [
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'wyvern_wc_empty_cart',
        'args' => [],
    ] );
} );

if ( !function_exists('wyvern_wc_empty_cart') )
{
    function wyvern_wc_empty_cart()
    {
        return [
            'success' => WC()->cart->empty_cart()
        ];
    }
}

// Update cart item quantity
add_action( 'rest_api_init', function () {
    register_rest_route( 'api', '/quantity/', [
        'methods' => WP_REST_Server::EDITABLE,
        'callback' => 'wyvern_wc_update_cart_item',
        'args' => [],
    ] );
} );

if ( !function_exists('wyvern_wc_update_cart_item') )
{
    function wyvern_wc_update_cart_item()
    {
        $data = $_POST;

        if ( !isset($data['id']) )
            return ['msg' => __('Item was not specified')];

        if ( !isset($data['quantity']) )
            return ['msg' => __('Quantity was not specified')];

        $msg = WC()->cart->set_quantity($data['id'], $data['quantity']);

        // Total
        $cart_total = 0;

        foreach( WC()->cart->cart_contents as $item )
        {
            $cart_total += $item['quantity'];
        }

        return [
            'success' => $msg,
            'cart' => wyvern_wc_cart(),
            'cart_total' => $cart_total,
        ];
    }
}