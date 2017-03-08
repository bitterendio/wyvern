var utils = require('./utils');

module.exports = {
  loaders: utils.cssLoaders({
    sourceMap: false,
    extract: true
  }),
  postcss: [
    require('autoprefixer')({
      browsers: ['last 2 versions']
    })
  ]
}
