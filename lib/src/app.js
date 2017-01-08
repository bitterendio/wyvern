import Vue from 'vue'
import VueRouter from 'vue-router'
import axios from 'axios'

require('es6-promise/auto');

// Import styles
import './../../style.scss'

const moment = require('moment')
require('moment/locale/cs')

Vue.use(require('vue-moment'), {
  moment
});
Vue.use(VueRouter);

window.wp.templates = [];

// Default components
import Posts from './posts.vue'
import Post from './post.vue'
Vue.component('Post', Post)
window.wp.templates.push('Post')

import Page from './page.vue'
Vue.component('Page', Page)
window.wp.templates.push('Page')

import Header from './theme-header.vue'
Vue.component('theme-header', Header)
import Footer from './theme-footer.vue'
Vue.component('theme-footer', Footer)

// Layout partials
import Levels from './levels.vue'
Vue.component('levels', Levels)

// Levels - ACF flexible content layouts
import Intro from './levels/intro.vue'
Vue.component('intro', Intro)
import Mapbox from './levels/mapbox.vue'
Vue.component('mapbox', Mapbox)

// Routes
var routes = {

  listed: {},

  push(obj) {

    var self = this
    obj.forEach(function(route){
      self.listed[route.path] = route
    })

  },

  get() {

    var self = this,
        output = []

    for ( var key in this.listed ) {
      var route = this.listed[key]
      output.push(route)
    }

    return output
  },

  add(route) {
    return this.push([route])
  }

}

// Cache
window.Cache = {
  data: {},

  set(key, value) {
    this.data[key] = value;
  },

  get(key) {
    if ( typeof this.data[key] !== 'undefined' )
      return this.data[key];
  },

  has(key) {
    if ( typeof this.data[key] !== 'undefined' )
      return true;
    return false;
  }
}

// Mixins
Vue.mixin({
  methods: {
    getMenuLocation(location, callback) {
      axios.get(wp.root + 'wp-api-menus/v2/menu-locations/' + location)
          .then(function (response) {
            if ( typeof callback == 'function' )
              callback(response.data);
          })
          .catch(function (error) {
            console.log(error);
          });
    },

    url2Slug(url) {
      return url.replace(/^.*\/\/[^\/]+/, '')
    },

    getPost(callback) {
      axios.get(wp.root + 'wp/v2/posts/' + this.$route.meta.postId)
          .then(function(response) {
            if ( typeof callback == 'function' )
              callback(response.data);
          }).catch(function(error) {
        console.log(error);
      });
    },

    getCategory(categoryId) {
      var self = this;
      axios.get(wp.root + 'wp/v2/categories/' + categoryId)
          .then(function(response) {
            self.categories.push(response.data)
          }).catch(function(error) {
        console.log(error);
      });
    },

    getTag(tagId, callback) {
      var self = this;
      axios.get(wp.root + 'wp/v2/tags/' + tagId)
          .then(function(response) {
            if ( typeof callback == 'function' )
              callback(response.data)
          }).catch(function(error) {
        console.log(error);
      });
    },

    getAuthor(authorID, callback) {
      /* todo: CPT - turn to getPost with callback */
      var self = this;
      axios.get(wp.root + 'wp/v2/authors/' + authorID + '?_embed=1')
          .then(function(response) {
            if ( typeof callback == 'function' )
              callback(response.data)
          }).catch(function(error) {
        console.log(error);
      });
    },

    getCustom(slug, callback) {
      var self = this;
      axios.get(wp.root + 'wp/v2/' + slug + '/?_embed=1')
          .then(function(response) {
            if ( typeof callback == 'function' )
              callback(response.data)
          }).catch(function(error) {
        console.log(error);
      });
    },

    getUser(userId) {
      var self = this;
      axios.get(wp.root + 'wp/v2/users/' + userId)
          .then(function(response) {
            self.author = response.data
          }).catch(function(error) {
        console.log(error);
      });
    },

    getPage(callback) {
      var self = this;

      var cachekey = 'getPage' + this.$route.meta.postId;
      if ( window.Cache.has(cachekey) ) {
        if ( typeof callback == 'function' )
          return callback(window.Cache.get(cachekey));
      }

      axios.get(wp.root + 'wp/v2/pages/' + this.$route.meta.postId).then(function(response){

        if ( typeof callback == 'function' )
          callback(response.data);

        window.Cache.set(cachekey, response.data);

      }).catch(function(error) {
        console.log(error);
      });
    },

    getPosts() {
      var self = this;
      axios.get(wp.root + 'wp/v2/posts').then(function(response) {
        self.posts = response.data;
        window.eventHub.$emit('page-title', '');
        window.eventHub.$emit('track-ga');
      }).catch(function(error) {
        console.log(error);
      });
    },

    getPages() {
      var self = this;
      axios.get(wp.root + 'wp/v2/pages').then(function(response) {
        self.pages = response.data;
      }, function(response) {
        console.log(response);
      });
    },

    getSearch(term, callback) {
      axios.get(wp.root + 'wp/v2/posts?search='+term).then(function(response) {
        if ( typeof callback == 'function' )
          callback(response.data);
      }).catch(function(error) {
        console.log(error);
      });
    },

    getSidebars(callback) {
      axios.get(wp.root + 'wp-json/wp-rest-api-sidebars/v1/sidebars').then(function(response) {
        if ( typeof callback == 'function' )
          callback(response.data);
      }).catch(function(error) {
        console.log(error);
      });
    },

    getSidebar(sidebarId, callback) {
      axios.get(wp.root + 'wp-json/wp-rest-api-sidebars/v1/sidebars/' + sidebarId).then(function(response) {
        if ( typeof callback == 'function' )
          callback(response.data);
      }).catch(function(error) {
        console.log(error);
      });
    },

    getPlacePosts(callback) {
      axios.get(wp.root + 'wp/v2/posts?per_page=100').then(function(response) {
        if ( typeof callback == 'function' )
          callback(response.data);
      }).catch(function(error) {
        console.log(error);
      });
    }
  }
});

// Front page displays == Your latest posts
if ( wp.show_on_front == 'posts' ) {
  routes.add({
    path: wp.base_path,
    component: Posts
  });
}

// Front page displays == A static page
if ( wp.show_on_front == 'page' ) {

  if ( wp.page_on_front != 0 ) {
    // type is "Front page"
    routes.add({
      path     : wp.base_path,
      component: Page,
      meta: {
        postId: wp.page_on_front
      }
    });
  } else if ( wp.page_on_front != 0 ) {
    // type is "Posts page"
    routes.add({
      path     : wp.base_path,
      component: Post,
      meta: {
        postId: wp.page_for_posts
      }
    });
  }
}

// Dynamically generated routes
wp.routes.forEach(function (wproute) {
  routes.add({
    path: wp.base_path + wproute.slug,
    component: {
      extends: Vue.component(getTemplateHierarchy(wproute.type, wproute.id, wproute.template))
    },
    meta: {
      postId: wproute.id,
      template: wproute.template
    }
  })

  // When full link is used
  routes.add({
    path: wproute.link,
    component: {
      extends: Vue.component(getTemplateHierarchy(wproute.type, wproute.id, wproute.template))
    },
    meta: {
      postId: wproute.id,
      template: wproute.template
    }
  })
})

function capitalize(string) {
  return string.charAt(0).toUpperCase() + string.slice(1);
}

function getTemplateHierarchy(type, id, template) {

  // f.e. Map
  if ( typeof template == 'string' ) {
    if (window.wp.templates.indexOf(capitalize(template)) !== -1)
      return capitalize(template);
  }

  // f.e. Page9
  if ( typeof type == 'string' && typeof id != 'undefined' ) {
    if (window.wp.templates.indexOf(capitalize(type) + id) !== -1)
      return capitalize(type) + id;
  }

  // f.e. Page
  if ( typeof type == 'string' ) {
    if (window.wp.templates.indexOf(capitalize(type)) !== -1)
      return capitalize(type);
  }

}

// Register eventHub
window.eventHub = new Vue();

export { routes, Vue, VueRouter, capitalize, getTemplateHierarchy }