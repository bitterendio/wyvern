/* eslint import/prefer-default-export: ["off"] */

/**
 * Get array of routes without components
 * and set components to them using WP
 * template hierarchy logic.
 * @param {Array} routes
 * @return {Array}
 */
export function setComponentsToRoutes(routes) {
  return routes.map((item) => {
    const newroute = item;
    if (window.Templates[item.meta.template]) {
      // (custom template) f.e. Sidebar
      newroute.component = window.Templates[item.meta.template];
    } else if (window.Templates[`${item.meta.type}-${item.meta.slug}`]) {
      // (page$slug) f.e. Pageslug
      newroute.component = window.Templates[`${item.meta.type}-${item.meta.slug}`];
    } else if (window.Templates[`${item.meta.type}-${item.meta.id}`]) {
      // (page$id) f.e. Page1
      newroute.component = window.Templates[`${item.meta.type}-${item.meta.id}`];
    } else if (window.Templates[item.meta.type]) {
      // f.e. Page
      newroute.component = window.Templates[item.meta.type];
    } else {
      newroute.component = window.Templates.Index;
    }
    return newroute;
  });
}
