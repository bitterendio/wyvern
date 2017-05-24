<template>
  <ul v-if="menu">
    <li v-for="item in menu.items">
      <router-link :to="url2Slug(item.url)">
        {{ item.title }}
      </router-link>
    </li>
  </ul>
</template>

<script>
  import { mapActions } from 'vuex';

  export default {
    props: {
      id: {
        default: false,
      },
      location: {
        default: false,
      },
    },
    computed: {
      menu() {
        return this.$store.getters.getMenuByLocation(this.location);
      },
    },
    methods: {
      ...mapActions([
        'getMenu',
      ]),
    },
    created() {
      if (this.location !== false) {
        this.$store.cacheDispatch('getMenu', {
          location: this.location,
        });
      }
    },
  };
</script>
