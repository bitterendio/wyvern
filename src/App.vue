<style lang="scss">
  @import './assets/style';
</style>

<template>
  <div id="app">
    <h1>Wyvern testing app</h1>
    <router-link to="/">
      <img src="../logo.png">
    </router-link>
    <menu-location :location="'primary'"></menu-location>
    <menu-location :location="'footer'"></menu-location>
    <menu-location :location="'primary'"></menu-location>
    <router-view></router-view>
  </div>
</template>

<script>
export default {
  name: 'app',
};
</script>

<style>
</style>
