import { routes, Vue, VueRouter } from './app';

import './../style.scss';

// Create router instance
const router = new VueRouter({
  mode: 'history',
  routes: routes.get(),
});

// Event bus
const bus = new Vue({});

// Start app
new Vue({ // eslint-disable-line no-new
  el: '#app',

  template: '<div class="template-wrapper" :class="this.$route.meta.slug">' +
    '<theme-header></theme-header>' +
    '<router-view class="router-view"></router-view>' +
    '<theme-footer></theme-footer>' +
  '</div>',

  router,

  data() {
    return {
      bus,
    };
  },

  mounted() {
    this.updateTitle('');
    this.trackGA();
  },

  methods: {
    updateTitle(pageTitle) {
      if (typeof pageTitle !== 'undefined') {
        if (pageTitle !== window.wp.site_name) {
          document.title = this.getTitle(pageTitle);
          return;
        }
      }

      document.title = this.getHomeTitle(pageTitle);
    },
    getTitle(pageTitle) {
      return `${pageTitle} - ${window.wp.site_name}`;
    },
    getHomeTitle() {
      return `${window.wp.site_name} - ${window.wp.site_desc}`;
    },
    trackGA() {
      if (typeof ga === 'function') {
        window.ga('set', 'page', `/${window.location.pathname.substr(1)}`);
        window.ga('send', 'pageview');
      }
    },
  },

  created() {
    window.eventHub.$on('page-title', this.updateTitle);
    window.eventHub.$on('track-ga', this.trackGA);
  },

  beforeDestroy() {
    window.eventHub.$off('page-title', this.updateTitle);
    window.eventHub.$off('track-ga', this.trackGA);
  },

  watch: {
    // Changed route
    $route(to, from) {
      window.eventHub.$emit('changed-route', to, from);
    },
  },
});
