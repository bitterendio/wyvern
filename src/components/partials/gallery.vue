<style lang="scss">
  .product__image__preview {
    cursor: pointer;
    border: 1px solid #ccc;
    text-align: center;

    &.active,
    &:hover {
      border: 1px solid #999;
    }
  }
</style>

<template>
  <div class="gallery">
    <img  v-for="(image, key) in images.large"
          v-show="key === current"
          :src="image[0]"
          :width="image[1]"
          :height="image[2]"
          class="product__image"
          @click="showLightboxAtPosition(key)">
    <div class="cols">
      <div v-for="(image, key) in images.thumbnail"
           class="product__image__preview"
           :class="[{'active' : key === current}]"
            @click="setCurrent(key)">
        <img :src="image[0]"
             :width="image[1]"
             :height="image[2]"
             class="product__image__thumbnail">
      </div>
    </div>
  </div>
</template>

<script>
  import { mapMutations } from 'vuex';

  export default {
    name: 'gallery',
    props: {
      images: {
        default: [],
      },
    },
    methods: {
      setCurrent(key) {
        this.current = key;
      },
      showLightboxAtPosition(position) {
        this.setLightboxPosition(position);
        this.showLightbox(true);
      },
      ...mapMutations([
        'showLightbox',
        'setLightboxPosition',
      ]),
    },
    data() {
      return {
        current: 0,
      };
    },
  };
</script>

<style>

</style>
