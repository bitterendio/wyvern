/**
 * Created by Insane on 05/01/2017.
 *
 * Config for PostCSS
 */

module.exports = {
    plugins: [
        require('precss'),
        require('postcss-assets')({
            loadPaths: ['assets/'],
            baseUrl: '/wp-content/themes/insane/'
        }),
        require('postcss-calc')
    ]
}
