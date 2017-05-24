import routes from './index';
import logger from './logger';

export default {
  find(id, callback) {
    axios.get(routes.menus.find.replace('{id}', id))
        .then((response) => {
          callback(response.data);
        })
        .catch((response) => {
          logger.error(response);
        });
  },
  getMenuByLocation(location, callback) {
    axios.get(routes.menus.location.replace('{location}', location))
        .then((response) => {
          callback(response.data);
        })
        .catch((response) => {
          logger.error(response);
        });
  },
};
