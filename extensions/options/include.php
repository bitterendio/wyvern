<?php

/*
|--------------------------------------------------------------------------
| Options
|--------------------------------------------------------------------------
|
| Create options page for theme options
|
*/

if ( !function_exists('wyvern_theme_options_menu') )
{
    function wyvern_theme_options_menu()
    {
        $wyvern_options_page = add_theme_page(
            __('Wyvern Theme'),
            __('Wyvern Theme'),
            'manage_options',
            'wyvern_theme_options',
            'wyvern_theme_options_page'
        );

        // Load the JS conditionally
        add_action( 'load-' . $wyvern_options_page, 'wyvern_theme_options_load_admin_js' );
    }
}

add_action( 'admin_menu', 'wyvern_theme_options_menu' );


if ( !function_exists('wyvern_theme_options_page') )
{
    function wyvern_theme_options_page()
    {
        ?>
        <!-- Create a header in the default WordPress 'wrap' container -->
        <div class="wrap">

            <div id="icon-themes" class="icon32"></div>
            <h2><?php _e('Wyvern Theme') ?></h2>
            <?php settings_errors(); ?>

            <?php echo file_get_contents(__DIR__ . '/template.html'); ?>

        </div><!-- /.wrap -->
        <?php
    }
}

if ( !function_exists('wyvern_theme_options_load_admin_js') )
{
    function wyvern_theme_options_load_admin_js()
    {
        add_action('admin_enqueue_scripts', 'wyvern_theme_options_enqueue_admin_js');
    }
}

if ( !function_exists('wyvern_theme_options_enqueue_admin_js') )
{
    function wyvern_theme_options_enqueue_admin_js()
    {
        $options = get_option('wyvern_options');

        wp_enqueue_script('vue', 'https://unpkg.com/vue');
        wp_enqueue_style( 'wyvern-options', get_stylesheet_directory_uri() . '/extensions/options/style.css');
        wp_enqueue_script('wyvern-options', get_stylesheet_directory_uri() . '/extensions/options/script.js', ['vue'], '1.0.0', true);

        wp_localize_script( 'wyvern-options', 'wyvernOptions', [
            'root' => esc_url_raw( rest_url() ),
            'nonce' => wp_create_nonce( 'wp_rest' ),
            'options' => $options,
        ] );
    }
}