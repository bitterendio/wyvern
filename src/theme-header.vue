<style lang="scss">
    /* @import '../style'; */
</style>

<template>
    <div class="theme-header">
        <div class="container">
          <!-- Site brand -->
          <div class="site-title">
              <router-link :to="{ path: wp.base_path }">{{ wp.site_name }}</router-link>
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
      wp: window.wp,
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
