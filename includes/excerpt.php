<?php

function wyvern_create_excerpt($text, $default_excerpt_length = 20)
{
    $excerpt_options = get_option('wyvern_theme_options_excerpt');
    $ending_chars = ['.', ' ', '?', '!'];

    $excerpt_length = isset($excerpt_options['excerpt_length']) ? $excerpt_options['excerpt_length'] : $default_excerpt_length;

    if ( strlen($text) <= $excerpt_length )
        return $text;

    $excerpt_text = substr($text, 0, $excerpt_length);

    if (! isset($excerpt_options['excerpt_smart'])) {
        $excerpt = $excerpt_text;
    } else {
        $counts = [];
        foreach ( $ending_chars as $char ) {
            $last_occurrence = strripos($excerpt_text, $char);
            if ($last_occurrence){
                array_push($counts, strripos($excerpt_text, $char));
            }
        }
        rsort($counts);
        $excerpt = substr($excerpt_text, 0, $counts[0]);
    }

    return $excerpt;

}

if ( function_exists('register_rest_field') ) {
    add_action( 'rest_api_init', 'wyvern_add_excerpt_to_posts' );
}

function wyvern_add_excerpt_to_posts() {
    $post_types = get_post_types(['public' => true], 'names');
    foreach ($post_types as $type) {
        register_rest_field( $type,
            'excerpt',
            array(
                'get_callback'    => 'wyvern_add_excerpt',
                'update_callback' => null,
                'schema'          => null,
            )
        );
    }
}

function wyvern_add_excerpt($object)
{
    $id = $object['id'];
    $post = get_post($id);
    $content = $post->post_content;

    $excerpt = wyvern_create_excerpt($content);
    return $excerpt;
}
