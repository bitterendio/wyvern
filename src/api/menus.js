import logger from './logger';

export default {
  find(id, callback) {
    wp.menus = wp.registerRoute('wyvern/v1', '/menu/(?P<id>\\S+)');
    wp.menus().id(id)
        .get((err, data) => {
          callback(data);
          if (err) {
            logger.error(err);
          }
        });
  },
  getMenuByLocation(location, callback) {
    wp.locations = wp.registerRoute('wyvern/v1', '/menu/location/(?P<location>\\S+)');
    wp.locations().location(location)
        .get((err, data) => {
          callback(data);
          if (err) {
            logger.error(err);
          }
        });
  },
};
