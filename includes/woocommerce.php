<?php

function wyvern_wc_settings_footer($input = [])
{

    // Total quantity in cart
    $cart_total = 0;

    foreach( WC()->cart->cart_contents as $item )
    {
        $cart_total += $item['quantity'];
    }

    // Shipping
    // @todo: optimize
    $packages = WC()->cart->get_shipping_packages();
    $calculated_package = WC()->shipping->calculate_shipping_for_package($packages[0], 0);
    $shipping_methods = WC()->shipping->get_shipping_methods();
    $shipping = [];

    foreach( $shipping_methods as $shipping_method )
    {
        foreach( $calculated_package['rates'] as $key => $rate )
        {
            if ( strpos($rate->method_id, $shipping_method->id) !== false )
            {
                $shipping[$rate->id] = [
                    'id' => $rate->id,
                    'title' => $rate->label,
                    'description' => $shipping_method->method_description,
                    'cost' => $rate->cost
                ];
            }
        }
    }

    $user_id = get_current_user_id();

    $wc_settings = [
        // Woocommerce attributes (conditionally remove)
        'attributes'         => woocommerce_get_all_attributes_with_values(),

        // Woocommerce cart
        'cart'               => WC()->cart->get_cart(),
        'cartTotal'          => $cart_total,

        // Woocommerce settings
        'gateways'           => WC()->payment_gateways->get_available_payment_gateways(),
        'shipping'           => $shipping,
        'decimal_separator'  => wc_get_price_decimal_separator(),
        'thousand_separator' => wc_get_price_thousand_separator(),
        'decimals'           => wc_get_price_decimals(),
        'price_format'       => get_woocommerce_price_format(),
        'currency'           => get_woocommerce_currency(),
        'currency_symbol'    => get_woocommerce_currency_symbol(),

        'customerId'         => get_current_user_id(),

        'wc_selected' => [
            'shipping_methods' => WC()->session->get('wyvern_shipping_methods', []),
            'payment_method'   => WC()->session->get('wyvern_payment_method'),
        ],

        'wc_user' => [
            'billing' => [
                'first_name' => get_user_meta($user_id, 'billing_first_name', true),
                'last_name'  => get_user_meta($user_id, 'billing_last_name', true),
                'address_1'  => get_user_meta($user_id, 'billing_address_1', true),
                'address_2'  => get_user_meta($user_id, 'billing_address_2', true),
                'phone'      => get_user_meta($user_id, 'billing_phone', true),
                'email'      => get_user_meta($user_id, 'billing_email', true),
            ],
        ]
    ];

    foreach ( $wc_settings as $key => $value )
    {
        $input[$key] = $value;
    }

    return $input;

}

add_filter( 'wyvern_wp_settings', 'wyvern_wc_settings_footer' );
