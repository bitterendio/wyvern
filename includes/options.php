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
        add_theme_page(
            __('Wyvern Theme'),
            __('Wyvern Theme'),
            'manage_options',
            'wyvern_theme_options',
            'wyvern_theme_options_page'
        );
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

            <!-- Here will be new implementation of options -->
            <?php $options = get_option('wyvern_options'); ?>

            <pre><?php var_export($options) ?></pre>

        </div><!-- /.wrap -->
        <?php
    }
}