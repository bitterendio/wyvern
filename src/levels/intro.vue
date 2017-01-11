<style>
  .level-intro {
    position: relative;
  }

  .level-intro-content {
    position: relative;
    z-index: 1;
  }

  .level-intro .embed-container {
    position: relative;
    padding-bottom: 56.25%;
    height: 0;
    overflow: hidden;
    max-width: 100%;
  }
  .level-intro .embed-container iframe, .level-intro .embed-container object, .level-intro .embed-container embed {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
  }

  .level-intro-background_image {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-size: cover;
    pointer-events: none;
    z-index: 0;
  }

  .level-intro.opened .level-intro-btn {
    display: none;
  }
</style>

<template>
  <div class="level level-intro" :class="{ opened: show_video }">
    <div class="level-intro-content">
      <h1 class="level-intro-title">{{ level.title }}</h1>
      <h2 class="level-intro-subtitle">{{ level.subtitle }}</h2>

      <transition name="fade">
        <div class="embed-wrapper" v-if="level.video && show_video">
          <div class="embed-container">
            <iframe :src="level.video" frameborder="0" webkit-allow-full-screen mozallowfullscreen allowfullscreen></iframe>
          </div>
        </div>
      </transition>

      <button type="button" @click="playVideo()" class="level-intro-btn">
        {{ level.button_label }}
      </button>
    </div>

    <div v-if="level.background_image" class="level-intro-background_image" :style="{ backgroundImage: 'url(' + level.background_image.url + ')' }"></div>
  </div>
</template>

<script>
  export default {

    props: ['level'],

    methods: {
      playVideo() {
        this.show_video = !this.show_video;
        window.eventHub.$emit('toggle-video', this.show_video);
      }
    },

    data() {
      return {
        assets_path: wp.assets_path,
        base_path: wp.base_path,
        site_name: wp.site_name,
        lang: wp.lang,
        show_video: false
      }
    }
  }
</script>