<?php

/*
|--------------------------------------------------------------------------
| API: Options
|--------------------------------------------------------------------------
|
| /options/                         - Return all options
| /options/full/                    - Return all options with full settings (authenticated)
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

    // {api base url}/options/get_option/<option>
    register_rest_route( 'wyvern/v1', '/options/get_option/(?P<option>\S+)', [
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'wyvern_get_option',
        'args' => [
            'option'
        ],
    ] );

    // {api base url}/options/get_option/<option>
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

        // Filter options to return only safe to read options
        $returnable_options = array_filter($options, function($item) {
           if ((isset($item['private']) && !$item['private']) || !isset($item['private'])) {
               return true;
           }
           return false;
        });

        // Return only values to public API
        array_walk($returnable_options, function(&$item) {
            $item = $item['value'];
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

        // Check if option is available
        if (isset($options[$data['option']]) && isset($options[$data['option']]['value'])) {
            // @todo: check if option is private or public before returning it
            return apply_filters( 'wyvern_get_option', [$data['option'] => $options[$data['option']]['value']] );
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

        // Update options object
        $options[$data['option']] = [
            'value' => isset($_POST['value']) ? $_POST['value'] : null,
            'private' => isset($_POST['private']) ? (bool)$_POST['private'] : false,
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