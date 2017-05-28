<?php

/*
|--------------------------------------------------------------------------
| Theme support
|--------------------------------------------------------------------------
|
| title-tag: allows theme to put content in <title> tag
| menus: allows theme to have menus
|
*/

add_theme_support( 'title-tag' );
add_theme_support( 'menus' );
add_theme_support( 'post-thumbnails' );

/*
|--------------------------------------------------------------------------
| Styles and scripts
|--------------------------------------------------------------------------
|
| Register default styles and scripts here
|
*/

function rest_theme_scripts() {

    // Get current asset names
    $current_assets = json_decode(file_get_contents(__DIR__ . '/dist/manifest.json'), true);

    // Styles
    wp_enqueue_style( 'style', get_stylesheet_directory_uri() . '/dist/' . $current_assets['app.css'], array(), '1.0.0' );

    wp_enqueue_script( 'wyvern-vue-manifest', get_stylesheet_directory_uri() . '/dist/' . $current_assets['manifest.js'], array(), '1.0.0', true );
    wp_enqueue_script( 'wyvern-vue-vendor', get_stylesheet_directory_uri() . '/dist/' . $current_assets['vendor.js'], array(), '1.0.0', true );
    wp_enqueue_script( 'wyvern-vue-app', get_stylesheet_directory_uri() . '/dist/' . $current_assets['app.js'], array(), '1.0.0', true );

    // Geenerate config script
    $base_url  = esc_url_raw( home_url() );
    $base_path = rtrim( parse_url( $base_url, PHP_URL_PATH ), '/' );

    wp_localize_script( 'wyvern-vue-app', 'config', apply_filters( 'wyvern_wp_settings', [
        'root'          => esc_url_raw( rest_url() ),
        'base_url'      => $base_url,
        'base_path'     => $base_path ? $base_path . '/' : '/',
        'nonce'         => wp_create_nonce( 'wp_rest' ),
        'site_name'     => get_bloginfo( 'name' ),
        'site_desc'     => get_bloginfo('description'),
        'routes'        => rest_theme_routes(),
    ] ) );
}

add_action( 'wp_enqueue_scripts', 'rest_theme_scripts' );


// Hotfix
// @todo: find out if multiple values in Access-Control can be solved otherwise
add_action('rest_api_init', function() {
    remove_filter('rest_pre_serve_request', 'rest_send_cors_headers');
}, 15);


/*
|--------------------------------------------------------------------------
| Routes
|--------------------------------------------------------------------------
|
| Register theme routes here
|
*/

if (!function_exists('rest_theme_routes'))
{
    function rest_theme_routes()
    {
        $routes = [];

        $query = new WP_Query([
            'post_type'      => 'any',
            'post_status'    => 'publish',
            'posts_per_page' => - 1,
        ]);

        // @temp
        $routes[] = [
            'path' => '/',
            'meta' => [
                'id'        => 2,
                'type'      => 'page',
                'slug'      => 'home',
                'template'  => 'page',
            ]
        ];

        if ( $query->have_posts() )
        {
            while ( $query->have_posts() )
            {
                $query->the_post();
                $routes[] = [
                    'path'     => str_replace(site_url(), '', get_permalink()),
                    'meta'     => [
                        'id'       => get_the_ID(),
                        'type'     => get_post_type(),
                        'slug'     => basename(get_permalink()),
                        'template' => get_page_template_slug(),
                    ],
                ];
            }
        }

        wp_reset_postdata();

        return $routes;
    }
}

// Hide admin bar until links in Edit page are eventually resolved
show_admin_bar(false);

if ( !function_exists('autoload_folder') )
{
    function autoload_folder($path)
    {
        if (!is_dir($path))
            return false;

        if ($handle = opendir($path)) {

            while (false !== ($entry = readdir($handle))) {

                if ($entry != "." && $entry != ".." && !is_dir($path . '/' . $entry) ) {

                    require_once ($path . '/' . $entry);

                }
            }

            closedir($handle);
        }
    }
}

/*
|--------------------------------------------------------------------------
| API
|--------------------------------------------------------------------------
|
| Load all calls registered in separate files in /api folder
|
*/

$relative_path = '/api';
foreach( array_unique([get_template_directory(), get_stylesheet_directory()]) as $folder )
{
    autoload_folder($folder . $relative_path);
}

/*
|--------------------------------------------------------------------------
| Includes
|--------------------------------------------------------------------------
|
| Autoload all php files in /includes folder
|
*/

$relative_path = '/includes';
foreach( array_unique([get_template_directory(), get_stylesheet_directory()]) as $folder )
{
    autoload_folder($folder . $relative_path);
}

/*
|--------------------------------------------------------------------------
| Settings
|--------------------------------------------------------------------------
|
| Register settings here
|
*/

if (class_exists('Wyvern\Includes\Settings'))
{
    Wyvern\Includes\Settings::add('New thing', 'new_thing');

    Wyvern\Includes\Settings::section('wyvern_excerpt_settings', 'Excerpt Options', null, 'wyvern_theme_options_excerpt');
    Wyvern\Includes\Settings::add('Excerpt length', 'excerpt_length', 'number', 20, 'wyvern_excerpt_settings', 'wyvern_theme_options_excerpt');
    Wyvern\Includes\Settings::add('Smart excerpt', 'excerpt_smart', 'checkbox', 0, 'wyvern_excerpt_settings', 'wyvern_theme_options_excerpt');
    Wyvern\Includes\Settings::add('Excerpt word', 'excerpt_word', 'input', 'Read more', 'wyvern_excerpt_settings', 'wyvern_theme_options_excerpt');

    Wyvern\Includes\Settings::section('wyvern_tracking_settings', 'Tracking Options', null, 'wyvern_theme_options_tracking');
    Wyvern\Includes\Settings::add('Google Tracking ID', 'google_analytics_id', 'input', null, 'wyvern_tracking_settings', 'wyvern_theme_options_tracking');

    Wyvern\Includes\Settings::section('wyvern_woocommerce_settings', 'Woocommerce Options', null, 'wyvern_theme_options_woocommerce');
    Wyvern\Includes\Settings::add('Variations in table', 'variations_table', 'checkbox', 0, 'wyvern_woocommerce_settings', 'wyvern_theme_options_woocommerce');
}

/**
 * Include files from web
 * @TODO Maybe move it from functions.php
 */

function wyvern_include($file_to_include, $source_path, $destination_path = NULL) {
    $url = 'http://in.sane.ninja/' . $source_path . '/';
    $path = get_template_directory() . '/' . $destination_path;

    if ( file_exists($path) ) {
        $scan = scandir($path);
        $search = array_search($file_to_include . '.txt', $scan);

        if ( ! $search ) {
            $source = fopen($url . $file_to_include . '.txt', 'r');
            $destination = $path . '/' . $file_to_include . '.php';

            file_put_contents($destination, $source);

        }

    }
}

function wyvern_exclude($file_to_exclude, $destination_path = NULL) {
    $path = get_template_directory() . '/' . $destination_path;
    $exclude = $file_to_exclude . '.php';

    if ( file_exists($path) ) {
        $scan = scandir($path);
        $search = array_search($exclude, $scan);

        if ( $search ) {
            unlink($path . '/' . $exclude);
        }

    }
}

/**
 * ACF Pluggable
 * @TODO Maybe move it from functions.php
 */

function wyvern_check_plugins() {
    $acf = ['advanced-custom-fields-pro', 'advanced-custom-fields'];
    $acf_not_found = 0;

    foreach ($acf as $plugin) {
        if ( is_plugin_active($plugin . '/acf.php') ) {
            wyvern_include('acf', 'includes', 'includes');
        } else {
            $acf_not_found++;
        }
    }

    if ( $acf_not_found == count($acf) ) {
        wyvern_exclude('acf', 'includes');
    }

    $woocommerce = 'woocommerce';

    if ( is_plugin_active($woocommerce . '/woocommerce.php') ) {
        wyvern_include('woocommerce', 'includes', 'includes');
        wyvern_include('woocommerce', 'includes/root');
    } else {
        wyvern_exclude('woocommerce', 'includes');
        wyvern_exclude('woocommerce');
    }

}

add_action('admin_init', 'wyvern_check_plugins');


// Temp

if (!function_exists('wyvern_wc_get_shipping'))
{
    function wyvern_wc_get_shipping() {

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
                        'cost' => $rate->cost,
                    ];
                }
            }
        }

        return $shipping;

    }
}

if (!function_exists('wyvern_wc_get_gateways'))
{
    function wyvern_wc_get_gateways() {

        return wyvern_wc_filter_payment_gateways(WC()->payment_gateways->get_available_payment_gateways());

    }
}

if (!function_exists('wyvern_wc_filter_payment_gateways'))
{
    function wyvern_wc_filter_payment_gateways($gateways = []) {

        foreach($gateways as $gateway_slug => &$gateway) {
            $gateway->enabled_methods = $gateway->get_option('enable_for_methods', []);
            $gateway->extra_charges = get_option('woocommerce_'.$gateway_slug.'_extra_charges', 0);
        }

        return $gateways;

    }
}

/*
if ( isset($_GET['heurekaset']) ) {

    $args = [
        'post_type' => 'product',
        'posts_per_page' => 100,
    ];

    $query = new WP_Query($args);

    while($query->have_posts()): $query->the_post();

        update_post_meta(get_the_ID(), 'ceske_sluzby_xml_heureka_kategorie', 'Heureka.cz | Oblečení a móda | Obuv | Dětská obuv');

    endwhile;

}
*/
