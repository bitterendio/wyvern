<style>
    .entry-meta {
        opacity: 0.5;
    }
    ul.categories {
        list-style-type: none;
        padding-left: 0;
    }
    ul.categories li:after {
        content: ", ";
        display: inline;
    }
    ul.categories li:last-child:after {
        display: none;
    }
</style>

<template>
    <transition name="slide-fade">

        <div class="post content" v-if="post.id" :class="post.slug">

            <component is="levels" :object="post"></component>

            <div class="container">

                <h1 class="entry-title" v-if="isSingle">{{ post.title.rendered }}</h1>

                <!-- Entry meta filters -->
                <div class="entry-meta">
                    <span class="hidden">{{ post.date | moment("lll") }}</span>

                    <ul class="categories hidden">
                        <li v-for="category in categories"><a v-bind:href="category.link">{{ category.name }}</a></li>
                    </ul>

                    <em class="hidden">
                        <a v-bind:href="author.link">
                            {{ author.name }}
                        </a>
                    </em>

                </div>

                <hr>

                <div class="entry-content" v-html="post.content.rendered">
                </div>

            </div>

        </div>

    </transition>
</template>

<script>

    export default {
        props: {
            post: {
                type: Object,
                default() {
                    return {
                        id: 0,
                        slug: '',
                        title: { rendered: '' },
                        content: { rendered: '' }
                    }
                }
            },
            categories: {
                type: Array,
                default() {
                    return [];
                }
            },
            tags: {
                type: Array,
                default() {
                    return [];
                }
            }
        },

        mounted() {
            // If post hasn't been passed by prop
            if (!this.post.id) {
                var self = this;
                this.getPost(function(data){

                    self.post = data

                    // Load category information
                    self.post.categories.forEach(function(category) {
                        self.getCategory(category)
                    });

                    self.post.tags.forEach(function(tag) {
                        self.getTag(tag, function(data) {
                            self.tags.push(data)
                        })
                    })

                    window.eventHub.$emit('page-title', self.post.title.rendered)
                    window.eventHub.$emit('track-ga')
                });

                this.isSingle = true;
            }
        },

        updated() {
            var self = this;
        },

        data() {
            return {
                assets_path: wp.assets_path,
                base_path: wp.base_path,
                isSingle: false,
                lang: wp.lang
            }
        },

        methods: {

        },

        route: {
            canReuse() {
                return false
            }
        }
    }
</script>