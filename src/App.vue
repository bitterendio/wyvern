<style lang="scss">
  @import './assets/style';
</style>

<template>
  <div id="app">

    <div class="template-wrapper">

      <div class="cols">

        <div class="sidebar">
          <h1>Wyvern testing app</h1>
          <router-link to="/" class="block">
            Test
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
export default {
  name: 'app',
};
</script>

<style>
</style>
