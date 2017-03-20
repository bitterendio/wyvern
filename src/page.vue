<style>
</style>

<template>
  <div class="page content" :class="[page.slug, page.template]">

    <div v-show="page.id" class="container cols">

      <sidebar></sidebar>

      <div class="main">

        <h1 class="entry-title">{{ page.title.rendered }}</h1>

        <div class="entry-content" v-html="page.content.rendered">
        </div>

        <component is="levels" :object="page"></component>

      </div>

    </div>

  </div>
</template>

<script>

  export default {
    mounted() {
      const vm = this;
      this.getPage((data) => {
        vm.page = data;
        window.eventHub.$emit('page-title', vm.page.title.rendered);
        window.eventHub.$emit('track-ga');
      });
    },

    data() {
      return {
        page: {
          id: 0,
          slug: '',
          title: { rendered: '' },
          content: { rendered: '' },
        },
        lang: window.lang,
        config: window.config,
      };
    },

    methods: {

    },

    route: {
      canReuse() {
        return false;
      },
    },
  };
</script>
