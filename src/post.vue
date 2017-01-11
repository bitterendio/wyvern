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
                <h2 class="entry-title" v-else><router-link :to="{ path: base_path + post.slug }">{{ post.title.rendered }}</router-link></h2>

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

                <hr>

                <ul class="post-gallery">
                    <li v-for="image in post.acf.obrazky">
                        <a v-if="image.obrazek.sizes" :href="image.obrazek.sizes.large" class="gallery" target="_blank">
                            <img :src="image.obrazek.sizes.thumbnail" :alt="image.obrazek.caption">
                        </a>
                    </li>
                </ul>

                <div v-if="post.acf.souvisejici">
                    <h4>Štítky</h4>
                    <ul class="list-post list-post-tags">
                        <li v-for="tag in tags">
                            <!-- <a v-if="tag.name" :href="tag.link"> -->
                                {{ tag.name }}
                            <!-- </a> -->
                        </li>
                    </ul>
                </div>

                <div v-if="post.acf.souvisejici">
                    <h4>Související</h4>
                    <ul>
                        <li v-for="souvisejici in post.acf.souvisejici">
                            <a v-if="souvisejici.prispevek.post_title" :href="souvisejici.prispevek.guid">
                                {{ souvisejici.prispevek.post_title }}
                            </a>
                        </li>
                    </ul>
                </div>

                <h4>Sdílet</h4>
                <ul>
                    <li><a :href="shareLink('facebook', post.link)">Facebook</a></li>
                    <li><a :href="shareLink('twitter', post.link)">Twitter</a></li>
                </ul>

                <div v-if="post.acf.literarni_zdroje">
                    <h4>Zdroje</h4>
                    <ul class="list-post list-post-sources">
                        <li v-for="zdroj in post.acf.literarni_zdroje">
                            {{ zdroj.zdroj }}
                        </li>
                    </ul>
                </div>

                <div v-for="author in authors" class="post-author">
                    <div v-if="showAuthorImage(author)" class="post-author-image">
                        <a :href="author._embedded['wp:featuredmedia'][0].media_details.sizes.full.source_url" class="author-image">
                            <img :src="author._embedded['wp:featuredmedia'][0].media_details.sizes.thumbnail.source_url">
                        </a>
                    </div>
                    <div class="post-author-about">
                        <h5>{{ author.acf.jmeno }}</h5>
                        <div v-html="author.acf.kratky_popis"></div>
                    </div>
                </div>

            </div>
        </div>
    </transition>
</template>

<script>

    var $ = require('jquery');
    var juliaBox = require('juliabox');

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
            },
            authors: {
                type: Array,
                default() {
                    return [];
                }
            },
            author: {
                type: Object,
                default() {
                    return {};
                }
            }
        },

        mounted() {
            // If post hasn't been passed by prop
            if (!this.post.id) {
                var self = this;
                this.getPost(function(data){
                    self.post = data;

                    // Load category information
                    self.post.categories.forEach(function(category){
                        self.getCategory(category);
                    });

                    self.post.tags.forEach(function(tag){
                        self.getTag(tag, function(data){
                            self.tags.push(data);
                        });
                    });

                    self.post.acf.autori.forEach(function (author) {
                        self.getAuthor(author.autor.ID, function (data) {
                            self.authors.push(data)
                        });
                    });

                    self.getUser(self.post.author);

                    window.eventHub.$emit('page-title', self.post.title.rendered);
                    window.eventHub.$emit('track-ga');
                });

                this.isSingle = true;
            }
        },

        updated() {

            var self = this;

            $(function() {

                if ( self.timeout != 'undefined' )
                    clearTimeout(self.timeout);

                self.timeout = setTimeout(function(){

                    $('a.gallery').juliaBox({
                        videoAutoplay: true,
                        i18n: {
                            close: 'Zavřít',
                            previous: 'Předchozí',
                            next: 'Další'
                        },
                    });

                    $('a.author-image').juliaBox({
                        videoAutoplay: true,
                        i18n: {
                            close: 'Zavřít',
                            previous: 'Předchozí',
                            next: 'Další'
                        },
                    });
                }, 600);
            });
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
            shareLink: function(provider, link, via) {

                var base_url = '';
                var output = '';

                if ( provider == 'facebook' ) {
                    base_url = 'https://www.facebook.com/sharer/sharer.php?u=';
                }

                if ( provider == 'twitter' ) {
                    base_url = 'https://twitter.com/intent/tweet?via=encyklopedie_m&url=';
                }

                output = base_url + encodeURIComponent(link);

                return output;
            },
            showAuthorImage: function(author) {

                if ( typeof author == 'undefined' ) return false;
                if ( typeof author._embedded == 'undefined') return false;
                if ( typeof author._embedded['wp:featuredmedia'] == 'undefined') return false;
                if ( typeof author._embedded['wp:featuredmedia'][0] == 'undefined') return false;
                if ( typeof author._embedded['wp:featuredmedia'][0].media_details == 'undefined') return false;
                if ( typeof author._embedded['wp:featuredmedia'][0].media_details.sizes == 'undefined') return false;
                if ( typeof author._embedded['wp:featuredmedia'][0].media_details.sizes.thumbnail == 'undefined') return false;
                if ( typeof author._embedded['wp:featuredmedia'][0].media_details.sizes.thumbnail.source_url == 'undefined') return false;

                return true
            }
        },

        route: {
            canReuse() {
                return false;
            }
        }
    }
</script>