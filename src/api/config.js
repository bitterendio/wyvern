import logger from './logger';

export default {
  getConfig(callback) {
    wp.config = wp.registerRoute('wyvern/v1', '/config/');
    wp.config().get((err, data) => {
      callback(data);
      if (err) {
        logger.error(err);
      }
    });
  },
};
