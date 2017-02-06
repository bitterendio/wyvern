<?php

/*
|--------------------------------------------------------------------------
| Page templates
|--------------------------------------------------------------------------
|
| Register custom page templates without actually
| creating files.
|
*/

if ( !function_exists('get_virtual_templates') )
{
    function get_virtual_templates()
    {
        return [
            'custom'    => 'Custom template',
            'cart'      => 'Cart template',
            'pay'       => 'Pay template',
            'account'   => 'Account template',
        ];
    }
}

/**
 * Wordpress < 4.7
 */

function get_custom_page_templates() {
    $templates = get_virtual_templates();
    return apply_filters( 'custom_page_templates', $templates );
}

add_action( 'edit_form_after_editor', 'custom_page_templates_init' );
add_action( 'load-post.php', 'custom_page_templates_init_post' );
add_action( 'load-post-new.php', 'custom_page_templates_init_post' );

function custom_page_templates_init() {
    remove_action( current_filter(), __FUNCTION__ );
    if ( is_admin() && get_current_screen()->post_type === 'page' ) {
        $templates = get_custom_page_templates(); // the function above
        if ( ! empty( $templates ) )  {
            set_custom_page_templates( $templates );
        }
    }
}

function custom_page_templates_init_post() {
    remove_action( current_filter(), __FUNCTION__ );
    $method = filter_input( INPUT_SERVER, 'REQUEST_METHOD', FILTER_SANITIZE_STRING );
    if ( empty( $method ) || strtoupper( $method ) !== 'POST' ) return;
    if ( get_current_screen()->post_type === 'page' ) {
        custom_page_templates_init();
    }
}

function set_custom_page_templates( $templates = array() ) {
    if ( ! is_array( $templates ) || empty( $templates ) ) return;
    $core = array_flip( (array) get_page_templates() ); // templates defined by file
    $data = array_filter( array_merge( $core, $templates ) );
    ksort( $data );
    $stylesheet = get_stylesheet();
    $hash = md5( get_theme_root( $stylesheet ) . '/' . $stylesheet );
    $persistently = apply_filters( 'wp_cache_themes_persistently', false, 'WP_Theme' );
    $exp = is_int( $persistently ) ? $persistently : 1800;
    wp_cache_set( 'page_templates-' . $hash, $data, 'themes', $exp );
}

/**
 * Wordpress >= 4.7
 */
function makewp_exclude_page_templates( $post_templates ) {
    $templates = get_virtual_templates();

    if ( version_compare( $GLOBALS['wp_version'], '4.7', '>=' ) ) {
        foreach( $templates as $key => $value )
        {
            $post_templates[$key] = $value;
        }
    }

    return $post_templates;
}

add_filter( 'theme_page_templates', 'makewp_exclude_page_templates' );