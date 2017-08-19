/* eslint no-shadow: ["error", { "allow": ["state", "routes"] }] */
/* eslint no-param-reassign: ["off"] */
import routes from '../../api/routes';
import * as types from '../mutation-types';

// initial state
const state = {
  all: [],
};

// getters
const getters = {
  allRoutes: state => state.all,
};

// actions
const actions = {
  getAllRoutes({ commit }) {
    routes.getRoutes((routes) => {
      commit(types.RECEIVE_POSTS, { routes });
    });
  },
};

// mutations
const mutations = {
  [types.RECEIVE_ROUTES](state, { routes }) {
    state.all = routes;
  },
};

export default {
  state,
  getters,
  actions,
  mutations,
};
