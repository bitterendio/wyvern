import routes from './index';
import logger from './logger';

export default {
  getPosts(callback) {
    axios.get(routes.posts.get)
        .then((response) => {
          callback(response.data);
        })
        .catch((response) => {
          logger.error(response);
        });
  },
};
