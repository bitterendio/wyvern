var config = require('./webpack.config.js');
const LiveReloadPlugin = require('webpack-livereload-plugin');

config.plugins.push(
    new LiveReloadPlugin({appendScriptTag: true})
);

module.exports = config;