<?php

add_action( 'rest_api_init', function () {
    register_rest_route( 'api', '/customer/(?P<id>\d+)', [
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'wyvern_wc_get_customer',
        'args' => [
            'id'
        ],
    ] );
} );

add_action( 'rest_api_init', function () {
    register_rest_route( 'api', '/customer/(?P<id>\d+)', [
        'methods' => WP_REST_Server::EDITABLE,
        'callback' => 'wyvern_wc_update_customer',
        'args' => [
            'id'
        ],
    ] );
} );

if ( !function_exists('wyvern_wc_get_customer') )
{
    function wyvern_wc_get_customer($data)
    {
        if ( !isset($data['id']) )
            return [];

        $customer_id = (int)$data['id'];

        $orders = get_posts( array(
            'numberposts' => -1,
            'meta_key'    => '_customer_user',
            'meta_value'  => $customer_id,
            'post_type'   => wc_get_order_types(),
            'post_status' => array_keys( wc_get_order_statuses() ),
        ) );

        foreach( $orders as &$order )
        {
            $order = new WC_Order($order->ID);
        }

        $userdata = [];

        if ( $customer_id )
            $userdata = get_userdata( $customer_id );

        return [
            'customerId'  => $customer_id,
            'userdata'    => $userdata,
            'orders'      => $orders,
            'billing_address' => [
                'first_name'    => get_user_meta($customer_id, 'billing_first_name', true),
                'last_name'     => get_user_meta($customer_id, 'billing_last_name', true),
                'phone'         => get_user_meta($customer_id, 'billing_phone', true),
                'email'         => get_user_meta($customer_id, 'billing_email', true),
                'address_1'     => get_user_meta($customer_id, 'billing_address_1', true),
                'address_2'     => get_user_meta($customer_id, 'billing_address_2', true),
                'address'       => get_user_meta($customer_id, 'billing_address_1', true) . "\n" . get_user_meta($customer_id, 'billing_address_2', true),
            ]
        ];
    }
}

if ( !function_exists('wyvern_wc_update_customer') )
{
    function wyvern_wc_update_customer($data)
    {
        if ( !isset($data['id']) )
            return [];

        $customer_id = (int)$data['id'];

        $updated = [];

        if ( isset($_POST['billing_address']) && !empty($_POST['billing_address']) )
            $updated['billing_address'] = wyvern_wc_update_customer_billing_address(json_decode(stripslashes($_POST['billing_address']), true), $customer_id);

        return $updated;
    }
}

if ( !function_exists('wyvern_wc_update_customer_billing_address') )
{
    function wyvern_wc_update_customer_billing_address($billing_address, $customer_id)
    {
        $updated = [];

        if ( ( isset($billing_address['address_1']) || isset($billing_address['address_2']) ) && isset($billing_address['address']) )
        {
            list($address_1, $address_2) = explode("\n", $billing_address['address'], 2);

            $billing_address['address_1'] = $address_1;
            $billing_address['address_2'] = $address_2;

            unset($billing_address['address']);
        }

        foreach( $billing_address as $key => $value )
        {
            if ( !empty($value) )
            {
                update_user_meta($customer_id, 'billing_' . $key, $value);
                $updated[$key] = $value;
            }
        }

        return $updated;
    }
}
