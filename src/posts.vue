<style>

</style>

<template>
    <transition name="slide-fade">
        <div class="posts content" v-show="posts.length">
            <Post v-for="post in posts" :post="post"></Post>
        </div>
    </transition>
</template>

<script>
    export default {
        mounted() {
            this.getPosts();
        },

        data() {
            return {
                posts: []
            }
        },

        methods: {

        }
    }
</script>