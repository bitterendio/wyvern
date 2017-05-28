<template>
  <div class="page" v-if="page && page.id">
    <h1 v-html="page.title.rendered"></h1>
    <div v-html="page.content.rendered"></div>
  </div>
</template>

<script>
  import { mapActions } from 'vuex';

  export default {
    name: 'page',
    data() {
      return {
      };
    },
    methods: {
      ...mapActions([
        'getPage',
      ]),
      fetchData() {
        this.$store.cacheDispatch('getPage', {
          id: this.$route.meta.id,
        });
      },
    },
    computed: {
      page() {
        return this.$store.getters.getPageById(this.$route.meta.id);
      },
    },
    mounted() {
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
