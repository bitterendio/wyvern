<style>
  .footer .container {
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .footer .container > * {
    flex: 1;
  }
</style>

<template>
  <footer class="theme-footer footer">
    <div class="container">
      <ul class="nav">
        <li v-for="item in menu">
          <router-link :to="{ path: url2Slug(item.url) }">{{ item.title }}</router-link>
        </li>
      </ul>
      <p>
        {{ footer_text }}
      </p>
    </div>
  </footer>
</template>

<script>
    export default {
        mounted() {
            var self = this;
            this.getMenuLocation('footer', function(data){
                self.menu = data;
            });
        },

        data() {
            return {
                assets_path: wp.assets_path,
                base_path: wp.base_path,
                site_name: wp.site_name,
                lang: wp.lang,
                menu: [],
                footer_text: wp.extras.footer_text
            }
        },

        methods: {

        }
    }
</script>