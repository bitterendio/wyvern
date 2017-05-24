/* eslint no-shadow: ["error", { "allow": ["state", "posts"] }]*/
/* eslint no-param-reassign: ["off"] */
import posts from '../../api/posts';
import * as types from '../mutation-types';

// initial state
const state = {
  all: [],
};

// getters
const getters = {
  allPosts: state => state.all,
};

// actions
const actions = {
  getAllPosts({ commit }) {
    posts.getPosts((posts) => {
      commit(types.RECEIVE_POSTS, { posts });
    });
  },
};

// mutations
const mutations = {
  [types.RECEIVE_POSTS](state, { posts }) {
    state.all = posts;
  },
};

export default {
  state,
  getters,
  actions,
  mutations,
};
