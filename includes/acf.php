<?php

/*
|--------------------------------------------------------------------------
| ACF
|--------------------------------------------------------------------------
|
| All ACF fields and excerpt to rest api
|
*/

if ( function_exists('register_rest_field') ) {
    add_action( 'rest_api_init', 'wyvern_add_acf_to_posts' );
}

function wyvern_add_acf_to_posts() {
    $post_types = get_post_types(['public' => true], 'names');
    foreach ($post_types as $type) {
        register_rest_field( $type,
            'acf',
            array(
                'get_callback'    => 'wyvern_get_acf',
                'update_callback' => null,
                'schema'          => null,
            )
        );
    }
}
function wyvern_get_acf( $object ) {
    if ( function_exists('get_fields') )
    {
        $fields = get_fields($object[ 'id' ]);
        return $fields !== false ? $fields : [];
    } else
    {
        return [];
    }
}