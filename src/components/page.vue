<template>
  <div class="page" v-if="page.id">
    <h1 v-html="page.title.rendered"></h1>
    <div v-html="page.content.rendered"></div>
  </div>
</template>

<script>
  import { mapGetters } from 'vuex';

  export default {
    name: 'page',
    data() {
      return {
      };
    },
    methods: {
      fetchData() {
        this.$store.dispatch('getPage', {
          id: this.$route.meta.id,
        });
      },
    },
    computed: mapGetters({
      page: 'currentPage',
    }),
    mounted() {
      this.fetchData();
    },
    watch: {
      $route: 'fetchData',
    },
  };
</script>

<style>

</style>
