const baseWebpackConfig = require('./webpack.config.js');
const LiveReloadPlugin = require('webpack-livereload-plugin');

baseWebpackConfig.plugins.push(
    new LiveReloadPlugin({
      appendScriptTag: true
    })
);

module.exports = baseWebpackConfig;