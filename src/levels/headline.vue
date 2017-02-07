<style lang="sass">

</style>

<template>
  <div class="level level-headline" v-html="headline">
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
      headline() {
        return `<${this.level.priority} class="level-headline--${this.level.priority}">${this.level.text}</${this.level.priority}>`;
      },
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
