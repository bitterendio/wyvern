import logger from './logger';

export default {
  getProduct(id, callback) {
    wp.products = wp.registerRoute('wyvern/v1', '/products/(?P<id>\\S+)');
    wp.products().id(id)
        .get((err, data) => {
          callback(data);
          if (err) {
            logger.error(err);
          }
        });
  },
  getProducts(callback) {
    wp.products = wp.registerRoute('wyvern/v1', '/products/');
    wp.products()
        .get((err, data) => {
          callback(data);
          if (err) {
            logger.error(err);
          }
        });
  },
};
