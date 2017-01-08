var config = require('./webpack.config.js');
const LiveReloadPlugin = require('webpack-livereload-plugin');

config.plugins = [
  new LiveReloadPlugin({appendScriptTag: true}),
];

module.exports = config;