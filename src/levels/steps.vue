<style lang="scss">

  .level-steps {
    display: flex;
  }

  .level-steps__step {
    flex: 1;
    padding-right: 10px;
    padding-left: 10px;
    &:first-child {
      padding-left: 0;
    }
    &:last-child {
      padding-right: 0;
    }
  }

</style>

<template>
  <div class="level level-steps">
    <div v-for="block in level.step" class="level-steps__step">
      <h4 class="level-steps__step__title">{{ block.step_title }}</h4>
      <p class="level-steps__step__description">{{ block.step_description }}</p>
    </div>
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

    data() {
      return {
      };
    },

    created() {
    },

    beforeDestroy() {
    },
  };
</script>
