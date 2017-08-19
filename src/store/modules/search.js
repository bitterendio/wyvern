/* eslint no-shadow: ["error", { "allow": ["state"] }] */
/* eslint no-param-reassign: ["off"] */
import search from '../../api/search';
import * as types from '../mutation-types';

// initial state
const state = {
  term: '',
  results: [],
};

// getters
const getters = {
  allResults: state => state.results,
  getTerm: state => state.term,
};

// actions
const actions = {
  setTerm({ commit }, term) {
    if (term === '') {
      const results = [];
      commit(types.RECEIVE_SEARCH, { results, term });
    } else {
      search.getSearch(term, (results) => {
        commit(types.RECEIVE_SEARCH, { results, term });
      });
    }
  },
};

// mutations
const mutations = {
  [types.RECEIVE_SEARCH](state, { results, term }) {
    state.results = results;
    state.term = term;
  },
};

export default {
  state,
  getters,
  actions,
  mutations,
};
