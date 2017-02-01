<?php
/**
 * Products endpoint
 */

add_action( 'rest_api_init', function () {
    register_rest_route( 'api', '/products/(?P<id>\d+)', [
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'wyvern_wc_get_products',
        'args' => [
            'id'
        ],
    ] );
} );

add_action( 'rest_api_init', function () {
    register_rest_route( 'api', '/products/', [
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
    function wyvern_wc_get_products_index( $posts_per_page = 100 )
    {
        $results = [];

        $args = [
            'post_type' => 'product',
            'posts_per_page' => $posts_per_page
        ];

        if ( isset($_GET['filters']) )
        {
            $filters = $_GET['filters'];
            $prepared = [];

            foreach( $filters as $name => $filter )
            {
                if ( !empty($filter) )
                {
                    $prepared[] = [
                        'taxonomy' => wc_attribute_taxonomy_name($name),
                        'field' => 'term_id',
                        'terms' => $filter,
                        'operator' => 'IN'
                    ];
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

        return $results;
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
        'available_variations',
        'default_attributes_variation',
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

                    $attributes[$attribute->attribute_name][] = $value;
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
        $available_variations = [];

        if ( method_exists($product, 'get_available_variations') )
            $available_variations = $product->get_available_variations();

        // Default variations
        $default_attributes_variation = [];

        foreach( $attributes as $attribute_key => $attribute_values )
        {
            $default_attributes_variation[$attribute_key] = $product->get_variation_default_attribute( wc_attribute_taxonomy_name($attribute_key) );
        }

        // Product prices
        if ( empty($available_variations) )
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