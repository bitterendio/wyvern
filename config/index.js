var path = require('path')

module.exports = {
  build: {
    env                     : require('./prod.env'),
    productionSourceMap     : true,
    productionGzip          : false,
    productionGzipExtensions: ['js', 'css'],
    assetsSubDirectory      : 'assets'
  },
  dev  : {
    env               : require('./dev.env'),
    cssSourceMap      : false,
    assetsSubDirectory: 'assets'
  }
}