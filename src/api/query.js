import logger from './logger';

export default {
  getQuery(post_type, id, callback) {
    apiPromise.then((site) => {
      site[post_type]().id(id)
          .get((err, data) => {
            callback(data);
            if (err) {
              logger.error(err);
            }
          });
    });
  },
  getQueryArchive(post_type, callback) {
    apiPromise.then((site) => {
      site[post_type]()
          .get((err, data) => {
            callback(data);
            if (err) {
              logger.error(err);
            }
          });
    });
  },
};
