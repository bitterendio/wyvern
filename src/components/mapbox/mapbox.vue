<template>
    <div class="mapbox">
        <div id="map"></div>
    </div>
</template>

<script>
  export default {

    props: ['bound1', 'bound2'],

    mounted() {
      const self = this;

      const scripts = [
        'https://api.mapbox.com/mapbox-gl-js/v0.28.0/mapbox-gl.js',
      ];


      const styles = [
        'https://api.mapbox.com/mapbox-gl-js/v0.28.0/mapbox-gl.css',
      ];

      this.loadStyles(styles);
      this.loadScripts(scripts, () => {
        window.mapboxgl.accessToken = self.token;

        const mapConfiguration = {
          container: self.container,
          style: self.style,
          center: self.center,
          zoom: self.zoom,
        };

        /* eslint-disable */
        window.map = new window.mapboxgl.Map(mapConfiguration);
        self.initData();
      });
    },

    methods: {
      loadScripts(scripts, callback) {
        const total = scripts.length;
        let loaded = 0;

        Object.keys(scripts).forEach((key) => {
          const script = scripts[key];
          this.loadScript(script).onload = function () {
            loaded += 1;
            if (loaded === total && typeof callback === 'function') {
              callback();
            }
          };
        });
      },

      loadScript(url) {
        const element = document.createElement('script');
        element.setAttribute('src', url);
        document.head.appendChild(element);
        return element;
      },

      loadStyles(styles, callback) {
        const total = styles.length;
        let loaded = 0;

        Object.keys(styles).forEach((key) => {
          loaded += 1;
          const style = styles[key];
          this.loadStyle(style);

          if (loaded === total && typeof callback === 'function') {
            callback();
          }
        });
      },

      loadStyle(url) {
        const element = document.createElement('link');
        element.type = 'text/css';
        element.rel = 'stylesheet';
        element.href = url;
        document.head.appendChild(element);
        return element;
      },

      initData() {

      },

      getMap() {
        return window.map;
      },
    },

    data() {
      return {
        container: 'map',
        token: '{MAPBOX_TOKEN_HERE}',
        style: '{MAPBOX_STYLE_HERE}',
        center: [14.453131, 50.096297],
        zoom: 5,

        base: true,
        directions: false,
      };
    },
  };
</script>
