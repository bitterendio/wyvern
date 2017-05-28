<template>
  <div class="hello">
    <ul>
      <li v-for="post in posts">
        {{ post }}
        {{ post.post_title }}
      </li>
    </ul>
  </div>
</template>

<script>
  import { mapGetters } from 'vuex';

  export default {
    name: 'twocols',
    data() {
      return {
      };
    },
    computed: mapGetters({
      posts: 'allPosts',
    }),
    created() {
      this.$store.dispatch('getAllPosts');
    },
  };
</script>

<style>

</style>
