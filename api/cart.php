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

        // Quantity
        $quantity = 1;

        if ( isset($_GET['quantity']) )
            $quantity = $_GET['quantity'];

        // Variation ID
        $variation_id = 0;

        if ( isset($_GET['variation_id']) )
            $variation_id = $_GET['variation_id'];

        // Variation
        $variation = [];

        if ( isset($_GET['variation']) )
            $variation = json_decode(stripslashes($_GET['variation']), true);

        return [
            'success' => WC()->cart->add_to_cart( $data['id'], $quantity, $variation_id, $variation )
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
        $cart = WC()->cart;

        /*if ( isset($_GET['shipping_total']) )
            $cart->shipping_total = (int)$_GET['shipping_total'];*/

        if ( !defined('WOOCOMMERCE_CHECKOUT') )
            define('WOOCOMMERCE_CHECKOUT', 1);

        if ( isset($_GET['shipping']) )
            wyvern_wc_set_shipping($_GET['shipping']);

        $cart->calculate_totals();

        return $cart;
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

if ( !function_exists('wyvern_wc_set_shipping') )
{
    function wyvern_wc_set_shipping($shipping_method)
    {
        if ( empty($shipping_method) )
            return;

        // Set shipping
        $packages = WC()->cart->get_shipping_packages();

        $calculated_package = WC()->shipping->calculate_shipping_for_package($packages[0], 0);

        $shipping_methods = [ $shipping_method ];

        WC()->session->set( 'chosen_shipping_methods', $shipping_methods );
        WC()->session->set( 'shipping_method_counts', [ count($calculated_package['rates']) ] );

        WC()->shipping->calculate_shipping($packages);
    }
}