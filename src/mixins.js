/* eslint import/prefer-default-export: 0 */
/**
 * Pass permalink and get relative url
 * @since 0.1.0
 * @param {string} url - Full url - f.e. http://example.com/hello-world
 * @returns {string} Relative url - f.e. /hello-world
 */
export function url2Slug(url) {
  return url.replace(/^.*\/\/[^/]+/, '');
}
