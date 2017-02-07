import Vue from 'vue';
import VueRouter from 'vue-router';
import axios from 'axios';
import Posts from './posts.vue';
import Post from './post.vue';
import Page from './page.vue';
import Product from './product.vue';
import Cart from './cart.vue';
import Footer from './theme-footer.vue';
import Header from './theme-header.vue';
import Levels from './levels.vue';
import Intro from './levels/intro.vue';
import Mapbox from './levels/mapbox.vue';
import Woocommerce from './levels/woocommerce.vue';
import Steps from './levels/steps.vue';
import Headline from './levels/headline.vue';
import FBPagePlugin from './levels/fb_pageplugin.vue';
import InstagramEmbed from './levels/instagram_embed.vue';
import * as vueMixins from './mixin';

require('es6-promise/auto');

Vue.use(VueRouter);
window.wp.templates = [];
Vue.component('Post', Post);
window.wp.templates.push('Post');
Vue.component('Page', Page);
window.wp.templates.push('Page');
Vue.component('Product', Product);
window.wp.templates.push('Product');
Vue.component('Cart', Cart);
window.wp.templates.push('Cart');
Vue.component('theme-header', Header);
Vue.component('theme-footer', Footer);
Vue.component('levels', Levels);
Vue.component('intro', Intro);
Vue.component('mapbox', Mapbox);
Vue.component('woocommerce', Woocommerce);
Vue.component('steps', Steps);
Vue.component('headline', Headline);
Vue.component('fb_pageplugin', FBPagePlugin);
Vue.component('instagram_embed', InstagramEmbed);

// Routes
const routes = {

  listed: {},

  push(obj) {
    obj.forEach((route) => {
      this.listed[route.path] = route;
    });
  },

  get() {
    const output = [];
    Object.keys(this.listed).forEach((key) => {
      const route = this.listed[key];
      output.push(route);
    });
    return output;
  },

  add(route) {
    return this.push([route]);
  },

  /**
   * Refresh router, when default components,
   * like Page, Post etc. have been replaced
   */
  refresh() {
    Object.keys(this.listed).forEach((key) => {
      const original = this.listed[key];
      this.listed[key] = {
        path: original.path,
        component: {
          extends: Vue.component(original.meta.name),
        },
        meta: original.meta,
      };
    });
  },
};

// Wyvern
window.wyvern = {
  http: axios,
};

// Cache
window.Cache = {
  data: {},

  set(key, value) {
    this.data[key] = value;
  },

  get(key) {
    if (typeof this.data[key] !== 'undefined') {
      return this.data[key];
    }
    return null;
  },

  has(key) {
    if (typeof this.data[key] !== 'undefined') {
      return true;
    }
    return false;
  },
};


function capitalize(string) {
  return string.charAt(0).toUpperCase() + string.slice(1);
}

function getTemplateHierarchy(type, id, template) {
  // f.e. Map
  if (typeof template === 'string') {
    if (window.wp.templates.indexOf(capitalize(template)) !== -1) {
      return capitalize(template);
    }
  }

  // f.e. Page9
  if (typeof type === 'string' && typeof id !== 'undefined') {
    if (window.wp.templates.indexOf(`${capitalize(type)}${id}`) !== -1) {
      return `${capitalize(type)}${id}`;
    }
  }

  // f.e. Page
  if (typeof type === 'string') {
    if (window.wp.templates.indexOf(capitalize(type)) !== -1) {
      return capitalize(type);
    }
  }

  return '';
}

// Front page displays == Your latest posts
if (window.wp.show_on_front === 'posts') {
  routes.add({
    path: window.wp.base_path,
    component: Posts,
    name: 'Posts',
    slug: 'home',
  });
}

// Front page displays == A static page
if (window.wp.show_on_front === 'page') {
  if (parseInt(window.wp.page_on_front, 10) !== 0) {
    // type is "Front page"
    routes.add({
      path: window.wp.base_path,
      component: Page,
      meta: {
        postId: window.wp.page_on_front,
        name: 'Page',
        slug: 'home',
      },
    });
  } else if (window.wp.page_on_front !== 0) {
    // type is "Posts page"
    routes.add({
      path: window.wp.base_path,
      component: Post,
      meta: {
        postId: window.wp.page_for_posts,
        name: 'Post',
        slug: 'home',
      },
    });
  }
}

// Dynamically generated routes
window.wp.routes.forEach((wproute) => {
  routes.add({
    path: `${window.wp.base_path}${wproute.slug}`,
    component: {
      extends: Vue.component(getTemplateHierarchy(wproute.type, wproute.id, wproute.template)),
    },
    meta: {
      postId: wproute.id,
      template: wproute.template,
      name: getTemplateHierarchy(wproute.type, wproute.id, wproute.template),
      slug: wproute.slug,
    },
  });

  // When full link is used
  routes.add({
    path: wproute.link,
    component: {
      extends: Vue.component(getTemplateHierarchy(wproute.type, wproute.id, wproute.template)),
    },
    meta: {
      postId: wproute.id,
      template: wproute.template,
      name: getTemplateHierarchy(wproute.type, wproute.id, wproute.template),
      slug: wproute.slug,
    },
  });
});

// Register eventHub
window.eventHub = new Vue();

export { routes, Vue, VueRouter, capitalize, getTemplateHierarchy };
