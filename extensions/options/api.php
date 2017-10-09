<?php

/*
|--------------------------------------------------------------------------
| API: Options
|--------------------------------------------------------------------------
|
| /options/                         - Return all options
| /options/full/                    - Return all options with full settings (authenticated)
| /options/update/                  - Update options
| /options/get_option/<option>      - Get option value by it's slug
| /options/update_option/<option>   - Update option
|   [POST] PARAMS:
|       - value - Option value
|       - private - true = Option not available for public API
|
*/

add_action( 'rest_api_init', function () {
    // {api base url}/options/
    register_rest_route( 'wyvern/v1', '/options/', [
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'wyvern_get_options',
        'args' => [
        ],
    ] );

    // {api base url}/options/full/
    register_rest_route( 'wyvern/v1', '/options/full/', [
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'wyvern_get_options_full',
        'args' => [
        ],
    ] );

    // {api base url}/options/update/
    register_rest_route( 'wyvern/v1', '/options/update/', [
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => 'wyvern_get_options_update',
        'args' => [
        ],
    ] );

    // {api base url}/options/get_option/<option>
    register_rest_route( 'wyvern/v1', '/options/get_option/(?P<option>\S+)', [
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'wyvern_get_option',
        'args' => [
            'option'
        ],
    ] );

    // {api base url}/options/update_option/<option>
    register_rest_route( 'wyvern/v1', '/options/update_option/(?P<option>\S+)', [
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => 'wyvern_update_option',
        'args' => [
            'option'
        ],
    ] );
} );

if ( !function_exists('wyvern_get_options') )
{
    /**
     * Get all public theme options
     *
     * @param $data
     * @return array
     */
    function wyvern_get_options($data)
    {
        // Get all available theme options
        // - for security reasons, we shall not provide access to general Wordpress options
        $options = get_option('wyvern_options');

        if (!$options) {
            return ['msg' => __('No options')];
        }

        // Filter options to return only safe to read options
        $returnable_options = array_filter($options, function($item) {
            if ((isset($item['private']) && !$item['private']) || !isset($item['private'])) {
                return true;
            }
            return false;
        });

        // Return options
        return apply_filters( 'wyvern_get_options', $returnable_options );
    }
}

if ( !function_exists('wyvern_get_options_full') )
{
    /**
     * Get all public theme options
     *
     * @param $data
     * @return array
     */
    function wyvern_get_options_full($data)
    {
        // Check if user can get the list of options
        // @todo: make check this available through WP nonce
        //if ( !current_user_can('manage_options') )
        //    return ['msg' => __('Your current user capabilities are not sufficient to return this data')];

        // Get all available theme options
        // - for security reasons, we shall not provide access to general Wordpress options
        $options = get_option('wyvern_options');

        // Return options
        return apply_filters( 'wyvern_get_options_full', $options );
    }
}

if ( !function_exists('wyvern_get_options_update') )
{
    /**
     * Update all options
     *
     * @param $data
     * @return array
     */
    function wyvern_get_options_update($data)
    {
        $input = $_POST['options'];
        $options = [];

        foreach($input as $key => $value) {
            $value['private'] = $value['private'] === 'true' ? true : false;
            $options[$key] = $value;
        }

        // Update options
        update_option('wyvern_options', $options);

        // Return options
        return wyvern_get_options_full([]);
    }
}

if ( !function_exists('wyvern_get_option') )
{
    /**
     * Get theme options by name
     *
     * @param $data
     * @return array
     */
    function wyvern_get_option($data)
    {
        // Check if option name was specified
        if ( !isset($data['option']) )
            return ['msg' => __('Option name was not specified')];

        // Get all available theme options
        // - for security reasons, we shall not provide access to general Wordpress options
        $options = get_option('wyvern_options');

        $key = array_search($data['option'], array_column($options, 'slug'));

        // Check if option is available
        /* if (isset($options[$data['option']]) && isset($options[$data['option']]['value'])) {
            // @todo: check if option is private or public before returning it
            return apply_filters( 'wyvern_get_option', [$data['option'] => $options[$data['option']]['value']] );
        } */

        if ($key !== false) {
            return apply_filters('wyvern_get_option', $options[$key]);
        }

        return ['msg' => __('Option was not found')];
    }
}

if ( !function_exists('wyvern_update_option') )
{
    /**
     * Update theme options
     *
     * @param $data
     * @return array
     */
    function wyvern_update_option($data)
    {
        // Check if option name was specified
        if ( !isset($data['option']) )
            return ['msg' => __('Option name was not specified')];

        // Get all available theme options
        // - for security reasons, we shall not provide access to general Wordpress options
        $options = get_option('wyvern_options');

        $key = array_search($data['option'], array_column($options, 'slug'));

        // Update options object
        $options[$key] = [
            'name'  => isset($_POST['name']) ? $_POST['name'] : $options[$key]['name'],
            'slug'  => isset($_POST['slug']) ? $_POST['slug'] : $options[$key]['slug'],
            'value' => isset($_POST['value']) ? $_POST['value'] : $options[$key]['value'],
            'private' => isset($_POST['private']) ? (bool)$_POST['private'] : $options[$key]['private'],
        ];

        // Update theme options object
        $result = update_option('wyvern_options', $options, true);

        if ($result)
        {
            return ['msg' => __('Option was succesfully updated')];
        }

        return ['msg' => __('Option could not be updated')];
    }
}