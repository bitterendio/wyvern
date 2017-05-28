/* eslint no-shadow: ["error", { "allow": ["state", "pages"] }]*/
/* eslint no-param-reassign: ["off"] */
import pages from '../../api/pages';
import * as types from '../mutation-types';

// initial state
const state = {
  pages: [],
};

// getters
const getters = {
  getPageById(state) {
    return id => state.pages.find(item => item.id === id);
  },
};

// actions
const actions = {
  getPage({ commit }, options) {
    if (typeof options.id !== 'undefined') {
      pages.getPage(options.id, (page) => {
        commit(types.RECEIVE_PAGE, { page });
      });
    }
  },
};

// mutations
const mutations = {
  [types.RECEIVE_PAGE](state, { page }) {
    state.pages.push(page);
  },
};

export default {
  state,
  getters,
  actions,
  mutations,
};
