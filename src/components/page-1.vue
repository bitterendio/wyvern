<template>
  <div class="hello">
    {{ msg }}
  </div>
</template>

<script>
  import { mapActions } from 'vuex';

  export default {
    name: 'page1',
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
