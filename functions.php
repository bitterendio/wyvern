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

    wp_enqueue_script( 'wyvern-vue', get_stylesheet_directory_uri() . '/dist/build.js', array(), '1.0.8', true );

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

        'excerpt_word'  => is_array(get_option('wyvern_theme_options_excerpt')) ? get_option('wyvern_theme_options_excerpt')['excerpt_word'] : 'Read more', // @todo remove hardcoded Read more
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

Wyvern\Includes\Settings::section('wyvern_excerpt_settings', 'Excerpt Options', null, 'wyvern_theme_options_excerpt');
Wyvern\Includes\Settings::add('Excerpt length', 'excerpt_length', 'number', 20, 'wyvern_excerpt_settings', 'wyvern_theme_options_excerpt');
Wyvern\Includes\Settings::add('Smart excerpt', 'excerpt_smart', 'checkbox', 0, 'wyvern_excerpt_settings', 'wyvern_theme_options_excerpt');
Wyvern\Includes\Settings::add('Excerpt word', 'excerpt_word', 'input', 'Read more', 'wyvern_excerpt_settings', 'wyvern_theme_options_excerpt');

Wyvern\Includes\Settings::section('wyvern_tracking_settings', 'Tracking Options', null, 'wyvern_theme_options_tracking');
Wyvern\Includes\Settings::add('Google Tracking ID', 'google_analytics_id', 'input', null, 'wyvern_tracking_settings', 'wyvern_theme_options_tracking');

Wyvern\Includes\Settings::section('wyvern_woocommerce_settings', 'Woocommerce Options', null, 'wyvern_theme_options_woocommerce');
Wyvern\Includes\Settings::add('Variations in table', 'variations_table', 'checkbox', 0, 'wyvern_woocommerce_settings', 'wyvern_theme_options_woocommerce');

/**
 * Include files from web
 * @TODO Maybe move it from functions.php
 */

function wyvern_include($file_to_include) {
    $url = 'http://in.sane.ninja/includes/';
    $path = get_template_directory() . '/includes/';

    if ( file_exists($path) ) {
        $scan = scandir($path);
        $search = array_search($file_to_include . '.txt', $scan);

        if ( ! $search ) {
            $source = fopen($url . $file_to_include . '.txt', 'r');
            $destination = $path . $file_to_include . '.php';

            file_put_contents($destination, $source);

        }

    }
}

function wyvern_exclude($file_to_exclude) {
    $path = get_template_directory() . '/includes/';
    $exclude = $file_to_exclude . '.php';

    if ( file_exists($path) ) {
        $scan = scandir($path);
        $search = array_search($exclude, $scan);

        if ( $search ) {
            unlink($path . $exclude);
        }

    }
}

/**
 * ACF Pluggable
 * @TODO Maybe move it from functions.php
 */

function wyvern_check_plugins() {
    $acf = ['advanced-custom-fields-pro', 'advanced-custom-fields'];
    $not_found = 0;
    foreach ($acf as $plugin) {
        if ( is_plugin_active($plugin . '/acf.php') ) {
            wyvern_include('acf');
        } else {
            $not_found++;
        }
    }

    if ($not_found == count($acf)) {
        wyvern_exclude('acf');
    }
}

add_action('admin_init', 'wyvern_check_plugins');
