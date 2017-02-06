<style lang="sass">

</style>

<template>
  <div class="level level-fb_pageplugin">
    <iframe :src="src" width="340" height="500" style="border:none;overflow:hidden" scrolling="no" frameborder="0" allowTransparency="true"></iframe>
  </div>
</template>

<script>
  export default {

    props: ['level'],

    mounted() {

    },

    methods: {

    },

    updated() {

    },

    computed: {
      src() {
        return 'https://www.facebook.com/plugins/page.php?href=' + encodeURI( this.level.page_url ) + '&tabs=timeline&width=340&height=500&small_header=false&adapt_container_width=true&hide_cover=false&show_facepile=true&appId=' + this.level.app_id
      }
    },

    data() {
      return {
      }
    },

    created() {
    },

    beforeDestroy() {
    }
  }
</script>