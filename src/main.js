// The Vue build version to load with the `import` command
// (runtime-only or standalone) has been set in webpack.base.conf with an alias.
import Vue from 'vue';
import Vuex from 'vuex';
import axios from 'axios';
import * as mixins from './mixins';
import App from './App';
import store from './store';
import router from './router';

// Vuex
Vue.use(Vuex);

// Mixins
Vue.mixin({
  methods: mixins,
});

// Standard UI components
Vue.component('menu-location', require('@/components/partials/menu-location'));

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
});
