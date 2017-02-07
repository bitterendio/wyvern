import Vue from 'vue';
import axios from 'axios';

// Mixins
Vue.mixin({
  methods: {
    getMenuLocation(location, callback) {
      axios.get(`${window.wp.root}wp-api-menus/v2/menu-locations/${location}`)
          .then((response) => {
            if (typeof callback === 'function') {
              callback(response.data);
            }
          })
          .catch((error) => {
            console.error(error);
          });
    },

    url2Slug(url) {
      return url.replace(/^.*\/\/[^/]+/, '');
    },

    getPost(callback) {
      axios.get(`${window.wp.root}wp/v2/posts/${this.$route.meta.postId}`)
          .then((response) => {
            if (typeof callback === 'function') {
              callback(response.data);
            }
          }).catch((error) => {
        console.error(error);
      });
    },

    getCategory(categoryId) {
      axios.get(`${window.wp.root}wp/v2/categories/${categoryId}`)
          .then((response) => {
            this.categories.push(response.data);
          }).catch((error) => {
        console.error(error);
      });
    },

    getTag(tagId, callback) {
      axios.get(`${window.wp.root}wp/v2/tags/${tagId}`)
          .then((response) => {
            if (typeof callback === 'function') {
              callback(response.data);
            }
          }).catch((error) => {
        console.error(error);
      });
    },

    getAuthor(authorID, callback) {
      /* todo: CPT - turn to getPost with callback */
      axios.get(`${window.wp.root}wp/v2/authors/${authorID}?_embed=1`)
          .then((response) => {
            if (typeof callback === 'function') {
              callback(response.data);
            }
          }).catch((error) => {
        console.error(error);
      });
    },

    getCustom(slug, callback) {
      axios.get(`${window.wp.root}wp/v2/${slug}/?_embed=1`)
          .then((response) => {
            if (typeof callback === 'function') {
              callback(response.data);
            }
          }).catch((error) => {
        console.error(error);
      });
    },

    getUser(userId) {
      axios.get(`${window.wp.root}wp/v2/users/${userId}`)
          .then((response) => {
            this.author = response.data;
          }).catch((error) => {
        console.error(error);
      });
    },

    getPage(callback) {
      const cachekey = `getPage${this.$route.meta.postId}`;

      if (window.Cache.has(cachekey)) {
        if (typeof callback === 'function') {
          callback(window.Cache.get(cachekey));
          return;
        }
      }

      axios.get(`${window.wp.root}wp/v2/pages/${this.$route.meta.postId}`).then((response) => {
        if (typeof callback === 'function') {
          callback(response.data);
        }
        window.Cache.set(cachekey, response.data);
      }).catch((error) => {
        console.error(error);
      });
    },

    getPosts() {
      axios.get(`${window.wp.root}wp/v2/posts`).then((response) => {
        this.posts = response.data;
      }).catch((error) => {
        console.error(error);
      });
    },

    getPages() {
      axios.get(`${window.wp.root}wp/v2/pages`).then((response) => {
        this.pages = response.data;
      }, (error) => {
        console.error(error);
      });
    },

    getSearch(term, callback) {
      axios.get(`${window.wp.root}wp/v2/posts?search=${term}`).then((response) => {
        if (typeof callback === 'function') {
          callback(response.data);
        }
      }).catch((error) => {
        console.error(error);
      });
    },

    getSidebars(callback) {
      axios.get(`${window.wp.root}wp-json/wp-rest-api-sidebars/v1/sidebars`).then((response) => {
        if (typeof callback === 'function') {
          callback(response.data);
        }
      }).catch((error) => {
        console.error(error);
      });
    },

    getSidebar(sidebarId, callback) {
      axios.get(`${window.wp.root}wp-json/wp-rest-api-sidebars/v1/sidebars/${sidebarId}`).then((response) => {
        if (typeof callback === 'function') {
          callback(response.data);
        }
      }).catch((error) => {
        console.error(error);
      });
    },

    hasClass(ele, cls) {
      return !!ele.className.match(new RegExp(`(\\s|^)${cls}(\\s|$)`));
    },

    addClass(ele, cls) {
      /* eslint-disable */
      if (!this.hasClass(ele,cls)) ele.className += ` ${cls}`;  // @todo refactor
    },

    removeClass(ele, cls) {
      if (this.hasClass(ele, cls)) {
        const reg = new RegExp(`(\\s|^)${cls}(\\s|$)`);
        /* eslint-disable */
        ele.className = ele.className.replace(reg, ' ');  // @todo refactor
      }
    },

    decode(input) {
      return input.replace(
          /&(\d+);/g,
          (match, number) => String.fromCharCode(number),
      );
    },
  },
});