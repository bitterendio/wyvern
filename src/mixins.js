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
    document.querySelector('title').innerHTML = this.$route.meta.wp_title;
  } else if (config && config.site_name && config.site_desc) {
    document.querySelector('title').innerHTML = `${config.site_name} · ${config.site_desc}`;
  }
}

/**
 * Get Wyvern option
 */
export function getWyvernOption(slug, all = false) {
  const options = config.wyvernOptions;
  const found = options.find(option => option.slug === slug);
  if (all) {
    return found;
  }
  return found.value;
}

/**
 * Set Wyvern option
 */
export function setWyvernOption(slug, value) {
  const data = new FormData();
  data.append('value', value);
  axios.post(`${config.root}/wyvern/v1/options/update_option/${slug}`, data).then(() => {
  });
}
