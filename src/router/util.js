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
    } else if (item.meta.archive === true && window.Templates[`archive-${item.meta.type}`]) {
      // f.e. ArchiveProject
      newroute.component = window.Templates[`archive-${item.meta.type}`];
    } else if (item.meta.archive === true && window.Templates.archive) {
      // f.e. Archive
      newroute.component = window.Templates.archive;
    } else if (!item.meta.archive && window.Templates[item.meta.type]) {
      // f.e. Page
      newroute.component = window.Templates[item.meta.type];
    } else if (!item.meta.archive && window.Templates.single) {
      // f.e. Single
      newroute.component = window.Templates.single;
    } else {
      newroute.component = window.Templates.index;
    }
    // Remove domain from path
    newroute.path = newroute.path.replace(/^.*\/\/[^/]+/, '');
    return newroute;
  });
}
