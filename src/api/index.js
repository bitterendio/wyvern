
const staticRoutes = {
  posts: {
    get: '/static/posts.json',
  },
  menus: {
    find: '/static/menus/{id}.json',
    location: '/static/menus/{location}.json',
  },
};

const wpApiBase = '/wp-json/';

const wpRoutes = {
  posts: {
    get: '',  // wp api url
  },
  menus: {
    find: `${wpApiBase}api/menu/{id}`,
    location: `${wpApiBase}api/menu/location/{location}`,
  },
};

const wpActive = false;

export default wpActive ? wpRoutes : staticRoutes;
