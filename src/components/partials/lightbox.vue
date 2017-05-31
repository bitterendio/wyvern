<style lang="scss">
  .lightbox-wrapper {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 9999;
    .background {
      background-color: rgba(0, 0, 0, 0.8);
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
    }
    .center {
      margin: auto;
      position: absolute;
      top: 0;
      left: 0;
      bottom: 0;
      right: 0;
      width: 60%;
    }
    .left-arrow,
    .right-arrow {
      display: block;
      width: 60px;
      height: 60px;
      line-height: 60px;
      text-align: center;
    }
    .left-arrow {
      position: absolute;
      left: 15%;
      top: 50%;
      color: #fff;
      cursor: pointer;
    }
    .right-arrow {
      position: absolute;
      right: 15%;
      top: 50%;
      color: #fff;
      cursor: pointer;
    }
    .close {
      position: absolute;
      right: 15%;
      top: 5%;
      color: #fff;
      cursor: pointer;
    }
  }
</style>

<template>
  <div class="lightbox-wrapper" v-if="show">

    <div class="background"
         @click="close"></div>

    <div class="close"
         @click="close">
      X
    </div>

    <div class="left-arrow"
         @click="previous"
         v-show="showPreviousArrow">
      &laquo;
    </div>

    <img :src="currentImage[0]"
         class="center">

    <div class="right-arrow"
         @click="next"
         v-show="showNextArrow">
      &raquo;
    </div>

    <div class="captions">

    </div>
  </div>
</template>

<script>
  import { mapMutations, mapGetters } from 'vuex';

  export default {
    props: {
      images: {
        default: [],
      },
      first: {
        default: 0,
      },
    },
    data() {
      return {
        size: 'full',
      };
    },
    computed: {
      ...mapGetters({
        show: 'showLightbox',
        current: 'getLightboxPosition',
      }),
      currentImage() {
        return this.images[this.size][this.current];
      },
      showPreviousArrow() {
        return this.availablePrevious();
      },
      showNextArrow() {
        return this.availableNext();
      },
    },
    methods: {
      ...mapMutations([
        'showLightbox',
        'setLightboxPosition',
      ]),
      max() {
        return this.images[this.size].length - 1;
      },
      close() {
        this.showLightbox(false);
      },
      next() {
        if (this.availableNext()) {
          this.setLightboxPosition(this.current + 1);
        }
      },
      previous() {
        if (this.availablePrevious()) {
          this.setLightboxPosition(this.current - 1);
        }
      },
      availablePrevious() {
        return this.current > 0;
      },
      availableNext() {
        return this.current < this.max();
      },
    },
    mounted() {
      this.current = this.first;

      document.addEventListener('keydown', (event) => {
        const localEvent = event || window.event;
        if (localEvent.keyCode === 37) {
          // Left arrow
          this.previous();
        }
        if (localEvent.keyCode === 39) {
          // Right arrow
          this.next();
        }
      });
    },
    watch: {
    },
  };
</script>
