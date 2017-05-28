import logger from './logger';

export default {
  getRoutes(callback) {
    wp.routes = wp.registerRoute('wyvern/v1', '/routes/');
    wp.routes().get((err, data) => {
      callback(data);
      if (err) {
        logger.error(err);
      }
    });
  },
};
