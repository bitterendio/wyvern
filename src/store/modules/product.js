/* eslint no-shadow: ["error", { "allow": ["state", "products"] }] */
/* eslint no-param-reassign: ["off"] */
import products from '../../api/products';
import * as types from '../mutation-types';

// initial state
const state = {
  products: [],
};

// getters
const getters = {
  getProductById(state) {
    return id => state.products.find(item => item.id === id);
  },
};

// actions
const actions = {
  getProduct({ commit }, options) {
    if (typeof options.id !== 'undefined') {
      products.getProduct(options.id, (product) => {
        commit(types.RECEIVE_PRODUCT, { product });
      });
    }
  },
};

// mutations
const mutations = {
  [types.RECEIVE_PRODUCT](state, { product }) {
    state.products.push(product);
  },
};

export default {
  state,
  getters,
  actions,
  mutations,
};
