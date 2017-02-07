const webpack    = require('webpack');
const path       = require('path');
const ExtractTextPlugin = require("extract-text-webpack-plugin");
const OptimizeCssAssetsPlugin = require('optimize-css-assets-webpack-plugin');
const eslintFriendlyFormatter = require('eslint-friendly-formatter');

function resolve (dir) {
    return path.join(__dirname, '..', dir)
}

module.exports = {
    entry: './src/main.js',

    output: {
        path: './dist',
        filename: 'build.js'
    },

    resolve: {
        extensions: ['.js', '.vue', '.json'],
        alias: {
            'vue$': 'vue/dist/vue.common.js',
        },
        modules: [
            path.resolve('./src'),
            'node_modules'
        ],
    },

    module: {
        loaders: [
            {
                test: /\.(js|vue)$/,
                loader: 'eslint-loader',
                enforce: "pre",
                include: [resolve('src'), resolve('test')],
                options: {
                    formatter: eslintFriendlyFormatter
                }
            },
            {
                test: /\.vue$/,
                loader: 'vue-loader'
            },
            {
                test: /\.js$/,
                loader: 'babel-loader?presets[]=es2015',
                exclude: /node_modules/
            },
            {
                test: /\.scss$/,
                use: ExtractTextPlugin.extract({
                    fallbackLoader: "style-loader",
                    loader: [
                        'css-loader?importLoaders=1',
                        'postcss-loader?parser=postcss-scss'
                    ]
                })
            }
        ],
    },

    plugins: [
        new ExtractTextPlugin("styles.css"),
        new OptimizeCssAssetsPlugin({
            assetNameRegExp: /styles\.css/g,
            cssProcessor: require('cssnano'),
            cssProcessorOptions: { discardComments: {removeAll: true } },
            canPrint: true
        })
    ]

};