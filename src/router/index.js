/* eslint no-unused-vars: ["off"] */
import Vue from 'vue';
import Router from 'vue-router';
import Templates from './templates';
import { setComponentsToRoutes } from './util';

window.Templates = Templates;

Vue.use(Router);

// Set names from route slugs
config.routes = config.routes
  ? config.routes.map(item => {
    const route = item;
    route.name = item.meta.slug;
    return route;
  })
  : [];

export default new Router({
  mode: 'history',
  routes: setComponentsToRoutes(config.routes),
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) {
      return savedPosition;
    }
    return { x: 0, y: 0 };
  },
});
