/* eslint no-shadow: ["error", { "allow": ["state", "pages"] }]*/
/* eslint no-param-reassign: ["off"] */
import pages from '../../api/pages';
import * as types from '../mutation-types';

// initial state
const state = {
  currentPage: {},
};

// getters
const getters = {
  currentPage: state => state.currentPage,
};

// actions
const actions = {
  getPage({ commit }, options) {
    pages.getPage(options.id, (page) => {
      commit(types.RECEIVE_PAGE, { page });
    });
  },
};

// mutations
const mutations = {
  [types.RECEIVE_PAGE](state, { page }) {
    state.currentPage = page;
  },
};

export default {
  state,
  getters,
  actions,
  mutations,
};
