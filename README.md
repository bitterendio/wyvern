![Insane Wyvern](/insane_wyvern.png?raw=true)

# Contents
- [Installation](#installation)
- [Folder structure](#folder-structure)
- [Mixin methods](#mixin)
  - [Menu](#mixin-menu)
- [Routes](#routes)
  - [Override in child theme](#routes-override)
- [Events](#events)
- [Page templates](#templates)
  - [Register page templates](#templates-page)
- [Theme Options](#theme-options)
  - [Register new settings](#theme-options-register-new-settings)
  - [WP object](#theme-options-wp-object)
    - [Customizing WP object](#theme-options-wp-object-customizing)
- [Plugins](#plugins)
    - [Necessary](#necessary-plugins)
    - [Recommended](#recommended-plugins)
- [Development](#development)
    - [Development commands](#development-cmds)
    - [Styles](#styles)

#<a name="installation"></a>Installation

1. Download latest version of Wordpress
    ```
    https://wordpress.org/download/
    ```

2. Setup your wp-config.php

3. Clone this repository to /wp-content/themes

    ```
    git clone https://github.com/sanatorium/wyvern.git
    ```
    
    or
    
    ```
    composer require sanatorium/wyvern
    ```

4. Install dependencies for Wyvern theme

    ```
    yarn install
    ```

    or

    ```
    npm install
    ```

5. Install Wordpress and then install and activate all [necessary](#necessary-plugins) and optionally even [recommended](#recommended-plugins)  plugins

6. Activate and enjoy insanely awesome Wyvern Theme!

#<a name="folder-structure"></a>Folder structure

```
    wyvern
    |
    |   postcss.config.js           PostCSS configuration file
    |   style.scss                  PostCSS styles with Sass markup
    |   
    └──api                          Files for customization WP Rest Api 2
    |   └───example.php                 Sample code for custom endpoint
    └───assets                      Assets (Images etc.)
    └───build                       Configurations for webpack
    |   └───webpack.config.dev.js       Webpack config for development
    |   └───webpack.config.js           Basic webpack configuration
    |   └───webpack.config.prod.js      Webpack config for production
    └───dist                        Compiled javascripts with styles
    └───lib                         PHP libraries
    └───src                         Main logic (.js and .vue files)
```

#<a name="mixin"></a>Mixin methods

##<a name="mixin-menu"></a>Menu

- Note: Menu API calls require WP API Menus plugin

**getMenuLocation(location, [callback])** allows retrieving menu by it's location. Sample usage in component:

```
<script>
export default {
  mounted() {
    this.getMenuLocation('primary', data => this.menu = data)
  },
  data() {
    return {
      menu: []
    }
  }
}
</script>
<template>
  <ul>
    <li v-for="item in menu">
      <router-link :to="{ path: item.url }">{{ item.title }}</router-link>
    </li>
  </ul>
</template>
```

#<a name="events"></a>Events

To emit events across all instances, use **window.eventHub** object. See implementation of Google Analytics tracking below as example:

```
// somewhere in component
new Vue({
  methods: {
    trackGA() {
      if ( typeof ga == 'function' ) {
        ga('set', 'page', '/' + window.location.pathname.substr(1))
        ga('send', 'pageview')
      }
    },
  },
  created() {
    window.eventHub.$on('track-ga', this.trackGA)
  }
});

// somewhere in another component
window.eventHub.$emit('track-ga')
```

#<a name="routes"></a>Routes

##<a name="routes-override"></a>Override in child theme

Sometimes you need to override components in child theme. Wyvern registers some basic components like Page and then registers routes using these components.

```
// app.js
import Page from './page.vue'
Vue.component('Page', Page)
```

Now your child theme might need different layout for Page component, therefore after you register new component, use **routes.refresh()**.

```
// main.js (for example child theme)
import Page from './page.vue'
Vue.component('Page', Page)

// Replace overridden components in existing routes
routes.refresh()
```

#<a name="templates"></a>Page templates

##<a name="templates-page"></a>Register page templates

Declare **get_virtual_templates()** function in your theme's functions.php. This function should return array value.

```
if ( !function_exists('get_virtual_templates') )
{
    function get_virtual_templates()
    {
        return [
            'custom' => 'Custom template',
        ];
    }
}
```

#<a name="theme-options"></a> Theme options

You will find theme options under **Appearance > Wyvern Theme**

##<a name="theme-options-register-new-settings"></a> Register new settings

```
Wyvern\Includes\Settings::add('New thing', 'new_thing');
```

##<a name="theme-options-wp-object"></a> WP object

Globally accessible WP object contains lot of useful stuff to be used in javascript app like **site_name** (``get_bloginfo('name')``) or **site_desc** (``get_bloginfo('description')``).

###<a name="theme-options-wp-object-customizing"></a> Customizing WP object

```
add_filter( 'wyvern_wp_settings', function($output) { 
  $output['cart'] = WC()->cart->get_cart();
  return $output;
} );
```

#<a name="plugins"></a>Plugins

##<a name="necessary-plugins"></a>Necessary

* [WordPress REST API][rest-api]
* [WP API Menus][wp-api-menus]

## <a name="recommended-plugins"></a>Recommended

* [ACF Pro][acf]
* [Akismet][akismet]
* [Captcha][captcha]
* [CPT UI][cptui]
* [MailPoet Newsletters][mailpoet]
* [Relative URL][relative-url]
* [wpMandrill][wpmandrill]

#<a name="development"></a>Development

Wyvern's development tools are using webpack so if you don't have webpack installed globally run:


```
yarn global add webpack
```

or

```
npm install webpack -g
```

Webpack configuration is stored in ``webpack.config.js``

##<a name="development-cmds"></a>Development commands


| command           | description                          | using tasks                                                                                    |
|-------------------|--------------------------------------|------------------------------------------------------------------------------------------------|
| ``npm run build`` | Run task once                        | ``webpack --config webpack.config.prod.js --progress --colors --display-error-details``        |
| ``npm run dev``   | Watch changes in files and run tasks | ``webpack --config webpack.config.dev.js --progress --colors --display-error-details --watch`` |

##<a name="styles"></a>Styles

- Styles could be written as Sass-like markup
- Styles could be stored in ``style.scss`` or inline in ``.vue`` templates
- Webpack watch for ``.scss`` files
- Styles are transforming to ``.css`` by [PostCSS][post-css]
- [PostCSS][post-css] configruation is stored in ``postcss.config.js``
- [PostCSS][post-css] uses [PostCSS SCSS][postcss-scss] as parser and [PreCSS][precss], [PostCSS-Assets][postcss-assets], [PostCSS-Calc][postcss-calc] as plugins 

[acf-account]: https://www.advancedcustomfields.com/my-account/
[akismet]: https://wordpress.org/plugins/akismet/
[captcha]: https://wordpress.org/plugins/captcha/
[rest-api]: https://wordpress.org/plugins/rest-api/
[wp-api-menus]: https://wordpress.org/plugins/wp-api-menus/
[relative-url]: https://wordpress.org/plugins/relative-url/
[wpmandrill]: https://wordpress.org/plugins/wpmandrill/
[mailpoet]: https://wordpress.org/plugins/wysija-newsletters/
[acf]: https://wordpress.org/plugins/advanced-custom-fields/
[cptui]: https://wordpress.org/plugins/custom-post-type-ui/
[post-css]: https://github.com/postcss/postcss
[postcss-scss]: https://github.com/postcss/postcss-scss
[precss]: https://github.com/jonathantneal/precss
[postcss-assets]: https://github.com/assetsjs/postcss-assets
[postcss-calc]: https://github.com/postcss/postcss-calc
[wordpress-download]:(https://wordpress.org/download/)