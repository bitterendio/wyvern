<?php

/*
|--------------------------------------------------------------------------
| Theme support
|--------------------------------------------------------------------------
|
| title-tag: allows theme to put content in <title> tag
| menus: allows theme to have menus
|
*/

add_theme_support( 'title-tag' );
add_theme_support( 'menus' );
add_theme_support( 'post-thumbnails' );

/*
|--------------------------------------------------------------------------
| Styles and scripts
|--------------------------------------------------------------------------
|
| Register default styles and scripts here
|
*/

function rest_theme_scripts() {
    // Styles
    wp_enqueue_style( 'normalize', get_template_directory_uri() . '/assets/normalize.css', false, '3.0.3' );
    wp_enqueue_style( 'style', get_stylesheet_uri(), array( 'normalize' ) );

    // Pace
    wp_enqueue_script( 'pace', get_template_directory_uri() . '/assets/js/pace.min.js', array(), '1.0.0', true );

    // 3rd party scripts from default setup that will not be used
    wp_deregister_script('mailpoet_vendor');
    wp_deregister_script('mailpoet_public');
    wp_deregister_style('mailpoet_public');
    //wp_deregister_style('style');

    wp_enqueue_script( 'wyvern-vue', get_stylesheet_directory_uri() . '/lib/dist/build.js', array(), '1.0.0', true );

    $base_url  = esc_url_raw( home_url() );
    $base_path = rtrim( parse_url( $base_url, PHP_URL_PATH ), '/' );
    wp_localize_script( 'wyvern-vue', 'wp', array(
        'root'          => esc_url_raw( rest_url() ),
        'base_url'      => $base_url,
        'base_path'     => $base_path ? $base_path . '/' : '/',
        'nonce'         => wp_create_nonce( 'wp_rest' ),
        'site_name'     => get_bloginfo( 'name' ),
        'routes'        => rest_theme_routes(),
        'assets_path'   => get_stylesheet_directory_uri() . '/assets',
        'lang'          => get_language_strings(),

        // Configurations
        'keys'          => [
            'mapbox'    => env('MAPBOX_TOKEN'),
        ],

        // Inline configurations
        'show_on_front' => get_option('show_on_front'), // (posts|page) Settings -> Reading -> Front page displays
        'page_on_front' => get_option('page_on_front'), // (int) Settings -> Reading -> Front page displays when "page" is selected and type is "Front page"
        'page_for_posts'=> get_option('page_for_posts'), // (int) Settings -> Reading -> Front page displays when "page" is selected and type is "Posts page"

        // Display options
        'display'       => get_option ( 'wyvern_theme_display_options' ),

        // Social options
        'social'        => get_option ( 'wyvern_theme_social_options' ),

        // Tracking options
        'tracking'      => get_option ( 'wyvern_theme_tracking_options' ),

        // Extras options
        'extras'        => get_option ( 'wyvern_theme_extras_options' ),

    ) );
}

add_action( 'wp_enqueue_scripts', 'rest_theme_scripts' );

function get_language_strings() {
    return [
        'show_menu' => __('Ukázat menu', 'rest'),
        'search_placeholder' => __('Vyhledávat', 'rest'),
    ];
}

/*
|--------------------------------------------------------------------------
| Routes
|--------------------------------------------------------------------------
|
| Register theme routes here
|
*/

function rest_theme_routes() {
    $routes = array();

    $query = new WP_Query( array(
        'post_type'      => 'any',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
    ) );

    if ( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();
            $routes[] = array(
                'id'        => get_the_ID(),
                'type'      => get_post_type(),
                'slug'      => basename( get_permalink() ),
                'link'      => str_replace( site_url(), '', get_permalink() ),
                'template'  => get_page_template_slug()
            );
        }
    }

    wp_reset_postdata();

    return $routes;
}

// Hide admin bar until links in Edit page are eventually resolved
show_admin_bar(false);

/*
|--------------------------------------------------------------------------
| Menus
|--------------------------------------------------------------------------
|
| Register theme menu positions
|
*/

register_nav_menus( array(
    'primary'   => 'Primary Menu',
    'footer'    => 'Footer Menu',
    'mega'      => 'Mega Menu',
) );

/*
|--------------------------------------------------------------------------
| ACF
|--------------------------------------------------------------------------
|
| All ACF fields to rest api
|
*/

add_action( 'rest_api_init', 'slug_register_acf' );
function slug_register_acf() {
    $post_types = get_post_types(['public' => true], 'names');
    foreach ($post_types as $type) {
        register_api_field( $type,
            'acf',
            array(
                'get_callback'    => 'slug_get_acf',
                'update_callback' => null,
                'schema'          => null,
            )
        );
    }
}
function slug_get_acf( $object, $field_name, $request ) {
    if ( function_exists('get_fields') )
    {
        $fields = get_fields($object[ 'id' ]);
        return $fields !== false ? $fields : [];
    } else
    {
        return [];
    }
}

/*
|--------------------------------------------------------------------------
| Page templates
|--------------------------------------------------------------------------
|
| Register custom page templates without actually
| creating files.
|
*/

function get_virtual_templates() {
    return [
        'map' => 'Map template',
    ];
}

/**
 * Wordpress < 4.7
 */

function get_custom_page_templates() {
    $templates = get_virtual_templates();
    return apply_filters( 'custom_page_templates', $templates );
}

add_action( 'edit_form_after_editor', 'custom_page_templates_init' );
add_action( 'load-post.php', 'custom_page_templates_init_post' );
add_action( 'load-post-new.php', 'custom_page_templates_init_post' );

function custom_page_templates_init() {
    remove_action( current_filter(), __FUNCTION__ );
    if ( is_admin() && get_current_screen()->post_type === 'page' ) {
        $templates = get_custom_page_templates(); // the function above
        if ( ! empty( $templates ) )  {
            set_custom_page_templates( $templates );
        }
    }
}

function custom_page_templates_init_post() {
    remove_action( current_filter(), __FUNCTION__ );
    $method = filter_input( INPUT_SERVER, 'REQUEST_METHOD', FILTER_SANITIZE_STRING );
    if ( empty( $method ) || strtoupper( $method ) !== 'POST' ) return;
    if ( get_current_screen()->post_type === 'page' ) {
        custom_page_templates_init();
    }
}

function set_custom_page_templates( $templates = array() ) {
    if ( ! is_array( $templates ) || empty( $templates ) ) return;
    $core = array_flip( (array) get_page_templates() ); // templates defined by file
    $data = array_filter( array_merge( $core, $templates ) );
    ksort( $data );
    $stylesheet = get_stylesheet();
    $hash = md5( get_theme_root( $stylesheet ) . '/' . $stylesheet );
    $persistently = apply_filters( 'wp_cache_themes_persistently', false, 'WP_Theme' );
    $exp = is_int( $persistently ) ? $persistently : 1800;
    wp_cache_set( 'page_templates-' . $hash, $data, 'themes', $exp );
}

/**
 * Wordpress >= 4.7
 */
function makewp_exclude_page_templates( $post_templates ) {
    $templates = get_virtual_templates();

    if ( version_compare( $GLOBALS['wp_version'], '4.7', '>=' ) ) {
        foreach( $templates as $key => $value )
        {
            $post_templates[$key] = $value;
        }
    }

    return $post_templates;
}

add_filter( 'theme_page_templates', 'makewp_exclude_page_templates' );

/*
|--------------------------------------------------------------------------
| Sidebars
|--------------------------------------------------------------------------
|
| Register sidebars for widgets.
|
*/

register_sidebar([
    'name'          => __( 'Primary sidebar', 'rest' ),
    'id'            => 'primary-sidebar',
    'description'   => '',
    'class'         => '',
    'before_widget' => '<li id="%1$s" class="widget %2$s">',
    'after_widget'  => '</li>',
    'before_title'  => '<h2 class="widgettitle">',
    'after_title'   => '</h2>'
]);


/* If CPT has archive, register archive route */

$post_types = get_post_types();

foreach( $post_types as $post_type )
{

    $cpt = get_post_type_object($post_type);

    if ( $cpt->has_archive )
    {

        $routes[] = [
            'type'     => 'archive',
            'slug'     => $cpt->name,
            'link'     => get_post_type_archive_link($cpt->name),
            'template' => 'archive-' . $cpt->name,
        ];

    }

}

/**
 * Add REST API support to an already registered post type.
 */
add_action( 'init', 'my_custom_post_type_rest_support', 25 );
function my_custom_post_type_rest_support() {
    global $wp_post_types;

    $post_type_names = [
        'authors',
        'partner'
    ];

    foreach( $post_type_names as $post_type_name )
    {

        if ( isset($wp_post_types[ $post_type_name ]) )
        {
            $wp_post_types[ $post_type_name ]->show_in_rest = true;
            $wp_post_types[ $post_type_name ]->rest_base = $post_type_name;
            $wp_post_types[ $post_type_name ]->rest_controller_class = 'WP_REST_Posts_Controller';
        }

    }

}

/*
|--------------------------------------------------------------------------
| Open Graph
|--------------------------------------------------------------------------
|
| Setting Open Graph Tags
|
*/

class WyvernOpenGraph {

    public $data = [];

    public function __construct($post = null)
    {
        $this->title = get_the_title();
        $this->url = get_permalink();

        if ( $post )
            $this->post = $post;
    }

    public function __get($name)
    {
        if ( isset($this->data[$name]) )
            return $this->data[$name];

        $separator = '_';

        $getter_method_name = 'get' . str_replace( $separator, '', ucwords($name, $separator) ) . 'Attribute';

        if ( method_exists( $this, $getter_method_name ) )
            return $this->{$getter_method_name}();
    }

    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    public function getDescriptionAttribute()
    {
        if( $excerpt = $this->post->post_excerpt )
        {
            $excerpt = strip_tags($this->post->post_excerpt);
            $excerpt = str_replace("", "'", $excerpt);
        }
        else
        {
            $excerpt = get_bloginfo('description');
        }

        return $excerpt;
    }
}

function add_opengraph_doctype($output) {
    return $output . '
    xmlns="https://www.w3.org/1999/xhtml"
    xmlns:og="https://ogp.me/ns#" 
    xmlns:fb="http://www.facebook.com/2008/fbml"';
}
add_filter('language_attributes', 'add_opengraph_doctype');

//Add Open Graph Meta Info from the actual article data, or customize as necessary
function facebook_open_graph() {

    global $post;

    if ( !is_singular()) //if it is not a post or a page
        return;

    $og = new WyvernOpenGraph($post);

    //You'll need to find you Facebook profile Id and add it as the admin
    echo '<meta property="fb:admins" content="XXXXXXXXX-fb-admin-id"/>';
    echo '<meta property="og:title" content="' . $og->title . '"/>';
    echo '<meta property="og:description" content="' . $og->description . '"/>';
    echo '<meta property="og:type" content="article"/>';
    echo '<meta property="og:url" content="' . $og->url . '"/>';
    //Let's also add some Twitter related meta data
    echo '<meta name="twitter:card" content="summary" />';
    //This is the site Twitter @username to be used at the footer of the card
    echo '<meta name="twitter:site" content="@site_user_name" />';
    //This the Twitter @username which is the creator / author of the article
    echo '<meta name="twitter:creator" content="@username_author" />';

    // Customize the below with the name of your site
    echo '<meta property="og:site_name" content="Your Site NAME Goes HERE"/>';
    if(!has_post_thumbnail( $post->ID )) { //the post does not have featured image, use a default image
        //Create a default image on your server or an image in your media library, and insert it's URL here
        $default_image="http://example.com/image.jpg";
        echo '<meta property="og:image" content="' . $default_image . '"/>';
    }
    else{
        $thumbnail_src = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'medium' );
        echo '<meta property="og:image" content="' . esc_attr( $thumbnail_src[0] ) . '"/>';
    }

    echo "
	";
}
add_action( 'wp_head', 'facebook_open_graph', 5 );

/* Backend settings */

/* Fire our meta box setup function on the post editor screen. */
add_action( 'load-post.php', 'og_post_meta_boxes_setup' );
add_action( 'load-post-new.php', 'og_post_meta_boxes_setup' );

/* Create one or more meta boxes to be displayed on the post editor screen. */
function og_add_post_meta_boxes() {

    add_meta_box(
        'og-meta-tags',                  // Unique ID
        esc_html__( 'Open Graph Meta Tags', 'wyvern' ),  // Title
        'og_meta_tags_meta_box',         // Callback function
        'post',                                 // Admin page (or post type)
        'normal',                               // Context
        'low'                                   // Priority
    );
}

/* Display the post meta box. */
function og_meta_tags_meta_box( $object, $box ) {
    ?>

    <?php wp_nonce_field( basename( __FILE__ ), 'og_meta_tags_nonce' ); ?>

    <h4>Facebook</h4>
    <div class="wyvern-og-facebook-preview">
        <div class="og-image">
            <img src="">
        </div>
        <div class="og-content">
            <p class="og-title"><?php the_title() ?></p>
            <p class="og-site_name">http://www.amazon.co.uk/66665258965266/ref=tsm_1_fb_lk</p>
            <p class="og-description">This exceptional cups you can make it appear as if you were a true professional photographer. The original coffee mug that is a real camera lens looks deceptively real and the cover of the lens cup you can also use it as Keksschale. The styleish coffee cup as a digital camera lens will excite al...</p>
        </div>
    </div>

    <hr>

    <p>
        <label for="og-meta-tags"><?php _e( "Add a custom CSS class, which will be applied to WordPress' post class.", 'example' ); ?></label>
        <br />
        <input class="widefat" type="text" name="og-meta-tags" id="og-meta-tags" value="<?php echo esc_attr( get_post_meta( $object->ID, 'og_meta_tags', true ) ); ?>" size="30" />
    </p>
    <?php
}

/* Save post meta on the 'save_post' hook. */
add_action( 'save_post', 'og_save_meta_tags_meta', 10, 2 );

/* Meta box setup function. */
function og_post_meta_boxes_setup() {

    /* Add meta boxes on the 'add_meta_boxes' hook. */
    add_action( 'add_meta_boxes', 'og_add_post_meta_boxes' );

    /* Save post meta on the 'save_post' hook. */
    add_action( 'save_post', 'og_save_meta_tags_meta', 10, 2 );
}

/* Save the meta box's post metadata. */
function og_save_meta_tags_meta( $post_id, $post ) {

    /* Verify the nonce before proceeding. */
    if ( !isset( $_POST['og_meta_tags_nonce'] ) || !wp_verify_nonce( $_POST['og_meta_tags_nonce'], basename( __FILE__ ) ) )
        return $post_id;

    /* Get the post type object. */
    $post_type = get_post_type_object( $post->post_type );

    /* Check if the current user has permission to edit the post. */
    if ( !current_user_can( $post_type->cap->edit_post, $post_id ) )
        return $post_id;

    /* Get the posted data and sanitize it for use as an HTML class. */
    $new_meta_value = ( isset( $_POST['og-meta-tags'] ) ? sanitize_html_class( $_POST['og-meta-tags'] ) : '' );

    /* Get the meta key. */
    $meta_key = 'og_meta_tags';

    /* Get the meta value of the custom field key. */
    $meta_value = get_post_meta( $post_id, $meta_key, true );

    /* If a new meta value was added and there was no previous value, add it. */
    if ( $new_meta_value && '' == $meta_value )
        add_post_meta( $post_id, $meta_key, $new_meta_value, true );

    /* If the new meta value does not match the old value, update it. */
    elseif ( $new_meta_value && $new_meta_value != $meta_value )
        update_post_meta( $post_id, $meta_key, $new_meta_value );

    /* If there is no new meta value but an old value exists, delete it. */
    elseif ( '' == $new_meta_value && $meta_value )
        delete_post_meta( $post_id, $meta_key, $meta_value );
}

/*
|--------------------------------------------------------------------------
| Settings
|--------------------------------------------------------------------------
|
| Theme settings using Settings API.
|
| @usage    $display_options = get_option( 'wyvern_theme_display_options' )
|           $social_options = get_option ( 'wyvern_theme_social_options' )
|           $tracking_options = get_option ( 'wyvern_theme_tracking_options' )
|
*/

function wyvern_theme_menu() {

    add_theme_page(
        'Wyvern Theme',            // The title to be displayed in the browser window for this page.
        'Wyvern Theme',            // The text to be displayed for this menu item
        'administrator',            // Which type of users can see this menu item
        'wyvern_theme_options',    // The unique ID - that is, the slug - for this menu item
        'wyvern_theme_display'     // The name of the function to call when rendering this menu's page
    );

} // end wyvern_theme_menu
add_action( 'admin_menu', 'wyvern_theme_menu' );

/**
 * Renders a simple page to display for the theme menu defined above.
 */
function wyvern_theme_display() {
    ?>
    <!-- Create a header in the default WordPress 'wrap' container -->
    <div class="wrap">

        <div id="icon-themes" class="icon32"></div>
        <h2>Wyvern Theme Options</h2>
        <?php settings_errors(); ?>

        <?php $active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'display_options'; ?>

        <h2 class="nav-tab-wrapper">
            <a href="?page=wyvern_theme_options&tab=display_options" class="nav-tab <?php echo $active_tab == 'display_options' ? 'nav-tab-active' : ''; ?>">Display Options</a>
            <a href="?page=wyvern_theme_options&tab=social_options" class="nav-tab <?php echo $active_tab == 'social_options' ? 'nav-tab-active' : ''; ?>">Social Options</a>
            <a href="?page=wyvern_theme_options&tab=tracking_options" class="nav-tab <?php echo $active_tab == 'tracking_options' ? 'nav-tab-active' : ''; ?>">Tracking Options</a>
            <a href="?page=wyvern_theme_options&tab=extras_options" class="nav-tab <?php echo $active_tab == 'extras_options' ? 'nav-tab-active' : ''; ?>">Custom Extras</a>
        </h2>

        <form method="post" action="options.php">
            <?php

            if( $active_tab == 'display_options' ) {
                settings_fields( 'wyvern_theme_display_options' );
                do_settings_sections( 'wyvern_theme_display_options' );
            } else if( $active_tab == 'tracking_options' ) {
                settings_fields( 'wyvern_theme_tracking_options' );
                do_settings_sections( 'wyvern_theme_tracking_options' );
            } else if( $active_tab == 'extras_options' ) {
                settings_fields( 'wyvern_theme_extras_options' );
                do_settings_sections( 'wyvern_theme_extras_options' );
            } else {
                settings_fields( 'wyvern_theme_social_options' );
                do_settings_sections( 'wyvern_theme_social_options' );
            } // end if/else

            submit_button();

            ?>
        </form>

    </div><!-- /.wrap -->
    <?php
} // end wyvern_theme_display

function wyvern_initialize_theme_options() {

    // If the theme options don't exist, create them.
    if( false == get_option( 'wyvern_theme_display_options' ) ) {
        add_option( 'wyvern_theme_display_options' );
    } // end if

    // First, we register a section. This is necessary since all future options must belong to a
    add_settings_section(
        'general_settings_section',         // ID used to identify this section and with which to register options
        'Display Options',                  // Title to be displayed on the administration page
        'wyvern_general_options_callback', // Callback used to render the description of the section
        'wyvern_theme_display_options'     // Page on which to add this section of options
    );

    // Next, we'll introduce the fields for toggling the visibility of content elements.
    add_settings_field(
        'show_logo',                      // ID used to identify the field throughout the theme
        'Image logo',                     // The label to the left of the option interface element
        'wyvern_toggle_logo_callback',    // The name of the function responsible for rendering the option interface
        'wyvern_theme_display_options',   // The page on which this option will be displayed
        'general_settings_section',       // The name of the section to which this field belongs
        array(                            // The array of arguments to pass to the callback. In this case, just a description.
            'Display image logo, leave unchecked for site title.'
        )
    );

    add_settings_field(
        'show_megamenu',
        'Megamenu',
        'wyvern_toggle_megamenu_callback',
        'wyvern_theme_display_options',
        'general_settings_section',
        array(
            'Activate this setting to display the megamenu.'
        )
    );

    // Finally, we register the fields with WordPress
    register_setting(
        'wyvern_theme_display_options',
        'wyvern_theme_display_options'
    );

} // end wyvern_initialize_theme_options
add_action('admin_init', 'wyvern_initialize_theme_options');

function wyvern_general_options_callback() {
    echo '<p>Select which areas of megamenu you wish to display.</p>';
} // end wyvern_general_options_callback

function wyvern_toggle_logo_callback($args) {

    // First, we read the options collection
    $options = get_option('wyvern_theme_display_options');

    // Next, we update the name attribute to access this element's ID in the context of the display options array
    // We also access the show_logo element of the options collection in the call to the checked() helper function
    $html = '<input type="checkbox" id="show_logo" name="wyvern_theme_display_options[show_logo]" value="1" ' . checked( 1, isset( $options['show_logo'] ) ? $options['show_logo'] : 0, false ) . '/>';

    // Here, we'll take the first argument of the array and add it to a label next to the checkbox
    $html .= '<label for="show_logo"> '  . $args[0] . '</label>';

    echo $html;

} // end wyvern_toggle_logo_callback

function wyvern_toggle_megamenu_callback($args) {

    $options = get_option('wyvern_theme_display_options');

    $html = '<input type="checkbox" id="show_megamenu" name="wyvern_theme_display_options[show_megamenu]" value="1" ' . checked( 1, isset( $options['show_megamenu'] ) ? $options['show_megamenu'] : 0, false ) . '/>';
    $html .= '<label for="show_megamenu"> '  . $args[0] . '</label>';

    echo $html;

} // end wyvern_toggle_megamenu_callback

/**
 * Initializes the theme's social options by registering the Sections,
 * Fields, and Settings.
 *
 * This function is registered with the 'admin_init' hook.
 */
function wyvern_theme_intialize_social_options() {

    // If the social options don't exist, create them.
    if( false == get_option( 'wyvern_theme_social_options' ) ) {
        add_option( 'wyvern_theme_social_options' );
    } // end if

    add_settings_section(
        'social_settings_section',          // ID used to identify this section and with which to register options
        'Social Options',                   // Title to be displayed on the administration page
        'wyvern_social_options_callback',  // Callback used to render the description of the section
        'wyvern_theme_social_options'      // Page on which to add this section of options
    );

    add_settings_field(
        'twitter',
        'Twitter',
        'wyvern_twitter_callback',
        'wyvern_theme_social_options',
        'social_settings_section'
    );

    add_settings_field(
        'facebook',
        'Facebook',
        'wyvern_facebook_callback',
        'wyvern_theme_social_options',
        'social_settings_section'
    );

    add_settings_field(
        'googleplus',
        'Google+',
        'wyvern_googleplus_callback',
        'wyvern_theme_social_options',
        'social_settings_section'
    );

    register_setting(
        'wyvern_theme_social_options',
        'wyvern_theme_social_options',
        'wyvern_theme_sanitize_social_options'
    );

} // end wyvern_theme_intialize_social_options
add_action( 'admin_init', 'wyvern_theme_intialize_social_options' );

function wyvern_social_options_callback() {
    echo '<p>Provide the URL to the social networks you\'d like to display.</p>';
} // end wyvern_general_options_callback

function wyvern_twitter_callback() {

    // First, we read the social options collection
    $options = get_option( 'wyvern_theme_social_options' );

    // Next, we need to make sure the element is defined in the options. If not, we'll set an empty string.
    $url = '';
    if( isset( $options['twitter'] ) ) {
        $url = $options['twitter'];
    } // end if

    // Render the output
    echo '<input type="text" id="twitter" name="wyvern_theme_social_options[twitter]" value="' . $url . '" />';

} // end wyvern_twitter_callback

function wyvern_facebook_callback() {

    $options = get_option( 'wyvern_theme_social_options' );

    $url = '';
    if( isset( $options['facebook'] ) ) {
        $url = $options['facebook'];
    } // end if

    // Render the output
    echo '<input type="text" id="facebook" name="wyvern_theme_social_options[facebook]" value="' . $url . '" />';

} // end wyvern_facebook_callback

function wyvern_googleplus_callback() {

    $options = get_option( 'wyvern_theme_social_options' );

    $url = '';
    if( isset( $options['googleplus'] ) ) {
        $url = $options['googleplus'];
    } // end if

    // Render the output
    echo '<input type="text" id="googleplus" name="wyvern_theme_social_options[googleplus]" value="' . $url . '" />';

} // end wyvern_googleplus_callback

function wyvern_theme_sanitize_social_options( $input ) {

    // Define the array for the updated options
    $output = array();

    // Loop through each of the options sanitizing the data
    foreach( $input as $key => $val ) {

        if( isset ( $input[$key] ) ) {
            $output[$key] = esc_url_raw( strip_tags( stripslashes( $input[$key] ) ) );
        } // end if

    } // end foreach

    // Return the new collection
    return apply_filters( 'wyvern_theme_sanitize_social_options', $output, $input );

} // end wyvern_theme_sanitize_social_options

/**
 * Initializes the theme's tracking options by registering the Sections,
 * Fields, and Settings.
 *
 * This function is registered with the 'admin_init' hook.
 */
function wyvern_theme_intialize_tracking_options() {

    // If the tracking options don't exist, create them.
    if( false == get_option( 'wyvern_theme_tracking_options' ) ) {
        add_option( 'wyvern_theme_tracking_options' );
    } // end if

    add_settings_section(
        'tracking_settings_section',          // ID used to identify this section and with which to register options
        'Tracking Options',                   // Title to be displayed on the administration page
        'wyvern_tracking_options_callback',  // Callback used to render the description of the section
        'wyvern_theme_tracking_options'      // Page on which to add this section of options
    );

    add_settings_field(
        'google_analytics_id',
        'Google Analytics ID',
        'wyvern_google_analytics_id_callback',
        'wyvern_theme_tracking_options',
        'tracking_settings_section'
    );

    register_setting(
        'wyvern_theme_tracking_options',
        'wyvern_theme_tracking_options',
        'wyvern_theme_sanitize_tracking_options'
    );

} // end wyvern_theme_intialize_tracking_options
add_action( 'admin_init', 'wyvern_theme_intialize_tracking_options' );

function wyvern_tracking_options_callback() {
    echo '<p>Tracking IDs.</p>';
} // end wyvern_general_options_callback

function wyvern_google_analytics_id_callback() {

    $options = get_option( 'wyvern_theme_tracking_options' );

    $url = '';
    if( isset( $options['google_analytics_id'] ) ) {
        $url = $options['google_analytics_id'];
    } // end if

    // Render the output
    echo '<input type="text" id="google_analytics_id" name="wyvern_theme_tracking_options[google_analytics_id]" value="' . $url . '" />';

} // end wyvern_googleplus_callback

function wyvern_theme_sanitize_tracking_options( $input ) {

    // Define the array for the updated options
    $output = array();

// Loop through each of the options sanitizing the data
foreach( $input as $key => $val ) {

    if( isset ( $input[$key] ) ) {
        $output[$key] = strip_tags( stripslashes( $input[$key] ) );
    } // end if

} // end foreach

// Return the new collection
return apply_filters( 'wyvern_theme_sanitize_tracking_options', $output, $input );

} // end wyvern_theme_sanitize_tracking_options

/**
 * Initializes the theme's extras options by registering the Sections,
 * Fields, and Settings.
 *
 * This function is registered with the 'admin_init' hook.
 */
function wyvern_theme_intialize_extras_options() {

    // If the extras options don't exist, create them.
    if( false == get_option( 'wyvern_theme_extras_options' ) ) {
        add_option( 'wyvern_theme_extras_options' );
    } // end if

    add_settings_section(
        'extras_settings_section',          // ID used to identify this section and with which to register options
        'Custom Extras',                    // Title to be displayed on the administration page
        'wyvern_extras_options_callback',  // Callback used to render the description of the section
        'wyvern_theme_extras_options'      // Page on which to add this section of options
    );

    add_settings_field(
        'custom_header_html',
        'Custom header HTML',
        'wyvern_custom_header_html_callback',
        'wyvern_theme_extras_options',
        'extras_settings_section'
    );

    add_settings_field(
        'footer_text',
        'Footer text',
        'wyvern_footer_text_callback',
        'wyvern_theme_extras_options',
        'extras_settings_section'
    );

    add_settings_field(
        'mapbox_style',
        'Mapbox style',
        'wyvern_mapbox_style_callback',
        'wyvern_theme_extras_options',
        'extras_settings_section'
    );

    register_setting(
        'wyvern_theme_extras_options',
        'wyvern_theme_extras_options',
        'wyvern_theme_sanitize_extras_options'
    );

} // end wyvern_theme_intialize_extras_options
add_action( 'admin_init', 'wyvern_theme_intialize_extras_options' );

function wyvern_extras_options_callback() {
    echo '<p>Custom settings and code.</p>';
} // end wyvern_general_options_callback

function wyvern_custom_header_html_callback() {

    $options = get_option( 'wyvern_theme_extras_options' );

    $custom_header_html = '';
    if( isset( $options['custom_header_html'] ) ) {
        $custom_header_html = $options['custom_header_html'];
    } // end if

    // Render the output
    echo '<textarea id="custom_header_html" name="wyvern_theme_extras_options[custom_header_html]" rows="8" cols="40">' . $custom_header_html . '</textarea>';

} // end custom_header_html_callback

function wyvern_footer_text_callback() {

    $options = get_option( 'wyvern_theme_extras_options' );

    $footer_text = '';
    if( isset( $options['footer_text'] ) ) {
        $footer_text = $options['footer_text'];
    } // end if

    // Render the output
    echo '<textarea id="footer_text" name="wyvern_theme_extras_options[footer_text]" rows="8" cols="40">' . $footer_text . '</textarea>';

} // end custom_header_html_callback

function wyvern_mapbox_style_callback() {

    $options = get_option( 'wyvern_theme_extras_options' );

    $mapbox_style = '';
    if( isset( $options['mapbox_style'] ) ) {
        $mapbox_style = $options['mapbox_style'];
    } // end if

    // Render the output
    echo '<textarea id="mapbox_style" name="wyvern_theme_extras_options[mapbox_style]" rows="8" cols="40">' . $mapbox_style . '</textarea>';

} // end custom_header_html_callback

function wyvern_theme_sanitize_extras_options( $input ) {

    // Define the array for the updated options
    $output = array();

    // Loop through each of the options sanitizing the data
    foreach( $input as $key => $val ) {

        if( isset ( $input[$key] ) ) {
            $output[$key] = $input[$key];
        } // end if

    } // end foreach

    // Return the new collection
    return apply_filters( 'wyvern_theme_sanitize_extras_options', $output, $input );

} // end wyvern_theme_sanitize_extras_options