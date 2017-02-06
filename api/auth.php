<?php
/**
 * REST API New call - Login
 */
add_action( 'rest_api_init', function () {
    register_rest_route('api', '/login/', array(
        'methods' => WP_REST_SERVER::ALLMETHODS,
        'callback' => 'custom_login',
        'args' => [
            'user_login',
            'user_password',
            'remember',
        ],
    ) );
} );

add_action( 'rest_api_init', function () {
    register_rest_route('api', '/logout/', array(
        'methods' => WP_REST_SERVER::ALLMETHODS,
        'callback' => 'custom_logout',
        'args' => [],
    ) );
} );

add_action( 'rest_api_init', function () {
    register_rest_route('api', '/register/', array(
        'methods' => WP_REST_SERVER::EDITABLE,
        'callback' => 'custom_register',
        'args' => [
            'email',
            'first_name',
            'last_name',
            'password',
            'country',
        ],
    ) );
} );

add_action( 'rest_api_init', function () {
    register_rest_route('api', '/social/(?P<provider>\S+)', array(
        'methods' => WP_REST_SERVER::ALLMETHODS,
        'callback' => 'wyvern_get_social_login_url_by_provider',
        'args' => [
            'provider'
        ],
    ) );
} );

add_action( 'rest_api_init', function () {
    register_rest_route('api', '/socials/', array(
        'methods' => WP_REST_SERVER::ALLMETHODS,
        'callback' => 'wyvern_get_social_login_urls',
        'args' => [],
    ) );
} );

add_action( 'rest_api_init', function () {
    register_rest_route('api', '/request-password/', array(
        'methods' => WP_REST_SERVER::READABLE,
        'callback' => 'wyvern_request_password',
        'args' => [],
    ) );
} );


if ( !function_exists('custom_login') )
{
    function custom_login($data)
    {
        $credentials = [
            'user_login'    => $data['user_login'],
            'user_password' => $data['user_password'],
            'remember'      => $data['remember'],
        ];
        $response = [];
        $login = wp_signon($credentials);
        if ( is_wp_error($login) )
        {
            $response = [
                'status' => 'error',
                'data'   => $login,
            ];
        } else
        {
            $response = [
                'status' => 'success',
                'data'   => $login,
            ];
        }

        return $response;
    }
}

if ( !function_exists('custom_logout') )
{
    function custom_logout()
    {
        wp_logout();

        return [
            'success' => get_current_user_id() < 1
        ];
    }
}

if ( !function_exists('custom_register') )
{
    function custom_register($data)
    {
        $userdata = [
            'user_login'    =>      $data['email'],
            'user_email'    =>      $data['email'],
            'user_pass'     =>      $data['password'],
            'first_name'    =>      $data['first_name'],
            'last_name'     =>      $data['last_name']
        ];

        $response = [];
        // Check that user doesn't already exist
        if ( !username_exists($userdata['user_login']) && !email_exists($userdata['user_email']) )
        {
            // Create user and set role to administrator
            $user_id = wp_insert_user($userdata);

            if ( is_int($user_id) )
            {
                // Login user
                $credentials = [
                    'user_login'    => $userdata['user_login'],
                    'user_password' => $userdata['user_pass'],
                    'remember'      => true
                ];

                wp_new_user_notification($user_id, null, 'both');

                if ( isset($userdata['first_name']) )
                    update_user_meta($user_id, 'billing_first_name', $userdata['first_name']);

                if ( isset($userdata['last_name']) )
                    update_user_meta($user_id, 'billing_last_name', $userdata['last_name']);

                if ( isset($userdata['user_email']) )
                    update_user_meta($user_id, 'billing_email', $userdata['user_email']);

                wp_signon($credentials);

                $response = [
                    'user_id' => $user_id
                ];
            } else {
                $response = [
                    'status'    => 'error',
                    'text'      => 'Error!',
                ];
            }

        } else {
            $response = [
                'status' => 'error',
                'text'   => 'This user or email already exists. Nothing was done.',
            ];
        }

        return $response;
    }
}

if ( !function_exists('wyvern_get_social_login_url_by_provider') )
{
    function wyvern_get_social_login_url_by_provider($data)
    {
        if ( !isset($data['provider']) )
            return [];

        if ( isset($data['provider']) )
            $provider = $data['provider'];

        return wyvern_social_login_url($provider);
    }
}

if ( !function_exists('wyvern_social_login_url') )
{
    function wyvern_social_login_url($provider)
    {
        $oauth = Sane_Oauth::getInstance();

        return [
            $oauth->get_url($provider)
        ];
    }
}

if ( !function_exists('wyvern_get_social_login_urls') )
{
    function wyvern_get_social_login_urls()
    {
        return [
            'facebook' => wyvern_social_login_url('facebook'),
            'google' => wyvern_social_login_url('google'),
        ];
    }
}

if ( !function_exists('wyvern_request_password') )
{
    function wyvern_request_password()
    {
        if ( !isset($_GET['user_email']) )
            return ['success' => false];

        $user_login = $_GET['user_email'];

        // @todo: cleanup functionality below
        // @credits: http://wordpress.stackexchange.com/questions/60318/sending-the-reset-password-link-programatically
        global $wpdb, $wp_hasher;
        $user_login = sanitize_text_field($user_login);
        if ( empty( $user_login) ) {
            return ['success' => false];
        } else if ( strpos( $user_login, '@' ) ) {
            $user_data = get_user_by( 'email', trim( $user_login ) );
            if ( empty( $user_data ) )
                return ['success' => false];
        } else {
            $login = trim($user_login);
            $user_data = get_user_by('login', $login);
        }

        do_action('lostpassword_post');
        if ( !$user_data ) return false;
        // redefining user_login ensures we return the right case in the email
        $user_login = $user_data->user_login;
        $user_email = $user_data->user_email;
        do_action('retreive_password', $user_login);  // Misspelled and deprecated
        do_action('retrieve_password', $user_login);
        $allow = apply_filters('allow_password_reset', true, $user_data->ID);
        if ( ! $allow )
            return ['success' => false];
        else if ( is_wp_error($allow) )
            return ['success' => false];
        $key = wp_generate_password( 20, false );
        do_action( 'retrieve_password_key', $user_login, $key );

        if ( empty( $wp_hasher ) ) {
            require_once ABSPATH . 'wp-includes/class-phpass.php';
            $wp_hasher = new PasswordHash( 8, true );
        }
        $hashed = $wp_hasher->HashPassword( $key );
        $wpdb->update( $wpdb->users, array( 'user_activation_key' => time().":".$hashed ), array( 'user_login' => $user_login ) );
        $message = __('Someone requested that the password be reset for the following account:') . "\r\n\r\n";
        $message .= network_home_url( '/' ) . "\r\n\r\n";
        $message .= sprintf(__('Username: %s'), $user_login) . "\r\n\r\n";
        $message .= __('If this was a mistake, just ignore this email and nothing will happen.') . "\r\n\r\n";
        $message .= __('To reset your password, visit the following address:') . "\r\n\r\n";
        $message .= network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login), 'login') . "\r\n";

        if ( is_multisite() )
            $blogname = $GLOBALS['current_site']->site_name;
        else
            $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

        $title = sprintf( __('[%s] Password Reset'), $blogname );

        $title = apply_filters('retrieve_password_title', $title);
        $message = apply_filters('retrieve_password_message', $message, $key);

        if ( $message && !wp_mail($user_email, $title, $message) )
            wp_die( __('The e-mail could not be sent.') . "<br />\n" . __('Possible reason: your host may have disabled the mail() function...') );

        return [
            'success' => true
        ];
    }
}



if ( strpos($_SERVER['REQUEST_URI'], '/oauth/endpoint') === 0 )
{
    $oauth = new Sane_Oauth();
    $oauth->resolve();
}
