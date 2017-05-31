<?php
/**
 * Products endpoint
 */

add_action( 'rest_api_init', function () {
    register_rest_route( 'wyvern/v1', '/products/(?P<id>\d+)', [
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'wyvern_wc_get_products',
        'args' => [
            'id'
        ],
    ] );
} );

add_action( 'rest_api_init', function () {
    register_rest_route( 'wyvern/v1', '/products/', [
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'wyvern_wc_get_products',
        'args' => [],
    ] );
} );

/**
 * Products API return single or all products
 * @param $data
 * @return mixed
 */
if ( !function_exists('wyvern_wc_get_products') )
{
    function wyvern_wc_get_products($data)
    {
        if ( isset($data['id']) )
        {
            return wyvern_wc_get_products_single($data['id']);
        }

        return wyvern_wc_get_products_index();
    }
}

/**
 * Retrieve all products
 */
if ( !function_exists('wyvern_wc_get_products_index') )
{
    function wyvern_wc_get_products_index( $posts_per_page = 100, $paged = 1, $orderby = 'date', $order = 'desc' )
    {
        $results = [];

        if (isset($_GET['posts_per_page'])) {
            $posts_per_page = (int)$_GET['posts_per_page'];
        }

        if (isset($_GET['paged'])) {
            $paged = (int)$_GET['paged'];
        }

        $args = [
            'post_type' => 'product',
            'posts_per_page' => $posts_per_page,
            'paged' => $paged,
        ];

        if (isset($_GET['orderby'])) {
            $orderby = strtolower($_GET['orderby']);

            if ($orderby === 'price') {
                $args['orderby'] = 'meta_value_num';
                $args['meta_key'] = '_price';
            } else {
                $args['orderby'] = $orderby;
            }
        }

        if (isset($_GET['order'])) {
            $order = (strtolower($_GET['order']) == 'asc' || strtolower($_GET['order']) == 'desc') ? strtolower($_GET['order']) : 'desc';
            $args['order'] = $order;
        }

        if ( isset($_GET['filters']) )
        {
            $filters = $_GET['filters'];
            $prepared = [];

            if ( !empty($filters) ) {
                $filters = json_decode(stripslashes($filters), true);
            } else {
                $filters = [];
            }

            if (is_array($filters))
            {
                foreach ( $filters as $name => $filter )
                {
                    $numeric = true;

                    if ( is_array($filter) )
                    {
                        foreach ( $filter as $filter_value )
                        {
                            if ( !is_numeric($filter_value) )
                            {
                                $numeric = false;
                            }
                        }
                    } else
                    {
                        if ( !is_numeric($filter) )
                        {
                            $numeric = false;
                        }
                    }

                    if ( !empty($filter) && $name !== 'search' && $name !== 'filters' )
                    {
                        $prepared[] = [
                            'taxonomy' => wc_attribute_taxonomy_name($name),
                            'field'    => $numeric && $name != 'delka' && $name != 'sirka' ? 'term_id' : 'slug',
                            'terms'    => $filter,
                            'operator' => 'IN'
                        ];
                    } elseif ( !empty($filter) && $name === 'search' ) {
                        $args['s'] = $filter;
                    }
                }
            }

            if ( !empty($prepared) ) {
                $args['tax_query'] = [
                    'relation' => 'AND'
                ];
                foreach( $prepared as $prepared_tax_query )
                {
                    $args['tax_query'][] = $prepared_tax_query;
                }
            }
        }

        if (isset($_GET['category']))
        {
            $args['tax_query'] = [
                'relation' => 'AND',
                [
                    'taxonomy' => 'product_cat',
                    'field' => 'slug',
                    'terms' => $_GET['category']
                ]
            ];
        }

        $query = new WP_Query( $args );

        if ( $query->have_posts() ) {

            while ( $query->have_posts() ) : $query->the_post();

                $results[] = wyvern_wc_get_products_single(get_the_ID());

            endwhile;

        } else {
            return [
                'msg' => __( 'No products found' ),
                'data' => $results
            ];
        }

        wp_reset_postdata();

        return [
            'pagination' => [
                'max_num_pages' => (int)$query->max_num_pages,
                'paged' => (int)$paged,
                'found_posts' => (int)$query->found_posts,
            ],
            'products' => $results,
        ];
    }
}

/**
 * Retrieve single product
 * @param       $id
 * @param array $return Array of values to return
 * @return array
 */
if ( !function_exists('wyvern_wc_get_products_single') )
{
    function wyvern_wc_get_products_single($id, $return = [
        'id',
        'title',
        'content',
        'content_raw',
        'excerpt',
        'images',
        'prices',
        'formatted_prices',
        'gallery',
        'permalink',
        'slug',
        'attributes',
        'availableVariations',
        'defaultAttributesVariation',
        'related',
        'stock',
        'custom_attributes',
    ])
    {
        global $post;

        $post = get_post($id);

        // Check if retrieved post is product
        if ( $post->post_type !== 'product' )
        {
            return [
                'msg' => __('No products found')
            ];
        }

        // Basic data
        $id = get_the_ID();
        if ( in_array('title', $return) )
            $title = get_the_title();

        if ( in_array('content', $return) || in_array('content_raw') )
            $content_raw = $post->post_content;

        if ( in_array('content', $return) )
            $content = apply_filters( 'the_content', $content_raw );

        if ( in_array('excerpt', $return) )
            $excerpt = get_the_excerpt();

        if ( in_array('permalink', $return) )
            $permalink = get_permalink();

        if ( in_array('slug', $return) )
            $slug = $post->post_name;

        // Woocommerce product
        $product = wc_get_product( $id );

        // Product stock status
        if ( in_array('stock', $return) )
        {
            $stock = [
                'manage_stock' => get_post_meta($product->id, '_manage_stock', true),
                'stock' => get_post_meta($product->id, '_stock', true),
                'backorders' => get_post_meta($product->id, '_backorders', true),
                'stock_status' => get_post_meta($product->id, '_stock_status', true)
            ];
        }

        // Product attributes
        if ( in_array('attributes', $return) )
        {
            $attributes = [];

            $possible_attributes = wc_get_attribute_taxonomies();

            foreach( $possible_attributes as $attribute )
            {
                $taxonomy_name = wc_attribute_taxonomy_name($attribute->attribute_name);

                $ids = wc_get_product_terms( $product->id, $taxonomy_name, [ 'fields' => 'ids' ] );

                $attributes[$attribute->attribute_name] = [];

                foreach ( $ids as $term_id )
                {
                    $term = get_term_by('id', $term_id, $taxonomy_name);

                    $value = (array)$term;

                    // @todo: remove exception
                    if ( $taxonomy_name == 'pa_barva' )
                    {
                        $value['barva'] = get_field('barva', $term);
                    }

                    // @todo: remove exception
                    if ( $taxonomy_name == 'pa_znacka' )
                    {
                        $value['logo'] = get_field('logo', $term);
                    }

                    $attributes[$attribute->attribute_name][] = $value;
                }
            }
        }

        // Custom attributes
        if ( in_array('custom_attributes', $return) )
        {
            $custom_attributes = get_post_meta($product->id, '_product_attributes', true);

            foreach($custom_attributes as $key => $value) {
                if (isset($attributes[str_replace('pa_', '', $key)])) {
                    unset($custom_attributes[$key]);
                }
            }
        }

        // Product images
        $images = [];
        $thumbnail_id = get_post_thumbnail_id($id);

        $sizes = get_intermediate_image_sizes();
        $sizes[] = 'full';

        foreach( $sizes as $size )
        {
            $images[$size] = wp_get_attachment_image_src($thumbnail_id, $size);
        }

        // Product gallery
        $gallery = [];

        $attachment_ids = $product->get_gallery_attachment_ids();

        foreach( $attachment_ids as $attachment_id )
        {
            foreach( $sizes as $size )
            {
                if ( !isset($gallery[$size]) )
                    $gallery[$size] = [];

                $gallery[$size][] = wp_get_attachment_image_src($attachment_id, $size);
            }
        }

        // Variations
        $availableVariations = [];

        if ( method_exists($product, 'get_available_variations') )
            $availableVariations = $product->get_available_variations();

        // Default variations
        $defaultAttributesVariation = [];

        if ( method_exists($product, 'get_variation_default_attribute') )
        {
            foreach ( $attributes as $attribute_key => $attribute_values )
            {
                $defaultAttributesVariation[ $attribute_key ] = $product->get_variation_default_attribute(wc_attribute_taxonomy_name($attribute_key));
            }
        }

        // Related products
        if ( in_array('related', $return) )
        {
            $related_ids = get_post_meta($product->id, '_related_ids', true);

            $related = false;

            if ($related_ids !== false && !empty($related_ids))
            {
                $related = [];

                foreach($related_ids as $product_id)
                {
                    // @todo: make for all cases, remove hardcoded taxonomy
                    $taxonomy_name = 'pa_barva';

                    $ids = wc_get_product_terms( $product_id, $taxonomy_name, [ 'fields' => 'ids' ] );

                    $related[$product_id] = [
                        'id' => $product_id,
                        'pa_barva' => [],
                        'permalink' => get_permalink($product_id),
                    ];

                    foreach ( $ids as $term_id )
                    {
                        $term = get_term_by('id', $term_id, $taxonomy_name);

                        $value = (array)$term;

                        // @todo: remove exception
                        if ( $taxonomy_name == 'pa_barva' )
                        {
                            $value['barva'] = get_field('barva', $term);
                        }

                        $related[$product_id]['pa_barva'][] = $value;
                    }
                }
            }
        }

        // Product prices
        if ( empty($availableVariations) )
        {
            $prices = [
                'regular' => $product->get_regular_price(),
                'sale'    => $product->get_sale_price(),
                'base'    => $product->get_price()
            ];
        } else {

            $product_variable  =  new WC_Product_Variable( $id );

            $min_or_max = 'min';

            $prices = [
                'regular' => $product_variable->get_variation_regular_price($min_or_max),
                'sale'    => $product_variable->get_variation_sale_price($min_or_max),
                'base'    => $product_variable->get_variation_price($min_or_max)
            ];
        }

        $formatted_prices = [];

        extract( [
            'ex_tax_label'       => false,
            'currency'           => '',
            'decimal_separator'  => wc_get_price_decimal_separator(),
            'thousand_separator' => wc_get_price_thousand_separator(),
            'decimals'           => wc_get_price_decimals(),
            'price_format'       => get_woocommerce_price_format()
        ] );

        foreach( $prices as $price_name => $price )
        {
            $negative        = $price < 0;
            $price           = apply_filters( 'raw_woocommerce_price', floatval( $negative ? $price * -1 : $price ) );
            $price           = apply_filters( 'formatted_woocommerce_price', number_format( $price, $decimals, $decimal_separator, $thousand_separator ), $price, $decimals, $decimal_separator, $thousand_separator );

            $formatted_price = ( $negative ? '-' : '' ) . sprintf( $price_format, get_woocommerce_currency_symbol( $currency ), $price );

            $formatted_prices[$price_name] = $formatted_price;
        }

        $result = [];

        foreach ( $return as $var )
        {
            $result[$var] = ${$var};
        }

        return $result;
    }
}
