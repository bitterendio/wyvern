![Insane Wyvern](/insane_wyvern.png?raw=true)

# Contents
- [Installation](#installation)
- [Folder structure](#folder-structure)
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
└---api                         Files for customization WP Rest Api 2
|   └---example.php                 Sample code for custom endpoint
└---assets                      Assets (Images etc.)
└---build                       Configurations for webpack
|   └---webpack.config.dev.js       Webpack config for development
|   └---webpack.config.js           Basic webpack configuration
|   └---webpack.config.prod.js      Webpack config for production
└---dist                        Compiled javascripts with styles
└---src                         Main logic (.js and .vue files)
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