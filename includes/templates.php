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
        ];
    }
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