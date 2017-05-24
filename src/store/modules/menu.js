/* eslint no-shadow: ["error", { "allow": ["state", "menus"] }]*/
/* eslint no-param-reassign: ["off"] */
import menus from '../../api/menus';
import * as types from '../mutation-types';

// initial state
const state = {
  menus: [],
};

// getters
const getters = {
  getMenuByLocation(state) {
    return location => state.menus.find(item => item.location === location);
  },
};

// actions
const actions = {
  getMenu({ commit }, options) {
    if (typeof options.location !== 'undefined') {
      menus.getMenuByLocation(options.location, (items) => {
        commit(types.RECEIVE_MENU, { items, options });
      });
    }
  },
};

// mutations
const mutations = {
  [types.RECEIVE_MENU](state, { items, options }) {
    state.menus.push({
      location: options.location,
      items,
    });
  },
};

export default {
  state,
  getters,
  actions,
  mutations,
};
