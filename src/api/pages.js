import logger from './logger';

export default {
  getPage(id, callback) {
    wp.pages().id(id).get((err, data) => {
      callback(data);
      if (err) {
        logger.error(err);
      }
    });
  },
};
