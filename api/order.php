<?php
// Add product to cart
add_action( 'rest_api_init', function () {
    register_rest_route( 'api', '/order/', [
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => 'wyvern_wc_create_order',
        'args' => [
            'id'
        ],
    ] );
} );

if ( !function_exists('wyvern_wc_create_order') )
{
    function wyvern_wc_create_order($data)
    {
        $order_options = [];

        if ( $user_id = get_current_user_id() )
        {
            $order_options['customer_id'] = $user_id;
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

        // Address
        $address = [];

        if ( isset($_POST['address']) ) {
            $address = json_decode( stripslashes ( $_POST['address'] ), true);
        } else {
            // Address not specified
        }

        $address = $address['shipping'];

        if ( isset($address['name']) )
        {
            list($first_name, $last_name) = explode(' ', $address['name'], 1);

            $address['first_name'] = $first_name;
            $address['last_name'] = $last_name;
            unset($address['name']);
        }

        if ( isset($address['address']) )
        {
            list($address_1, $address_2) = explode("\n", $address['address'], 1);
            $address['address_1'] = $address_1;
            $address['address_2'] = $address_2;
            unset($address['address']);
        }

        $billing_address = $address;
        $shipping_address = $address;

        $order->calculate_shipping();
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
        if ( $user_id ) {
            if ( apply_filters( 'woocommerce_checkout_update_customer_data', true, $this ) ) {
                foreach ( $billing_address as $key => $value ) {
                    update_user_meta( $user_id, 'billing_' . $key, $value );
                }
                if ( WC()->cart->needs_shipping() ) {
                    foreach ( $shipping_address as $key => $value ) {
                        update_user_meta( $user_id, 'shipping_' . $key, $value );
                    }
                }
            }
            do_action( 'woocommerce_checkout_update_user_meta', $user_id );
        }

        // Let plugins add meta
        do_action( 'woocommerce_checkout_update_order_meta', $order_id, $_POST );

        // If we got here, the order was created without problems!
        wc_transaction_query( 'commit' );

        // Set payment
        //$order->set_payment_method($_POST['payment']);

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
        $order->set_payment_method( $_POST['payment'] );

        $redirect = null;

        if ( in_array($_POST['payment'], ['paypal']) )
        {
            $available_gateways = WC()->payment_gateways->get_available_payment_gateways();
            $result = $available_gateways[ $_POST['payment'] ]->process_payment($order->id);

            $redirect = $result['redirect'];
        }

        return [
            'success' => true,
            'cart' => wyvern_wc_cart(),
            'cart_total' => 0,
            'redirect' => $redirect,
        ];
    }
}

/**
$address = array(
'first_name' => $customer_name,
'last_name'  => '',
'company'    => '',
'email'      => $customer_email,
'phone'      => $customer_phone,
'address_1'  => '',
'address_2'  => '',
'city'       => '',
'state'      => '',
'postcode'   => '',
'country'    => ''
);

$order = wc_create_order();

// add products from cart to order
$items = WC()->cart->get_cart();
foreach($items as $item => $values) {
$product_id = $values['product_id'];
$product = wc_get_product($product_id);
$var_id = $values['variation_id'];
$var_slug = $values['variation']['attribute_pa_weight'];
$quantity = (int)$values['quantity'];
$variationsArray = array();
$variationsArray['variation'] = array(
'pa_weight' => $var_slug
);
$var_product = new WC_Product_Variation($var_id);
$order->add_product($var_product, $quantity, $variationsArray);
}

$order->set_address( $address, 'billing' );
$order->set_address( $address, 'shipping' );

$order->calculate_totals();
$order->update_status( 'processing' );

WC()->cart->empty_cart();
 */