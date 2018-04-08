// The Vue build version to load with the `import` command
// (runtime-only or standalone) has been set in webpack.base.conf with an alias.
import Vue from 'vue';
import Vuex from 'vuex';
import axios from 'axios';
import WPAPI from 'wpapi';
import * as mixins from './mixins';
import App from './App';
import store from './store';
import router from './router';
import { setComponentsToRoutes } from './router/util';
import WPConfig from './api/config';
import EventBus from './event-bus';

// Vuex
Vue.use(Vuex);

// Mixins
Vue.mixin({
  methods: mixins,
});

// Lodash
window._ = require('lodash');

/**
 * WP API
 * https://github.com/WP-API/node-wpapi
 */
window.wp = new WPAPI({ endpoint: config.root });
window.apiPromise = WPAPI.discover(config.base_url);

/**
 * Standard UI components
 */
Vue.component('menu-location', require('@/components/partials/menu-location'));
Vue.component('gallery', require('@/components/partials/gallery'));
Vue.component('lightbox', require('@/components/partials/lightbox'));
Vue.component('theme-header', require('@/components/partials/theme-header'));

// Running in dev mode, load routes from API
if (process.env.NODE_ENV !== 'production') {
  WPConfig.getConfig(data => {
    window.config = data;
    EventBus.$emit('config', window.config);
    router.addRoutes(setComponentsToRoutes(window.config.routes));
  });
}

/**
 * Following implementation allows using axios in two ways,
 * calling this.$http or addressing window.axios.
 * axios is also added to eslint globals.
 */
Vue.prototype.$http = axios;
window.axios = axios;

Vue.config.productionTip = false;

/* eslint-disable no-new */
new Vue({
  el: '#app',
  router,
  store,
  template: '<App/>',
  components: { App },
  methods: {
    // Track google analytics pageview
    trackGA() {
      if (typeof window.ga === 'function') {
        window.ga('set', 'page', `/${window.location.pathname.substr(1)}`);
        window.ga('send', 'pageview');
      }
    },
  },
  watch: {
    $route: 'trackGA',
  },
});

/* eslint-disable */
export { Vue, router, store, App };
