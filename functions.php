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
    // Styles
    wp_enqueue_style( 'normalize', get_template_directory_uri() . '/assets/normalize.css', false, '3.0.3' );
    wp_enqueue_style( 'style', get_stylesheet_directory_uri() . '/dist/styles.css', array( 'normalize' ) );

    // Pace
    wp_enqueue_script( 'pace', get_template_directory_uri() . '/assets/js/pace.min.js', array(), '1.0.0', true );

    // 3rd party scripts from default setup that will not be used
    wp_deregister_script('mailpoet_vendor');
    wp_deregister_script('mailpoet_public');
    wp_deregister_style('mailpoet_public');

    wp_enqueue_script( 'wyvern-vue', get_stylesheet_directory_uri() . '/dist/build.js', array(), '1.0.0', true );

    $base_url  = esc_url_raw( home_url() );
    $base_path = rtrim( parse_url( $base_url, PHP_URL_PATH ), '/' );
    wp_localize_script( 'wyvern-vue', 'wp', apply_filters( 'wyvern_wp_settings', [
        'root'          => esc_url_raw( rest_url() ),
        'base_url'      => $base_url,
        'base_path'     => $base_path ? $base_path . '/' : '/',
        'nonce'         => wp_create_nonce( 'wp_rest' ),
        'site_name'     => get_bloginfo( 'name' ),
        'site_desc'     => get_bloginfo('description'),
        'routes'        => rest_theme_routes(),
        'assets_path'   => get_stylesheet_directory_uri() . '/assets',

        // Inline configurations
        'show_on_front' => get_option('show_on_front'), // (posts|page) Settings -> Reading -> Front page displays
        'page_on_front' => get_option('page_on_front'), // (int) Settings -> Reading -> Front page displays when "page" is selected and type is "Front page"
        'page_for_posts'=> get_option('page_for_posts'), // (int) Settings -> Reading -> Front page displays when "page" is selected and type is "Posts page"
    ] ) );
}

add_action( 'wp_enqueue_scripts', 'rest_theme_scripts' );

/*
|--------------------------------------------------------------------------
| Routes
|--------------------------------------------------------------------------
|
| Register theme routes here
|
*/

function rest_theme_routes() {
    $routes = array();

    $query = new WP_Query( array(
        'post_type'      => 'any',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
    ) );

    if ( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();
            $routes[] = array(
                'id'        => get_the_ID(),
                'type'      => get_post_type(),
                'slug'      => basename( get_permalink() ),
                'link'      => str_replace( site_url(), '', get_permalink() ),
                'template'  => get_page_template_slug()
            );
        }
    }

    wp_reset_postdata();

    return $routes;
}

// Hide admin bar until links in Edit page are eventually resolved
show_admin_bar(false);

/*
|--------------------------------------------------------------------------
| ACF
|--------------------------------------------------------------------------
|
| All ACF fields to rest api
|
*/

add_action( 'rest_api_init', 'slug_register_acf' );
function slug_register_acf() {
    $post_types = get_post_types(['public' => true], 'names');
    foreach ($post_types as $type) {
        register_api_field( $type,
            'acf',
            array(
                'get_callback'    => 'slug_get_acf',
                'update_callback' => null,
                'schema'          => null,
            )
        );
    }
}
function slug_get_acf( $object, $field_name, $request ) {
    if ( function_exists('get_fields') )
    {
        $fields = get_fields($object[ 'id' ]);
        return $fields !== false ? $fields : [];
    } else
    {
        return [];
    }
}

/* If CPT has archive, register archive route */

$post_types = get_post_types();

foreach( $post_types as $post_type )
{

    $cpt = get_post_type_object($post_type);

    if ( $cpt->has_archive )
    {

        $routes[] = [
            'type'     => 'archive',
            'slug'     => $cpt->name,
            'link'     => get_post_type_archive_link($cpt->name),
            'template' => 'archive-' . $cpt->name,
        ];

    }

}

/**
 * Add REST API support to an already registered post type.
 */
add_action( 'init', 'my_custom_post_type_rest_support', 25 );
function my_custom_post_type_rest_support() {
    global $wp_post_types;

    $post_type_names = [
        'authors',
        'partner'
    ];

    foreach( $post_type_names as $post_type_name )
    {

        if ( isset($wp_post_types[ $post_type_name ]) )
        {
            $wp_post_types[ $post_type_name ]->show_in_rest = true;
            $wp_post_types[ $post_type_name ]->rest_base = $post_type_name;
            $wp_post_types[ $post_type_name ]->rest_controller_class = 'WP_REST_Posts_Controller';
        }

    }

}

if ( !function_exists('autoload_folder') )
{
    function autoload_folder($path)
    {
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

$path = get_template_directory() . '/api';
autoload_folder($path);

/*
 * Automatically install necessary plugins via TGM plugin activation
 */

require_once get_template_directory() . '/lib/TGM/class-tgm-plugin-activation.php';

require_once get_template_directory() . '/lib/TGM/plugins.php';

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
        'show_image'   	=> true,
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
        remove_action('wp_enqueue_scripts', [WC_Frontend_Scripts, 'load_scripts']);
        remove_action('wp_enqueue_scripts', [WC_Frontend_Scripts, 'localize_printed_scripts']);
        remove_action('wp_enqueue_scripts', [WC_Frontend_Scripts, 'localize_printed_scripts']);
    }
}

define( 'WOOCOMMERCE_USE_CSS', false );
add_action( 'init', 'wyvern_remove_woocommerce_styles_scripts', 99 );

/*
|--------------------------------------------------------------------------
| Includes
|--------------------------------------------------------------------------
|
| Autoload all php files in /includes folder
|
*/

$path = get_template_directory() . '/includes';
autoload_folder($path);

/*
|--------------------------------------------------------------------------
| Settings
|--------------------------------------------------------------------------
|
| Register settings here
|
*/

Wyvern\Includes\Settings::add('New thing', 'new_thing');

Wyvern\Includes\Settings::section('wyvern_tracking_settings', 'Tracking Options', null, 'wyvern_theme_options_tracking');
Wyvern\Includes\Settings::add('Google Tracking ID', 'google_analytics_id', 'input', null, 'wyvern_tracking_settings', 'wyvern_theme_options_tracking');

Wyvern\Includes\Settings::section('wyvern_woocommerce_settings', 'Woocommerce Options', null, 'wyvern_theme_options_woocommerce');
Wyvern\Includes\Settings::add('Variations in table', 'variations_table', 'checkbox', 0, 'wyvern_woocommerce_settings', 'wyvern_theme_options_woocommerce');