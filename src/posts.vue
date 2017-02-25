<style>

</style>

<template>
    <div class="cols container">
        <div class="main posts content" v-show="posts.length">
            <Post v-for="post in posts" :object="post"></Post>
        </div>
        <ul class="sidebar" v-html="sidebar.rendered">
        </ul>
    </div>
</template>

<script>
  export default {
    mounted() {
      this.getPosts();

      this.getSidebars((data) => {
        if (typeof data['primary-sidebar'] !== 'undefined') {
          this.sidebar = data['primary-sidebar'];
        }
      });
    },

    data() {
      return {
        posts: [],
        sidebar: {},
      };
    },

    methods: {

    },
  };
</script>
