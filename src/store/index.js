import Vue from 'vue';
import Vuex from 'vuex';
import * as actions from './actions';
import * as getters from './getters';
import page from './modules/page';
import post from './modules/post';
import menu from './modules/menu';
import route from './modules/route';
import createLogger from '../plugins/logger';
import vuexCache from '../plugins/vuex-cache';

Vue.use(Vuex);

const debug = process.env.NODE_ENV !== 'production';

export default new Vuex.Store({
  actions,
  getters,
  modules: {
    post,
    menu,
    page,
    route,
  },
  strict: debug,
  plugins: debug ? [vuexCache, createLogger()] : [vuexCache],
});
