import Vue from 'vue';
import Vuex from 'vuex';
import * as actions from './actions';
import * as getters from './getters';
import product from './modules/product';
import menu from './modules/menu';
import search from './modules/search';
import lightbox from './modules/lightbox';
// import route from './modules/route';
import query from './modules/query';
import createLogger from '../plugins/logger';
import vuexCache from '../plugins/vuex-cache';

Vue.use(Vuex);

const debug = process.env.NODE_ENV !== 'production';

export default new Vuex.Store({
  actions,
  getters,
  modules: {
    product,
    menu,
    // route,
    query,
    search,
    lightbox,
  },
  strict: debug,
  plugins: debug ? [vuexCache, createLogger()] : [vuexCache],
});
