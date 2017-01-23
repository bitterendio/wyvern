<style lang="sass">
    @import '../_variables.scss';
</style>

<template>
    <div class="theme-header">
        <div class="site-title">
            <router-link :to="{ path: wp.base_path }" v-show="!wp.display.show_logo">{{ wp.site_name }}</router-link>
            <router-link :to="{ path: wp.base_path }" v-show="wp.display.show_logo" class="block">
                <img v-bind:src="wp.assets_path + '/images/logo.svg'"
                     v-bind:alt="wp.site_name"
                     height="40">
            </router-link>
        </div>
    </div>
</template>

<script>
    export default {
        mounted() {

        },

        data() {
            return {
                wp: window.wp
            }
        },

        methods: {

        },

        watch: {

        },

        // Create listeners
        created() {

        },

        // It's good to clean up event listeners before
        // a component is destroyed.
        beforeDestroy() {

        },
    }
</script>