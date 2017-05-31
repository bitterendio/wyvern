<style lang="scss">
.product__image {
  max-width: 100%;
  height: auto;
}
</style>

<template>
  <div class="product" v-if="product && product.id">
    <div class="cols">
      <div>
        <gallery :images="images"></gallery>
        <lightbox :images="images"></lightbox>
      </div>
      <div>
        <h1 v-html="product.title"></h1>
        <div v-html="product.content"></div>
      </div>
    </div>
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
        this.$store.cacheDispatch('getProduct', {
          id: this.$route.meta.id,
        });
      },
    },
    computed: {
      product() {
        return this.$store.getters.getProductById(this.$route.meta.id);
      },
      images() {
        const sizes = ['thumbnail', 'medium', 'large', 'full'];
        const output = {};
        sizes.forEach((size) => {
          output[size] = [];
          if (this.product.images[size]) {
            output[size].push(this.product.images[size]);
          }
          this.product.gallery[size].forEach((image) => {
            output[size].push(image);
          });
        });
        return output;
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
