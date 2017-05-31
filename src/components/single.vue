<template>
  <div :class="[$route.meta.type]" v-if="post && post.id">
    <h1 v-html="post.title.rendered"></h1>
    <div v-html="post.content.rendered"></div>
  </div>
</template>

<script>
  export default {
    data() {
      return {
      };
    },
    methods: {
      fetchData() {
        this.$store.cacheDispatch('getQuery', {
          post_type: this.$route.meta.type,
          id: this.$route.meta.id,
        });
      },
    },
    computed: {
      post() {
        return this.$store.getters.getQueryById(this.$route.meta.id);
      },
    },
    created() {
      this.fetchData();
      this.title();
    },
    watch: {
      $route: ['fetchData', 'title'],
    },
  };
</script>

<style>

</style>
