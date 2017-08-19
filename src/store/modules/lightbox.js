/* eslint no-shadow: ["error", { "allow": ["state"] }] */
/* eslint no-param-reassign: ["off"] */

// initial state
const state = {
  show: false,
  position: 0,
};

// getters
const getters = {
  showLightbox: state => state.show,
  getLightboxPosition: state => state.position,
};

// mutations
const mutations = {
  showLightbox(state, show) {
    state.show = show;
  },
  setLightboxPosition(state, position) {
    state.position = position;
  },
};

export default {
  state,
  getters,
  mutations,
};
