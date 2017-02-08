import axios from 'axios';

export function getMenuLocation(location, callback) {
  axios.get(`${window.wp.root}wp-api-menus/v2/menu-locations/${location}`)
    .then((response) => {
      if (typeof callback === 'function') {
        callback(response.data);
      }
    })
    .catch(() => {
    });
}

export function url2Slug(url) {
  return url.replace(/^.*\/\/[^/]+/, '');
}

export function getPost(callback) {
  axios.get(`${window.wp.root}wp/v2/posts/${this.$route.meta.postId}`)
    .then((response) => {
      if (typeof callback === 'function') {
        callback(response.data);
      }
    }).catch(() => {
    });
}

export function getCategory(categoryId) {
  axios.get(`${window.wp.root}wp/v2/categories/${categoryId}`)
    .then((response) => {
      this.categories.push(response.data);
    }).catch(() => {
    });
}

export function getTag(tagId, callback) {
  axios.get(`${window.wp.root}wp/v2/tags/${tagId}`)
    .then((response) => {
      if (typeof callback === 'function') {
        callback(response.data);
      }
    }).catch(() => {
    });
}

export function getAuthor(authorID, callback) {
  /* todo: CPT - turn to getPost with callback */
  axios.get(`${window.wp.root}wp/v2/authors/${authorID}?_embed=1`)
    .then((response) => {
      if (typeof callback === 'function') {
        callback(response.data);
      }
    }).catch(() => {
    });
}

export function getCustom(slug, callback) {
  axios.get(`${window.wp.root}wp/v2/${slug}/?_embed=1`)
    .then((response) => {
      if (typeof callback === 'function') {
        callback(response.data);
      }
    }).catch(() => {
    });
}

export function getUser(userId) {
  axios.get(`${window.wp.root}wp/v2/users/${userId}`)
    .then((response) => {
      this.author = response.data;
    }).catch(() => {
    });
}

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
  }).catch(() => {
  });
}

export function getPosts() {
  axios.get(`${window.wp.root}wp/v2/posts`).then((response) => {
    this.posts = response.data;
  }).catch(() => {
  });
}

export function getPages() {
  axios.get(`${window.wp.root}wp/v2/pages`).then((response) => {
    this.pages = response.data;
  }, () => {
  });
}

export function getSearch(term, callback) {
  axios.get(`${window.wp.root}wp/v2/posts?search=${term}`).then((response) => {
    if (typeof callback === 'function') {
      callback(response.data);
    }
  }).catch(() => {
  });
}

export function getSidebars(callback) {
  axios.get(`${window.wp.root}wp-json/wp-rest-api-sidebars/v1/sidebars`).then((response) => {
    if (typeof callback === 'function') {
      callback(response.data);
    }
  }).catch(() => {
  });
}

export function getSidebar(sidebarId, callback) {
  axios.get(`${window.wp.root}wp-json/wp-rest-api-sidebars/v1/sidebars/${sidebarId}`).then((response) => {
    if (typeof callback === 'function') {
      callback(response.data);
    }
  }).catch(() => {
  });
}

export function hasClass(ele, cls) {
  return !!ele.className.match(new RegExp(`(\\s|^)${cls}(\\s|$)`));
}

export function addClass(ele, cls) {
  /* eslint-disable */
  if (!this.hasClass(ele,cls)) ele.className += ` ${cls}`;  // @todo refactor
}

export function removeClass(ele, cls) {
  if (this.hasClass(ele, cls)) {
    const reg = new RegExp(`(\\s|^)${cls}(\\s|$)`);
    /* eslint-disable */
    ele.className = ele.className.replace(reg, ' ');  // @todo refactor
  }
}

/**
 * Decode unicode string
 * @param input
 * @returns string
 */
export function decode(input) {
  return input.replace(
      /&#(\d+);/g,
      (match, number) => String.fromCharCode(number),
  );
}

/**
 * i18n string localization - equivalent to wp __() function
 * @param input f.e.: You accept <a href=":terms_link">terms and agreements</a>
 * @param args f.e.: {'terms_link': '/terms'}
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