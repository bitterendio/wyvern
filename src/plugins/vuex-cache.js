/* eslint-disable */
/* Credits: superwf/vuex-cache */
export default store => {
  const cache = Object.create(null);
  store.cacheDispatch = function cacheDispatch () {
    let type = arguments[0];
    if (typeof arguments[1] !== 'undefined') {
      type = type + JSON.stringify(arguments[1]);
    }
    if (type in cache) {
      return cache[type];
    }
    cache[type] = store.dispatch.apply(store, arguments);
    return cache[type];
  };

  store.removeCache = actionName => {
    if (actionName in cache) {
      delete cache[actionName];
      return true;
    }
    return false;
  };

  store.hasCache = key => {
    return key in cache
  };

  store.clearCache = () => {
    for (const key in cache) {
      delete cache[key];
    }
    return true;
  };
};