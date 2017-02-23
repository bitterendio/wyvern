<?php
/**
 * Menu endpoint
 */
add_action( 'rest_api_init', function () {
    register_rest_route( 'api', '/thumbnails/(?P<id>\d+)', [
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'wyvern_get_thumbnail_url',
        'args' => [
            'id'
        ],
    ] );
} );

if ( !function_exists('wyvern_get_thumbnail_url') )
{
    function wyvern_get_thumbnail_url($data)
    {
        if ( !isset($data['id']) )
            return ['msg' => __('Post id was not specified')];

        $source = wp_get_attachment_image_src(get_post_thumbnail_id($data['id']), 'thumbnail');

        if ( !isset($source[0]) )
            return ['msg' => __('Post has no thumbnail')];

        return $source[0];
    }
}