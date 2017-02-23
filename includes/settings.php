<?php namespace Wyvern\Includes;

/**
 * Wyvern Settings
 *
 * This is used to define, hold reference to and print out available settings.
 *
 * @since      1.0.0
 * @package    Wyvern
 * @subpackage Wyvern/includes
 * @author     sane <in@sane.ninja>
 */
class Settings {

    /**
     * @var array Settings
     */
    public $settings = [];

    /**
     * @var array Setting sections
     */
    public $sections = [
        [
            'id' => 'wyvern_general_settings',
            'title' => 'General',
            'callback' => null,
            'page' => 'wyvern_theme_options_general',
        ]
    ];

    /**
     * @var Singleton The reference to *Singleton* instance of this class
     */
    private static $instance;

    /**
     * @var string The title to be displayed in the browser window for this page.
     */
    public $page_title = 'Wyvern Theme';

    /**
     * @var string The text to be displayed for this menu item
     */
    public $menu_title = 'Wyvern Theme';

    /**
     * @var string Which type of users can see this menu item
     */
    public $capability = 'administrator';

    /**
     * @var string The unique ID - that is, the slug - for this menu item
     */
    public $menu_slug = 'wyvern_theme_options';

    public function __construct($page_title = 'Wyvern Theme', $menu_title = 'Wyvern Theme', $capability = 'administrator', $menu_slug = 'wyvern_theme_options')
    {
        $this->page_title = $page_title;
        $this->menu_title = $menu_title;
        $this->capability = $capability;
        $this->menu_slug = $menu_slug;

        add_action( 'admin_menu', [$this, 'admin_menu'] );
        add_action( 'admin_init', [$this, 'prepare'] );
        add_action( 'wp_footer', [$this, 'output'], 1 );
    }

    public function admin_menu()
    {
        add_theme_page(
            $this->page_title,
            $this->menu_title,
            $this->capability,
            $this->menu_slug,
            [$this, 'display']
        );
    }

    public function prepare()
    {
        foreach( $this->sections as $key => $section )
        {
            if( false == get_option( $section['page'] ) ) {
                add_option( $section['page'] );
            }

            if ( empty($section['callback']) )
                $section['callback'] = [$this, 'section_callback'];

            add_settings_section(
                $section['id'],
                $section['title'],
                $section['callback'],
                $section['page']
            );

            register_setting(
                $section['page'],
                $section['page'],
                [$this, 'sanitize_callback']
            );
        }

        foreach( $this->settings as $key => $setting )
        {
            add_settings_field(
                $setting['slug'],
                $setting['name'],
                [$this, 'render_field'],
                $setting['page'],
                $setting['section'],
                $setting
            );
        }

    }

    /**
     * Renders a simple page to display for the theme menu defined above.
     */
    public function display()
    {
        ?>
        <!-- Create a header in the default WordPress 'wrap' container -->
        <div class="wrap">

            <div id="icon-themes" class="icon32"></div>
            <h2><?php echo $this->page_title ?></h2>
            <?php settings_errors(); ?>

            <?php $active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'wyvern_general_settings'; ?>

            <h2 class="nav-tab-wrapper">
                <?php foreach( $this->sections as $key => $section ) : ?>
                    <a href="?page=<?php echo $this->menu_slug ?>&tab=<?php echo $section['id'] ?>" class="nav-tab <?php echo $active_tab == $section['id'] ? 'nav-tab-active' : ''; ?>"><?php echo $section['title'] ?></a>
                <?php endforeach; ?>
            </h2>

            <form method="post" action="options.php">
                <?php foreach( $this->sections as $key => $section ) :
                    if( $active_tab == $section['id'] ) {
                        settings_fields( $section['page'] );
                        do_settings_sections( $section['page'] );
                    }
                endforeach; ?>
                <?php submit_button(); ?>
            </form>

        </div><!-- /.wrap -->
        <?php
    }

    /**
     * Returns the *Singleton* instance of this class.
     *
     * @return Singleton The *Singleton* instance.
     */
    public static function getInstance()
    {
        if (null === static::$instance) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    public static function add($name = null, $slug = null, $type = 'input', $default = null, $section = 'wyvern_general_settings', $page = 'wyvern_theme_options_general')
    {
        $settings = self::getInstance();

        $settings->addSetting($name, $slug, $type, $default, $section, $page);
    }

    public static function section($id, $title, $callback, $page)
    {
        $settings = self::getInstance();

        $settings->addSection($id, $title, $callback, $page);
    }

    public function addSetting($name = null, $slug = null, $type = 'input', $default = null, $section = 'wyvern_general_settings', $page = 'wyvern_theme_options_general')
    {
        $this->settings[] = compact('name', 'slug', 'type', 'default', 'section', 'page');
    }

    /**
     * @param $id ID used to identify this section and with which to register options
     * @param $title Title to be displayed on the administration page
     * @param $callback Callback used to render the description of the section
     * @param $page Page on which to add this section of options
     */
    public function addSection($id, $title, $callback, $page)
    {
        if ( empty($callback) )
            $callback = [$this, 'sanitize_callback'];

        $this->sections[] = compact('id', 'title', 'callback', 'page');
    }

    /* @TODO checkbox default value checked */
    public function render_field($args)
    {
        // First, we read the options collection
        $options = get_option($args['page']);

        if ( isset($options[$args['slug']]) )
            $value = $options[$args['slug']];
        else
            $value = $args['default'];

        $name = $args['page'].'['.$args['slug'].']';

        // Render the output
        switch ( $args['type'] )
        {
            case 'checkbox':
                echo '<label for="show_logo"><input type="checkbox" id="' . $args['slug'] . '" name="' . $name . '" value="1" ' . checked( 1, isset( $options[$args['slug']] ) ? $options[$args['slug']] : 0, false ) . '></label>';
                break;

            case 'textarea':
                echo '<textarea id="' . $args['slug'] . '" name="' . $name . '" rows="8" cols="40">' . $value . '</textarea>';
                break;

            case 'input':
            default:
                echo '<input type="text" id="' . $args['slug'] . '" name="' . $name . '" value="' . $value . '">';
                break;

            case 'number':
                echo '<input type="number" id="' . $args['slug'] . '" name="' . $name . '" value="' . $value . '">';
                break;
        }
    }

    public function section_callback( $args )
    {
        // $args contains - 'id', 'title', 'callback'
        // this is good place for custom code
    }

    public function sanitize_callback( $input )
    {
        // Define the array for the updated options
        $output = [];

        // Loop through each of the options sanitizing the data
        foreach( $input as $key => $val ) {

            if( isset ( $input[$key] ) ) {
                $output[$key] = $input[$key];
            } // end if

        } // end foreach

        // Return the new collection
        return apply_filters( 'wyvern_settings_sanitize_callback', $output, $input );
    }

    public static function after_setup_theme()
    {
        $settings = self::getInstance();
        $settings->prepare();
    }

    public function getStructuredValues()
    {
        $buffer = [];
        $output = [];

        foreach( $this->settings as $setting )
        {
            if ( !isset($buffer[$setting['page']]) )
                $buffer[$setting['page']] = get_option($setting['page']);

            if ( !isset($output[$setting['page']]) )
                $output[$setting['page']] = [];

            // Set default value if no value is set
            if ( !isset($buffer[$setting['page']][$setting['slug']]) && isset($setting['default']) )
                $buffer[$setting['page']][$setting['slug']] = $setting['default'];
            elseif (!isset($buffer[$setting['page']][$setting['slug']]))
                $buffer[$setting['page']][$setting['slug']] = '';

            $output[$setting['page']][$setting['slug']] = $buffer[$setting['page']][$setting['slug']];
        }

        return $output;
    }

    public function output($varname = 'settings')
    {
        if ( empty($varname) )
            $varname = 'settings';

        $settings = self::getInstance();
        echo '<script type="text/javascript">' .
                'window.'.$varname.' = ' . json_encode($settings->getStructuredValues()) . ';' .
            '</script>';
    }

    public function __destruct()
    {

    }

}
