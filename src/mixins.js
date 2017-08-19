/* eslint import/prefer-default-export: 0 */
/**
 * Pass permalink and get relative url
 * @since 0.1.0
 * @param {string} url - Full url - f.e. http://example.com/hello-world
 * @return {string} Relative url - f.e. /hello-world
 */
export function url2Slug(url) {
  if (typeof url === 'undefined') {
    return '';
  }
  return url.replace(/^.*\/\/[^/]+/, '');
}

/**
 * Set page title from route meta
 * @since 0.2.0
 */
export function title() {
  if (this.$route && this.$route.meta && this.$route.meta.wp_title) {
    document.title = this.$route.meta.wp_title;
  } else if (config && config.site_name && config.site_desc) {
    document.title = `${config.site_name} · ${config.site_desc}`;
  }
}
