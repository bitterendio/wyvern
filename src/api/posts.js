import logger from './logger';

export default {
  getPosts(callback) {
    wp.posts().get((err, data) => {
      callback(data);
      if (err) {
        logger.error(err);
      }
    });
  },
  getPost(id, callback) {
    wp.posts().id(id).get((err, data) => {
      callback(data);
      if (err) {
        logger.error(err);
      }
    });
  },
};
