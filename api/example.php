<?php
/**
 * Sample of custom endpoint for WP Rest Api 2
 */

add_action( 'rest_api_init', function () {
    register_rest_route( 'wp/v2', '/example/(?P<id>\d+)', array(
        'methods' => 'GET',
        'callback' => 'my_example_func',
        'args' => array(
            'id'
        ),
    ) );
} );

function my_example_func( $data ) {
    return 'Your number is : ' . $data['id'];
}