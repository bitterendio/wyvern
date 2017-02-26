import axios from 'axios';

export function getMenuLocation(location, callback) {
  axios.get(`${window.wp.root}api/menu/${location}`)
    .then((response) => {
      if (typeof callback === 'function') {
        callback(response.data);
      }
    })
    .catch(() => {
    });
}

/**
 * Pass permalink and get relative url
 * @since 0.1.0
 * @param {string} url - Full url - f.e. http://example.com/hello-world
 * @returns {string} Relative url - f.e. /hello-world
 */
export function url2Slug(url) {
  return url.replace(/^.*\/\/[^/]+/, '');
}

/**
 * Get single post by id defined in $route.meta.postId
 * @todo make id param and use $route.meta.postId as fallback
 * @todo potentially create functionality for error
 * @since 0.1.0
 * @param {function} callback - Callback function
 */
export function getPost(callback) {
  axios.get(`${window.wp.root}wp/v2/posts/${this.$route.meta.postId}`)
    .then((response) => {
      if (typeof callback === 'function') {
        callback(response.data);
      }
    }).catch(() => {});
}

/**
 * Get single category by id, and push to local categories array
 * @todo return category instead of pushing to categories array
 * @todo potentially create functionality for error
 * @since 0.1.0
 * @param {number} categoryId - Id of the given category
 */
export function getCategory(categoryId, callback) {
  axios.get(`${window.wp.root}wp/v2/categories/${categoryId}`)
    .then((response) => {
      if (typeof callback === 'function') {
        callback(response.data);
      }
    }).catch(() => {});
}

/**
 * Get tag object and pass it to callback function
 * @since 0.1.0
 * @todo potentially create functionality for error
 * @param {number} tagId - Tag ID
 * @param {function} callback - Callback function
 */
export function getTag(tagId, callback) {
  axios.get(`${window.wp.root}wp/v2/tags/${tagId}`)
    .then((response) => {
      if (typeof callback === 'function') {
        callback(response.data);
      }
    }).catch(() => {});
}

/**
 * Get author object and pass it to callback function
 * @since 0.1.0
 * @todo get rid of this function, replace with getCustom
 * @todo make ?_embed configurable - mayble all params
 * @todo potentially create functionality for error
 * @param {number} authorID - Author ID
 * @param {function} callback
 */
export function getAuthor(authorID, callback) {
  axios.get(`${window.wp.root}wp/v2/authors/${authorID}?_embed=1`)
    .then((response) => {
      if (typeof callback === 'function') {
        callback(response.data);
      }
    }).catch(() => {});
}

/**
 * Get archive of custom post objects by custom post object slug
 * @since 0.1.0
 * @todo make ?_embed configurable - maybe all params
 * @todo make archive/single configurable
 * @todo potentially create functionality for error
 * @param {string} slug - Slug of custom post type - f.e. 'post'
 * @param {function} callback - Callback function
 */
export function getCustom(slug, callback) {
  axios.get(`${window.wp.root}wp/v2/${slug}/?_embed=1&per_page=100`)
    .then((response) => {
      if (typeof callback === 'function') {
        callback(response.data);
      }
    }).catch(() => {});
}

/**
 * Get user and set it to local property author
 * @since 0.1.0
 * @todo replace local author property with callback function
 * @todo potentially create functionality for error
 * @param {number} userId - User ID
 */
export function getUser(userId) {
  axios.get(`${window.wp.root}wp/v2/users/${userId}`)
    .then((response) => {
      this.author = response.data;
    }).catch(() => {});
}

/**
 * Get page by $route.meta.postId and pass it to callback function
 * @since 0.1.0
 * @todo replace $route.meta.postId with param
 * @todo remove local cache - potentially move to global cache
 * @todo potentially create functionality for error
 * @param {function} callback - Callback function
 */
export function getPage(callback) {
  const cachekey = `getPage${this.$route.meta.postId}`;

  if (window.Cache.has(cachekey)) {
    if (typeof callback === 'function') {
      callback(window.Cache.get(cachekey));
      return;
    }
  }

  axios.get(`${window.wp.root}wp/v2/pages/${this.$route.meta.postId}`).then((response) => {
    if (typeof callback === 'function') {
      callback(response.data);
    }
    window.Cache.set(cachekey, response.data);
  }).catch(() => {});
}

/**
 * Get posts and set them to local property posts
 * @since 0.1.0
 * @todo replace assigning to property posts with callback function
 * @todo eventually merge single and archive call
 * @todo potentially create functionality for error
 */
export function getPosts() {
  axios.get(`${window.wp.root}wp/v2/posts`).then((response) => {
    this.posts = response.data;
  }).catch(() => {
  });
}

/**
 * Get pages and set them to local property pages
 * @since 0.1.0
 * @todo replace assigning to property pages with callback function
 * @todo eventually merge single and archive call
 */
export function getPages() {
  axios.get(`${window.wp.root}wp/v2/pages`).then((response) => {
    this.pages = response.data;
  }, () => {});
}

/**
 * Pass results of search to callback function
 * @since 0.1.0
 * @todo potentially create functionality for error
 * @param {string} term - Search term
 * @param {function} callback - Callback function
 */
export function getSearch(term, callback) {
  axios.get(`${window.wp.root}wp/v2/posts?search=${term}`).then((response) => {
    if (typeof callback === 'function') {
      callback(response.data);
    }
  }).catch(() => {
  });
}

/**
 * Get all sidebars and pass them to callback function
 * @since 0.1.0
 * @todo review api call on the endpoint
 * @todo potentially create functionality for error
 * @todo get this call working without plugin
 * @param {function} callback - Callback function
 */
export function getSidebars(callback) {
  axios.get(`${window.wp.root}api/sidebars`).then((response) => {
    if (typeof callback === 'function') {
      callback(response.data);
    }
  }).catch(() => {
  });
}

/**
 * Get single sidebar by sidebar ID and pass it to callback function
 * @since 0.1.0
 * @todo review api call on the endpoint
 * @todo potentially create functionality for error
 * @todo get this call working without plugin
 * @param {number} siderbarId - Sidebar ID
 * @param {function} callback - Callback function
 */
export function getSidebar(sidebarId, callback) {
  axios.get(`${window.wp.root}wp-json/wp-rest-api-sidebars/v1/sidebars/${sidebarId}`).then((response) => {
    if (typeof callback === 'function') {
      callback(response.data);
    }
  }).catch(() => {
  });
}

/**
 * Cache if element has class
 * @since 0.1.0
 * @todo refactor - use full param names
 * @param {object} ele - Element
 * @param {string} cls - CSS Class
 * @returns {boolean}
 */
export function hasClass(ele, cls) {
  return !!ele.className.match(new RegExp(`(\\s|^)${cls}(\\s|$)`));
}

/**
 * Add class to given element
 * @since 0.1.0
 * @todo make eslint compatible
 * @todo consider some return value
 * @param {object} ele - Element
 * @param {string} cls - CSS Class
 */
export function addClass(ele, cls) {
  /* eslint-disable */
  if (!this.hasClass(ele,cls)) ele.className += ` ${cls}`;
}

/**
 * Remove class from given element
 * @since 0.1.0
 * @todo make eslint compatible
 * @todo consider some return value
 * @param {object} ele - Element
 * @param {string} cls - CSS Class
 */
export function removeClass(ele, cls) {
  if (this.hasClass(ele, cls)) {
    const reg = new RegExp(`(\\s|^)${cls}(\\s|$)`);
    /* eslint-disable */
    ele.className = ele.className.replace(reg, ' ');
  }
}

/**
 * Decode unicode string
 * @since 0.1.0
 * @param {string} input - Encoded unicode string
 * @returns {string} string - Decoded string
 */
export function decode(input) {
  return input.replace(
    /&#(\d+);/g,
    (match, number) => String.fromCharCode(number),
  );
}

/**
 * i18n string localization - equivalent to wp __() function
 * @since 0.1.0
 * @param {string} input - f.e.: You accept <a href=":terms_link">terms and agreements</a>
 * @param {array} args - f.e.: {'terms_link': '/terms'}
 * @returns string
 */
export function __(input, args) {
  if (typeof window.lang[input] !== 'undefined') {
    let output = window.lang[input];
    Object.keys(args).forEach((key) => {
      const value = args[key];
      output = output.replace(`:${key}`, value);
    });
    return output;
  }
  return input;
}