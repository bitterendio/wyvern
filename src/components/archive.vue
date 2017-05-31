<template>
  <div class="archive">
    <h1>Archive</h1>
    <ul>
      <li v-for="post in posts">
        <router-link :to="url2Slug(post.link)">
          {{ post.title.rendered }}
        </router-link>
      </li>
    </ul>
  </div>
</template>

<script>
  export default {
    data() {
      return {
      };
    },
    computed: {
      posts() {
        return this.$store.getters.getQueryArchiveByType(this.$route.meta.type);
      },
    },
    created() {
      this.$store.cacheDispatch('getQuery', {
        post_type: this.$route.meta.type,
      });
    },
  };
</script>

<style>

</style>
