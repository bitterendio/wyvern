<?php
// Create order
add_action( 'rest_api_init', function () {
    register_rest_route( 'api', '/order/', [
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => 'wyvern_wc_create_order',
        'args' => [
            'id'
        ],
    ] );
} );

// Get order by id
add_action( 'rest_api_init', function () {
    register_rest_route( 'api', '/order/', [
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'wyvern_wc_read_order',
        'args' => [],
    ] );
} );

// Deete order by id
add_action( 'rest_api_init', function () {
    register_rest_route( 'api', '/order/', [
        'methods' => WP_REST_Server::DELETABLE,
        'callback' => 'wyvern_wc_delete_order',
        'args' => [],
    ] );
} );


if ( !function_exists('wyvern_wc_create_order') )
{
    function wyvern_wc_create_order($data)
    {
        $order_options = [];

        $order_options['customer_id'] = 0;

        if ( isset($_POST['customer_id']) )
        {
            $customer_id = (int) $_POST['customer_id'];

            if ( $customer_id )
            {
                $order_options['customer_id'] = $customer_id;
            }
        }

        // Start transaction if available
        wc_transaction_query( 'start' );

        $order_data = [
            'status'        => apply_filters( 'woocommerce_default_order_status', 'pending' ),
            'customer_id'   => $order_options['customer_id'],
            'customer_note' => isset( $_POST['comment'] ) ? $_POST['comment'] : '',
            'cart_hash'     => md5( json_encode( wc_clean( WC()->cart->get_cart_for_session() ) ) . WC()->cart->total ),
            'created_via'   => 'checkout',
        ];

        // Insert or update the post data
        $order_id = absint( WC()->session->order_awaiting_payment );

        /**
         * If there is an order pending payment, we can resume it here so
         * long as it has not changed. If the order has changed, i.e.
         * different items or cost, create a new order. We use a hash to
         * detect changes which is based on cart items + order total.
         */
        if ( $order_id && $order_data['cart_hash'] === get_post_meta( $order_id, '_cart_hash', true ) && ( $order = wc_get_order( $order_id ) ) && $order->has_status( array( 'pending', 'failed' ) ) ) {

            $order_data['order_id'] = $order_id;
            $order                  = wc_update_order( $order_data );

            if ( is_wp_error( $order ) ) {
                throw new Exception( sprintf( __( 'Error %d: Unable to create order. Please try again.', 'woocommerce' ), 522 ) );
            } else {
                $order->remove_order_items();
                do_action( 'woocommerce_resume_order', $order_id );
            }

        } else {

            $order = wc_create_order( $order_data );

            if ( is_wp_error( $order ) ) {
                throw new Exception( sprintf( __( 'Error %d: Unable to create order. Please try again.', 'woocommerce' ), 520 ) );
            } elseif ( false === $order ) {
                throw new Exception( sprintf( __( 'Error %d: Unable to create order. Please try again.', 'woocommerce' ), 521 ) );
            } else {
                $order_id = $order->id;
                do_action( 'woocommerce_new_order', $order_id );
            }
        }

        // Store the line items to the new/resumed order
        foreach ( WC()->cart->get_cart() as $cart_item_key => $values ) {
            $item_id = $order->add_product(
                $values['data'],
                $values['quantity'],
                array(
                    'variation' => $values['variation'],
                    'totals'    => array(
                        'subtotal'     => $values['line_subtotal'],
                        'subtotal_tax' => $values['line_subtotal_tax'],
                        'total'        => $values['line_total'],
                        'tax'          => $values['line_tax'],
                        'tax_data'     => $values['line_tax_data'] // Since 2.2
                    )
                )
            );

            if ( ! $item_id ) {
                throw new Exception( sprintf( __( 'Error %d: Unable to create order. Please try again.', 'woocommerce' ), 525 ) );
            }

            // Allow plugins to add order item meta
            do_action( 'woocommerce_add_order_item_meta', $item_id, $values, $cart_item_key );
        }

        // Store fees
        foreach ( WC()->cart->get_fees() as $fee_key => $fee ) {
            $item_id = $order->add_fee( $fee );

            if ( ! $item_id ) {
                throw new Exception( sprintf( __( 'Error %d: Unable to create order. Please try again.', 'woocommerce' ), 526 ) );
            }

            // Allow plugins to add order item meta to fees
            do_action( 'woocommerce_add_order_fee_meta', $order_id, $item_id, $fee, $fee_key );
        }


        // Set shipping
        $packages = WC()->cart->get_shipping_packages();

        $shipping_methods = WC()->session->get('wyvern_shipping_methods');

        WC()->shipping->calculate_shipping($packages);

        // Store shipping for all packages
        foreach ( WC()->shipping->get_packages() as $package_key => $package ) {
            if ( isset( $package['rates'][ $shipping_methods[ $package_key ] ] ) ) {
                $item_id = $order->add_shipping( $package['rates'][ $shipping_methods[ $package_key ] ] );

                if ( ! $item_id ) {
                    throw new Exception( sprintf( __( 'Error %d: Unable to create order. Please try again.', 'woocommerce' ), 527 ) );
                }

                // Allows plugins to add order item meta to shipping
                do_action( 'woocommerce_add_shipping_order_item', $order_id, $item_id, $package_key );
            }
        }

        // Store tax rows
        foreach ( array_keys( WC()->cart->taxes + WC()->cart->shipping_taxes ) as $tax_rate_id ) {
            if ( $tax_rate_id && ! $order->add_tax( $tax_rate_id, WC()->cart->get_tax_amount( $tax_rate_id ), WC()->cart->get_shipping_tax_amount( $tax_rate_id ) ) && apply_filters( 'woocommerce_cart_remove_taxes_zero_rate_id', 'zero-rated' ) !== $tax_rate_id ) {
                throw new Exception( sprintf( __( 'Error %d: Unable to create order. Please try again.', 'woocommerce' ), 528 ) );
            }
        }

        $billing_address = wyvern_wc_get_address($_POST['billing_address']);

        if ( isset($_POST['shipping_address']) )
            $shipping_address = wyvern_wc_get_address($_POST['shipping_address']);

        $empty_shipping_address = true;

        foreach( $shipping_address as $key => $value )
        {
            if ( !empty($value) )
            {
                $empty_shipping_address = false;
            }
        }

        if ( $empty_shipping_address )
            $shipping_address = wyvern_wc_get_address($_POST['billing_address']);

        $order->calculate_totals();

        $order->set_address( $billing_address, 'billing' );
        $order->set_address( $shipping_address, 'shipping' );

        // Set totals
        $order->set_total( WC()->cart->shipping_total, 'shipping' );
        $order->set_total( WC()->cart->get_cart_discount_total(), 'cart_discount' );
        $order->set_total( WC()->cart->get_cart_discount_tax_total(), 'cart_discount_tax' );
        $order->set_total( WC()->cart->tax_total, 'tax' );
        $order->set_total( WC()->cart->shipping_tax_total, 'shipping_tax' );
        $order->set_total( WC()->cart->total );

        // Update user meta
        if ( $customer_id ) {
            foreach ( $billing_address as $key => $value ) {
                update_user_meta( $customer_id, 'billing_' . $key, $value );
            }
            if ( WC()->cart->needs_shipping() ) {
                foreach ( $shipping_address as $key => $value ) {
                    update_user_meta( $customer_id, 'shipping_' . $key, $value );
                }
            }
            do_action( 'woocommerce_checkout_update_user_meta', $customer_id );
        }

        // Let plugins add meta
        do_action( 'woocommerce_checkout_update_order_meta', $order_id, $_POST );

        // If we got here, the order was created without problems!
        wc_transaction_query( 'commit' );

        // Note
        if ( isset($_POST['note']) )
        {
            $order->add_order_note($_POST['note'], false, true);
        }

        update_post_meta($order->id, '_payment_method', $_POST['payment']);

        if ( isset($_POST['payment']) )
            WC()->session->set('wyvern_payment_method', $_POST['payment']);

        $order->calculate_shipping();
        $order->calculate_totals();

        // Conditionally set status
        $order->update_status( 'pending' );

        // Empty cart
        WC()->cart->empty_cart();

        // Payment
        $available_gateways = WC()->payment_gateways->get_available_payment_gateways();
        $order->set_payment_method( $available_gateways[ $_POST['payment'] ] );

        $redirect = null;

        if ( in_array($_POST['payment'], ['paypal']) )
        {
            $result = $available_gateways[ $_POST['payment'] ]->process_payment($order->id);

            $redirect = $result['redirect'];
        }

        if ( empty($redirect) )
        {
            $redirect = '/checkout/order-received/' . $order->id . '/?method=' . $_POST['payment'];
        }

        return [
            'success' => true,
            'cart' => wyvern_wc_cart(),
            'cart_total' => 0,
            'redirect' => $redirect,
        ];
    }
}

if ( !function_exists('wyvern_wc_get_address') ) {
    function wyvern_wc_get_address($address)
    {
        if ( empty($address) )
            return [];

        $address = json_decode( stripslashes ( $address ), true);

        if ( isset($address['name']) )
        {
            $parts = explode(' ', $address['name'], 2);

            if (count($parts) === 2)
            {
                list($first_name, $last_name) = $parts;
                $address['first_name'] = $first_name;
                $address['last_name'] = $last_name;
                unset($address['name']);
            } else {
                $address['first_name'] = $address['name'];
            }
        }

        if ( isset($address['address']) )
        {
            $parts = explode("\n", $address['address'], 2);

            if (count($parts) === 2)
            {
                list($address_1, $address_2) = $parts;
                $address['address_1'] = $address_1;
                $address['address_2'] = $address_2;
                unset($address['address']);
            } else {
                $address['address_1'] = $address['address'];
            }
        }

        return $address;
    }
}


if ( !function_exists('wyvern_wc_read_order') )
{
    function wyvern_wc_read_order()
    {
        $order = new WC_Order((int)$_GET['order_id']);

        $payment_method = [
            'id' => get_post_meta($order->id, '_payment_method', true),
            'title' => get_post_meta($order->id, '_payment_method_title', true),
        ];

        $available_gateways = WC()->payment_gateways->get_available_payment_gateways();

        $gateway = $available_gateways[$payment_method['id']];

        if ( !$order->is_paid() && $payment_method['id'] == 'cod' )
        {
            $order->update_status( 'processing' );
        }

        return [
            'is_paid' => $order->is_paid(),
            'status' => $order->get_status(),
            'transaction_id' => $order->get_transaction_id(),
            'gateway' => $gateway,
            'shipping_methods' => $order->get_shipping_methods(),
            'billing_address' => $order->get_formatted_billing_address(),
            'shipping_address' => $order->get_formatted_shipping_address(),
            'notes' => $order->get_customer_order_notes(),
            'prices' => [
                'total' => $order->get_total(),
                'shipping' => $order->get_total_shipping(),
            ]
        ];
    }
}

if ( !function_exists('wyvern_wc_delete_order') )
{
    function wyvern_wc_delete_order()
    {
        if ( !isset($_GET['order_id']) )
            return [];

        $order_id = (int)$_GET['order_id'];

        $order = new WC_Order($order_id);

        return [
            'success' => $order->cancel_order()
        ];
    }
}