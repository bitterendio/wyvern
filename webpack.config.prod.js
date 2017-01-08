var config = require('./webpack.config.js');
const webpack    = require('webpack');

config.plugins = [
  new webpack.optimize.UglifyJsPlugin({
    compress: {
      screw_ie8: true,
      warnings: false
    }
  })
];

module.exports = config;