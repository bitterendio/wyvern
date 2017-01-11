/**
 * Created by Insane on 05/01/2017.
 */
const webpack    = require('webpack');
const path       = require('path');

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
                loaders: [
                    'style-loader',
                    'css-loader?importLoaders=1',
                    'postcss-loader?parser=postcss-scss'
                ]
            }
        ],
    }

};