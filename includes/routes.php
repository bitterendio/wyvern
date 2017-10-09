<?php

/*
|--------------------------------------------------------------------------
| Routes
|--------------------------------------------------------------------------
|
| Register theme routes here
|
*/

if (!function_exists('wyvern_theme_routes'))
{
    function wyvern_theme_routes()
    {
        $routes = [];

        $query = new WP_Query([
            'post_type'      => 'any',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
        ]);

        // If home page is page, add home route
        if ( get_option('show_on_front') === 'page' )
        {
            query_posts([
                'page_id' => get_option('page_on_front'),
            ]);

            while( have_posts() )
            {
                the_post();

                $routes[] = wyvern_get_rest_theme_route();
            }
        }

        wp_reset_postdata();

        // Add all posts to routes
        if ( $query->have_posts() )
        {
            while ( $query->have_posts() )
            {
                $query->the_post();
                $routes[] = wyvern_get_rest_theme_route();
            }
        }

        wp_reset_postdata();

        // Post type archives
        $post_types = get_post_types([], 'objects');

        foreach($post_types as $post_type)
        {
            if ($post_type->has_archive !== false) {
                $routes[] = [
                    'path'  => '/' . ($post_type->has_archive === true ? $post_type->name : $post_type->has_archive),
                    'meta'  => [
                        'wp_title'  => $post_type->label . ' · ' . get_bloginfo('name'),
                        'archive'   => true,
                        'type'      => $post_type->name,
                    ],
                ];
            }
        }

        return apply_filters( 'wyvern_theme_routes', $routes );
    }
}

if ( !function_exists('wyvern_get_rest_theme_route') ) {
    function wyvern_get_rest_theme_route() {
        return [
            'path'     => str_replace(site_url(), '', get_permalink()),
            'meta'     => [
                'id'            => get_the_ID(),
                'type'          => get_post_type(),
                'slug'          => basename(get_permalink()),
                'template'      => get_page_template_slug(),
                'wp_title'      => get_the_title() . ' · ' . get_bloginfo('name'),
            ],
        ];
    }
}
