<?php

function wyvern_theme_localization(){
    load_theme_textdomain( wp_get_theme()->get('TextDomain'), get_stylesheet_directory() . '/languages' );
}

add_action('after_setup_theme', 'wyvern_theme_localization');

function wyvern_theme_localization_strings() {
    $lang = [
        'add_to_cart' => __('Add to cart', 'wyvern-shop'),
        'empty_cart' => __('Empty cart', 'wyvern-shop'),
        'account_general' => __('Account: General', 'wyvern-shop'),
        'account_orders' => __('Account: Orders', 'wyvern-shop'),
        'first_name' => __('First name', 'wyvern-shop'),
        'last_name' => __('Last name', 'wyvern-shop'),
        'phone' => __('Phone', 'wyvern-shop'),
        'email' => __('E-mail', 'wyvern-shop'),
        'address' => __('Address', 'wyvern-shop'),
        'password' => __('Password', 'wyvern-shop'),
        'billing_address' => __('Billing address', 'wyvern-shop'),
        'shipping_address' => __('Shipping address', 'wyvern-shop'),
        'notes' => __('Notes', 'wyvern-shop'),
        'cancel_order' => __('Cancel order', 'wyvern-shop'),
        'account' => __('Account', 'wyvern-shop'),
        'logout' => __('Logout', 'wyvern-shop'),
        'sign_in' => __('Sign in', 'wyvern-shop'),
        'sign_up' => __('Sign up', 'wyvern-shop'),
        'sign_in_with' => __('Sign in with', 'wyvern-shop'),
        'sign_up_with' => __('Sign up with', 'wyvern-shop'),
        'or' => __('or', 'wyvern-shop'),
        'username_or_email' => __('Username or E-mail', 'wyvern-shop'),
        'remember_me' => __('Remember me', 'wyvern-shop'),
        'forgot_password' => __('Forgot Password', 'wyvern-shop'),
        'reset_password' => __('Reset password', 'wyvern-shop'),
        'create_account' => __('Create Account', 'wyvern-shop'),
        'remove_from_cart' => __('Remove from cart', 'wyvern-shop'),
        'subtotal' => __('Subtotal', 'wyvern-shop'),
        'shipping_total' => __('Shipping total', 'wyvern-shop'),
        'total' => __('Total', 'wyvern-shop'),
        'first_name_and_last_name' => __('First name and Last name', 'wyvern-shop'),
        'note' => __('Note', 'wyvern-shop'),
        'ship_to_different_address' => __('Ship to different address', 'wyvern-shop'),
        'choose_payment' => __('Choose payment', 'wyvern-shop'),
        'choose_shipping' => __('Choose shipping', 'wyvern-shop'),
        'place_order' => __('Place order', 'wyvern-shop'),
        'search' => __('Search', 'wyvern-shop'),
        'back_to_catalog' => __('Back to catalog', 'wyvern-shop'),
        'all' => __('All', 'wyvern-shop'),
        'account_number' => __('Account number', 'wyvern-shop'),
        'payment_id' => __('Payment ID', 'wyvern-shop'),
        'bank_name' => __('Bank name', 'wyvern-shop'),
        'iban' => __('IBAN', 'wyvern-shop'),
        'bic' => __('Swift', 'wyvern-shop'),
        'local_pickup_place' => __('Place for local pickup', 'wyvern-shop'),
        'go_to_admin' => __('Go to Admin', 'wyvern-shop'),

        'update' => __('Update', 'wyvern-shop'),
    ];
    echo '<script type="text/javascript">window.lang = ' . json_encode($lang) . ';</script>';
}
add_action( 'wp_footer', 'wyvern_theme_localization_strings', 1 );