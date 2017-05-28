/* eslint no-shadow: ["error", { "allow": ["state", "posts"] }]*/
/* eslint no-param-reassign: ["off"] */
import posts from '../../api/posts';
import * as types from '../mutation-types';

// initial state
const state = {
  all: [],
  currentPost: {},
};

// getters
const getters = {
  allPosts: state => state.all,
  currentPost: state => state.currentPost,
};

// actions
const actions = {
  getAllPosts({ commit }) {
    posts.getPosts((posts) => {
      commit(types.RECEIVE_POSTS, { posts });
    });
  },
  getPost({ commit }, options) {
    posts.getPost(options.id, (post) => {
      commit(types.RECEIVE_POST, { post });
    });
  },
};

// mutations
const mutations = {
  [types.RECEIVE_POSTS](state, { posts }) {
    state.all = posts;
  },
  [types.RECEIVE_POST](state, { post }) {
    state.currentPost = post;
  },
};

export default {
  state,
  getters,
  actions,
  mutations,
};
