<style lang="scss">
  @import './assets/style';
</style>

<template>
  <div id="app">

    <div class="template-wrapper">

      <div class="cols">
        <div class="sidebar">
          <h1>{{ config.site_name }}</h1>
          <p>{{ config.site_desc }}</p>
          <router-link to="/" class="block">
            {{ config.site_name }}
          </router-link>
          <menu-location :location="'primary'" class="nav"></menu-location>
        </div>

        <div class="main">
          <theme-header></theme-header>
          <router-view></router-view>
          <footer class="footer text-center">
            <menu-location :location="'footer'" class="nav nav-footer"></menu-location>
          </footer>
        </div>

      </div>

    </div>

  </div>
</template>

<script>
import './../build/wyvern';
import EventBus from './event-bus';

export default {
  name: 'app',
  data() {
    return {
      config: window.config,
    };
  },
  created() {
    EventBus.$on('config', (data) => {
      this.config = data;
    });
  },
};
</script>

<style>
</style>
