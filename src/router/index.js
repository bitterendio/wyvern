/* eslint no-unused-vars: ["off"] */
import Vue from 'vue';
import Router from 'vue-router';
import Templates from './templates';
import { setComponentsToRoutes } from './util';

window.Templates = Templates;

Vue.use(Router);

export default new Router({
  routes: setComponentsToRoutes(window.routes),
});
