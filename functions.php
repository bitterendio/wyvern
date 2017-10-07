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
    $filepath = get_stylesheet_directory() . '/dist/manifest.json';
    if (file_exists($filepath))
    {
        $current_assets = json_decode(file_get_contents($filepath), true);
    }

    // Styles
    if (isset($current_assets['app.css']))
        wp_enqueue_style( 'style', get_stylesheet_directory_uri() . '/dist/' . $current_assets['app.css'], array(), '1.0.0' );

    // Scripts
    if (isset($current_assets['manifest.js']))
        wp_enqueue_script( 'wyvern-vue-manifest', get_stylesheet_directory_uri() . '/dist/' . $current_assets['manifest.js'], array(), '1.0.0', true );

    if (isset($current_assets['vendor.js']))
        wp_enqueue_script( 'wyvern-vue-vendor', get_stylesheet_directory_uri() . '/dist/' . $current_assets['vendor.js'], array(), '1.0.0', true );

    if (isset($current_assets['app.js']))
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

add_action( 'send_headers', function() {
    if ( ! did_action('rest_api_init') ) {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Expose-Headers: Link");
        header("Access-Control-Allow-Methods: HEAD");
    }
} );

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
            'posts_per_page' => -1,
        ]);

        // If home page is page, add home route
        if ( get_option('show_on_front') === 'page' )
        {
            query_posts([
                'page_id' => get_option('page_on_front'),
            ]);

            while( have_posts() )
            {
                the_post();

                $routes[] = get_rest_theme_route();
            }
        }

        wp_reset_postdata();

        // Add all posts to routes
        if ( $query->have_posts() )
        {
            while ( $query->have_posts() )
            {
                $query->the_post();
                $routes[] = get_rest_theme_route();
            }
        }

        wp_reset_postdata();

        // Post type archives
        $post_types = get_post_types([], 'objects');

        foreach($post_types as $post_type)
        {
            if ($post_type->has_archive !== false) {
                $routes[] = [
                    'path'  => '/' . ($post_type->has_archive === true ? $post_type->name : $post_type->has_archive),
                    'meta'  => [
                        'wp_title'  => $post_type->label . ' · ' . get_bloginfo('name'),
                        'archive'   => true,
                        'type'      => $post_type->name,
                    ],
                ];
            }
        }

        return apply_filters( 'rest_theme_routes', $routes );
    }
}

if ( !function_exists('get_rest_theme_route') ) {
    function get_rest_theme_route() {
        return [
            'path'     => str_replace(site_url(), '', get_permalink()),
            'meta'     => [
                'id'            => get_the_ID(),
                'type'          => get_post_type(),
                'slug'          => basename(get_permalink()),
                'template'      => get_page_template_slug(),
                'wp_title'      => get_the_title() . ' · ' . get_bloginfo('name'),
            ],
        ];
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

