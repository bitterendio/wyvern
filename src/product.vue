<style>

</style>

<template>
  <div class="page content" :class="[product.slug, product.template]">

    <div v-show="product.id">

      <component is="levels" :object="product"></component>

      <div class="container">

        <h1 class="entry-title">{{ product.title }}</h1>

        <div class="entry-content" v-html="product.content">
        </div>

      </div>

    </div>

  </div>
</template>

<script>

  export default {
    mounted() {
      var vm = this
      window.wyvern.http.get(wp.root + 'api/products/' + vm.$route.meta.postId).then((response) => {
        vm.product = response.data
      })
    },

    data() {
      return {
        product: {
          id: 0
        },
        lang: wp.lang
      }
    },

    methods: {

    },

    route: {
      canReuse() {
        return false;
      }
    }
  }
</script>