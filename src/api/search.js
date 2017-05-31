import logger from './logger';

export default {
  getSearch(term, callback) {
    wp.pages().search(term).get((err, data) => {
      callback(data);
      if (err) {
        logger.error(err);
      }
    });
  },
};
