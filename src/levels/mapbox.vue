<style>

</style>

<template>
    <div class="level level-mapbox">
        <div id="map"></div>
    </div>
</template>

<script>
import MapboxComponent from '../components/mapbox/mapbox.vue';

export default {

  extends: MapboxComponent,

  props: ['level', 'bound1', 'bound2'],

  methods: {
    initData() {
      const map = this.getMap();

      map.on('load', () => {
        // place for custom logic
      });
    },
  },

  data() {
    return {
      container: 'map',
      token: window.config.extras.mapbox_key,
      style: window.config.extras.mapbox_style,
      center: [14.453131, 50.096297],
      zoom: 3,
    };
  },
};
</script>
