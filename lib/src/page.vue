<style>

</style>

<template>
    <div class="page content" :class="[page.slug, page.template]">

        <div v-show="page.id">

            <component is="levels" :object="page"></component>

            <div class="container">

                <h1 class="entry-title">{{ page.title.rendered }}</h1>

                <div class="entry-content" v-html="page.content.rendered">
                </div>
            </div>

        </div>

    </div>
</template>

<script>

    export default {
        mounted() {
            var self = this;
            this.getPage(function(data){
                self.page = data;
                window.eventHub.$emit('page-title', self.page.title.rendered);
                window.eventHub.$emit('track-ga');
            });
        },

        data() {
            return {
                page: {
                    id: 0,
                    slug: '',
                    title: { rendered: '' },
                    content: { rendered: '' }
                },
                lang: wp.lang
            }
        },

        methods: {

        },

        route: {
            canReuse() {
                return false;
            }
        }
    }
</script>