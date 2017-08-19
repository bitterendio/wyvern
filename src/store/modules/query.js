/* eslint no-shadow: ["error", { "allow": ["state", "customs"] }] */
/* eslint no-param-reassign: ["off"] */
import query from '../../api/query';
import * as types from '../mutation-types';

// initial state
const state = {
  posts: [],
  archives: [],
};

// getters
const getters = {
  getQueryById(state) {
    return id => state.posts.find(item => item.id === id);
  },
  getQueryArchiveByType(state) {
    return (post_type) => {
      const statePost = state.archives.find(item => item.post_type === post_type);

      if (typeof statePost !== 'undefined') {
        return statePost.posts;
      }
      return [];
    };
  },
};

// @todo: find better solution
function getApiLevelFromPostType(post_type) {
  if (post_type === 'page') {
    return 'pages';
  } else if (post_type === 'post') {
    return 'posts';
  }
  return post_type;
}

// actions
const actions = {
  getQuery({ commit }, options) {
    if (typeof options.post_type !== 'undefined') {
      if (typeof options.id !== 'undefined') {
        query.getQuery(getApiLevelFromPostType(options.post_type), options.id, (post) => {
          commit(types.RECEIVE_POST, { post });
        });
      } else {
        query.getQueryArchive(getApiLevelFromPostType(options.post_type), (posts) => {
          commit(types.RECEIVE_POSTS, { posts, options });
        });
      }
    }
  },
};

// mutations
const mutations = {
  [types.RECEIVE_POST](state, { post }) {
    state.posts.push(post);
  },
  [types.RECEIVE_POSTS](state, { posts, options }) {
    state.archives.push({
      post_type: options.post_type,
      posts,
    });
  },
};

export default {
  state,
  getters,
  actions,
  mutations,
};
