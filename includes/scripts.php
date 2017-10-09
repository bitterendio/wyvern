<?php

/*
|--------------------------------------------------------------------------
| Scripts
|--------------------------------------------------------------------------
|
| Register default styles and scripts here
|
*/

if ( !function_exists('wyvern_theme_scripts') )
{
    function wyvern_theme_scripts() {

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

        wp_localize_script( 'wyvern-vue-app', 'config', apply_filters( 'wyvern_wp_settings',  wyvern_theme_config()) );
    }
}

add_action( 'wp_enqueue_scripts', 'wyvern_theme_scripts' );

if ( !function_exists('wyvern_theme_scripts') )
{

    function wyvern_theme_config()
    {
        // Geenerate config script
        $base_url = esc_url_raw(home_url());
        $base_path = rtrim(parse_url($base_url, PHP_URL_PATH), '/');

        // Get Wyvern options
        $options = get_option('wyvern_options');
        $non_private = array_filter($options, function ($item) {
            if ( (isset($item['private']) && !$item['private']) || !isset($item['private']) )
            {
                return true;
            }

            return false;
        });

        return [
            'root'          => esc_url_raw(rest_url()),
            'base_url'      => $base_url,
            'base_path'     => $base_path ? $base_path . '/' : '/',
            'nonce'         => wp_create_nonce('wp_rest'),
            'site_name'     => get_bloginfo('name'),
            'site_desc'     => get_bloginfo('description'),
            'routes'        => wyvern_theme_routes(),
            'wyvernOptions' => $non_private,
        ];
    }
}
