<?php
/**
 * Woocommerce specific starter
 */

// Get all attributes with their values
if ( !function_exists('woocommerce_get_all_attributes_with_values') )
{
    function woocommerce_get_all_attributes_with_values( $options = ['hide_empty' => false] )
    {
        $result = [];

        $attributes = wc_get_attribute_taxonomies();

        foreach( $attributes as $attribute )
        {
            $options['taxonomy'] = wc_attribute_taxonomy_name($attribute->attribute_name);

            $result[$attribute->attribute_name] = [
                'id' => $attribute->attribute_id,
                'label' => $attribute->attribute_label,
                'type' => $attribute->attribute_type,
                'orderby' => $attribute->attribute_orderby,
                'public' => $attribute->attribute_public,
                'name' => $attribute->attribute_name,
                'taxonomy' => $options['taxonomy'],
                'values' => get_terms($options)
            ];
        }

        return $result;
    }
}

if (!function_exists('wyvern_wc_settings_footer'))
{
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

            'wc_placeholder'     => wc_placeholder_img_src(),

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
}

if (function_exists('WC')) {
    add_filter( 'wyvern_wp_settings', 'wyvern_wc_settings_footer' );
}



/**
 * Image sizes
 */
add_image_size( 'product_image', 260, 260, false );

/**
 * Woocommerce
 */

// Theme support
add_action( 'after_setup_theme', 'woocommerce_support' );
function woocommerce_support() {
    add_theme_support( 'woocommerce' );
}

// Get all attributes with their values
if ( !function_exists('woocommerce_get_all_attributes_with_values') )
{
    function woocommerce_get_all_attributes_with_values( $options = ['hide_empty' => false] )
    {
        $result = [];

        $attributes = wc_get_attribute_taxonomies();

        foreach( $attributes as $attribute )
        {
            $options['taxonomy'] = wc_attribute_taxonomy_name($attribute->attribute_name);

            $result[$attribute->attribute_name] = [
                'id' => $attribute->attribute_id,
                'label' => $attribute->attribute_label,
                'type' => $attribute->attribute_type,
                'orderby' => $attribute->attribute_orderby,
                'public' => $attribute->attribute_public,
                'name' => $attribute->attribute_name,
                'taxonomy' => $options['taxonomy'],
                'values' => get_terms($options)
            ];
        }

        return $result;
    }
}

// Image to order email
/**
 * Adds product images to the WooCommerce order emails table
 * Uses WooCommerce 2.5 or newer
 *
 * @param string $output the buffered email order items content
 * @param \WC_Order $order
 * @return $output the updated output
 */
function sww_add_images_woocommerce_emails( $output, $order ) {

    // set a flag so we don't recursively call this filter
    static $run = 0;

    // if we've already run this filter, bail out
    if ( $run ) {
        return $output;
    }

    $args = array(
        'show_image'    => true,
        'image_size'    => array( 60, 60 ),
    );

    // increment our flag so we don't run again
    $run++;

    // if first run, give WooComm our updated table
    return $order->email_order_items_table( $args );
}
add_filter( 'woocommerce_email_order_items_table', 'sww_add_images_woocommerce_emails', 10, 2 );

/**
 * Remove all WooCommerce scripts and styles! Forever!
 *
 * @author WP Smith, sane
 * @since 1.0.1
 */
function wyvern_remove_woocommerce_styles_scripts() {
    if ( class_exists('WC_Frontend_Scripts') )
    {
        remove_action('wp_enqueue_scripts', ['WC_Frontend_Scripts', 'load_scripts']);
        remove_action('wp_enqueue_scripts', ['WC_Frontend_Scripts', 'localize_printed_scripts']);
        remove_action('wp_enqueue_scripts', ['WC_Frontend_Scripts', 'localize_printed_scripts']);
    }
}

define( 'WOOCOMMERCE_USE_CSS', false );
add_action( 'init', 'wyvern_remove_woocommerce_styles_scripts', 99 );