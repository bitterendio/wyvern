<style lang="scss">
    @import '../style';
</style>

<template>
    <div class="theme-header">
        <div class="container">
          <!-- Site brand -->
          <div class="site-title">
              <router-link :to="{ path: config.base_path }">{{ config.site_name }}</router-link>
          </div>

          <!-- Navigation -->
          <ul class="nav">
              <li v-for="item in menu">
                  <router-link :to="{ path: url2Slug(item.url) }">{{ item.title }}</router-link>
              </li>
          </ul>
        </div>
    </div>
</template>

<script>
export default {
  mounted() {
    this.getMenuLocation('primary', (response) => {
      this.menu = response;
    });
  },

  data() {
    return {
      config: window.config,
      menu: [],
    };
  },

  methods: {

  },

  watch: {

  },

  created() {

  },

  beforeDestroy() {

  },
};
</script>
