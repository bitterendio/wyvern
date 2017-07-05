const webpack    = require('webpack');
const path       = require('path');
const ExtractTextPlugin = require("extract-text-webpack-plugin");
const OptimizeCssAssetsPlugin = require('optimize-css-assets-webpack-plugin');
const eslintFriendlyFormatter = require('eslint-friendly-formatter');
const StyleLintPlugin = require('stylelint-webpack-plugin');
const vueLoaderConfig = require('./vue-loader.conf');
const utils = require('./utils');

function resolve (dir) {
    return path.join(__dirname, '..', dir)
}

module.exports = {
    entry: './src/main.js',

    output: {
        path: path.resolve('./dist'),
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
                loader: 'vue-loader',
                options: vueLoaderConfig
            },
            {
                test: /\.js$/,
                loader: 'babel-loader?presets[]=es2015',
                exclude: /node_modules/
            },
            {
                test: /\.(png|jpe?g|gif|svg|woff2?|eot|ttf|otf)(\?.*)?$/,
                loader: 'url-loader',
                query: {
                    limit: 10000,
                    name: utils.assetsPath('[name].[ext]')
                }
            }
        ],
    },

    plugins: [
        new ExtractTextPlugin("styles.css"),
        new StyleLintPlugin({
            files: ['style.scss']
        }),
        new OptimizeCssAssetsPlugin({
            assetNameRegExp: /styles\.css/g,
            cssProcessor: require('cssnano'),
            cssProcessorOptions: { discardComments: {removeAll: true } },
            canPrint: true
        })
    ]

};