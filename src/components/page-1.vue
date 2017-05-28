<template>
  <div class="hello">
    {{ msg }}
  </div>
</template>

<script>
  import { mapGetters } from 'vuex';

  export default {
    name: 'page1',
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
