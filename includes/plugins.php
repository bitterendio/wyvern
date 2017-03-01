<?php
/**
 * Support for plugins
 */

/**
 * Include plugin files from web
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
 * Check if plugin is active, then download a file with functionality
 *
 */

function wyvern_check_plugins() {
    // ACF (Advanced Custom Fileds and Advanced Custom Fields PRO)
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
    // Woocommerce
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

// Intuitive CPO

if ( class_exists('Hicpo') ) {

    $intuitive_CPO_options = get_option( 'hicpo_options' )['objects'];

    if ( $intuitive_CPO_options )
    {
        foreach ($intuitive_CPO_options as $option) {

            add_filter( "rest_" . $option ."_query", function( $args, $request )
            {

                if ( !isset($_GET['orderby']) ) {
                    $args['orderby'] = 'menu_order';
                }

                if ( !isset($_GET['order']) ) {
                    $args['order']   = 'asc';
                }

                return $args;
            }, 10, 2 );

        }
    }

}
