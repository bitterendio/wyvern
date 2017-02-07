<style lang="sass">
    @import '../_variables.scss';
</style>

<template>
    <div class="theme-header">
        <div class="site-title">
            <router-link :to="{ path: wp.base_path }">{{ wp.site_name }}</router-link>
        </div>
    </div>
</template>

<script>
export default {
  mounted() {

  },

  data() {
    return {
      wp: window.wp,
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
