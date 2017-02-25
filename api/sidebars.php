<?php
/**
 * Sidebar endpoints
 */

add_action( 'rest_api_init', function () {
    register_rest_route( 'api', '/sidebars/', [
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'wyvern_get_sidebars',
        'args' => [
        ],
    ] );
} );

add_action( 'rest_api_init', function () {
    register_rest_route( 'api', '/sidebar/(?P<id>\d+)', [
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'wyvern_get_sidebar',
        'args' => [
            'id'
        ],
    ] );
} );

if (!function_exists('wyvern_get_sidebars'))
{
    function wyvern_get_sidebars()
    {
        if (isset($GLOBALS['wp_registered_sidebars']))
        {
            $sidebars = $GLOBALS['wp_registered_sidebars'];
            $output = [];

            foreach($sidebars as $key => $sidebar)
            {
                $output[$key] = wyvern_get_sidebar($sidebar['id']);
            }

            return $output;
        }

        return [];
    }
}

if(!function_exists('wyvern_get_rendered_sidebar'))
{
    function wyvern_get_rendered_sidebar($sidebar)
    {
        $sidebar_id = $sidebar;
        if (is_array($sidebar))
        {
            if (isset($sidebar['id']))
            {
                $sidebar_id = $sidebar['id'];
            }
        }

        if (is_dynamic_sidebar($sidebar_id))
        {
            ob_start();
            dynamic_sidebar($sidebar_id);
            $rendered = ob_get_contents();
            ob_end_clean();
            return $rendered;
        }
        return null;
    }
}

if(!function_exists('wyvern_get_widgets_by_sidebar'))
{
    function wyvern_get_widgets_by_sidebar($sidebar)
    {
        $sidebar_id = $sidebar;

        if (is_array($sidebar))
        {
            if (isset($sidebar['id'])) {
                $sidebar_id = $sidebar['id'];
            }
        }

        $active_widgets = get_option( 'sidebars_widgets' );

        if (isset($active_widgets[$sidebar_id]))
        {
            $widgets = $active_widgets[$sidebar_id];

            foreach($widgets as $key => $widget_id)
            {
                $widgets[$key] = [
                    'id' => $widget_id,
                    'rendered' => wyvern_get_rendered_widget_by_id($widget_id, $sidebar_id),
                ];
            }

            return $widgets;
        }

        return [];
    }
}

if (!function_exists('wyvern_get_rendered_widget_by_id'))
{
    function wyvern_get_rendered_widget_by_id($widget_id, $sidebar_id)
    {
        $widgets = $GLOBALS['wp_registered_widgets'];
        $sidebars = $GLOBALS['wp_registered_sidebars'];

        if (isset($widgets[$widget_id]) && isset($sidebars[$sidebar_id]))
        {
            $instance = get_option($widgets[$widget_id]['classname']);
            $sidebar = $sidebars[$sidebar_id];

            if (!is_array($instance))
            {
                $instance = [];
            }

            $params = array_merge(
                array( array_merge( $sidebar, array('widget_id' => $widget_id, 'widget_name' => $widgets[$widget_id]['name']) ) ),
                (array) $widgets[$widget_id]['params']
            );

            ob_start();
            call_user_func_array($widgets[$widget_id]['callback'], $params);
            $rendered = ob_get_contents();
            ob_end_clean();

            return $rendered;
        }

        return null;
    }
}

if (!function_exists('wyvern_get_sidebar'))
{
    function wyvern_get_sidebar($id)
    {
        $sidebars = $GLOBALS['wp_registered_sidebars'];

        if (!isset($sidebars[$id]))
            return null;

        $sidebar = $sidebars[$id];

        $sidebar['rendered'] = wyvern_get_rendered_sidebar($id);
        $sidebar['widgets'] = wyvern_get_widgets_by_sidebar($id);

        return $sidebar;
    }
}

