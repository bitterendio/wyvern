<style lang="scss">
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

                <h1 class="entry-title" v-if="isSingle">
                  {{ post.title.rendered }}
                </h1>
                <h2 class="entry-title" v-else>
                  <router-link :to="url2Slug(post.link)">
                    {{ post.title.rendered }}
                  </router-link>
                </h2>

                <!-- Entry meta filters -->
                <div class="entry-meta">
                    <span>{{ getDate(post.date) }}</span>

                    <ul class="categories">
                        <li v-for="category in categories"><a v-bind:href="category.link">{{ category.name }}</a></li>
                    </ul>

                    <em>
                        <a v-bind:href="author.link">
                            {{ author.name }}
                        </a>
                    </em>
                </div>

                <div class="entry-content" v-html="post.content.rendered" v-if="isSingle"></div>
                <div class="entry-excerpt" v-html="post.excerpt" v-else></div>

                <hr>

            </div>

        </div>

    </transition>
</template>

<script>

const moment = require('moment');

export default {
  props: {
    object: {
      type: Object,
      default() {
        return {
          id: 0,
          slug: '',
          title: { rendered: '' },
          content: { rendered: '' },
        };
      },
    },
    categories: {
      type: Array,
      default() {
        return [];
      },
    },
    tags: {
      type: Array,
      default() {
        return [];
      },
    },
    author: {
      type: Object,
      default() {
        return {};
      },
    },
  },

  data() {
    return {
      post: {
        type: Object,
        default() {
          return {
            id: 0,
            slug: '',
            title: { rendered: '' },
            content: { rendered: '' },
          };
        },
      },
      assets_path: window.wp.assets_path,
      base_path: window.wp.base_path,
      isSingle: false,
      lang: window.lang,
    };
  },

  mounted() {
    // If post hasn't been passed by prop
    if (!this.object.id) {
      this.getPost((data) => {
        this.post = data;

        // Load author
        this.getAuthor(this.post.author, (response) => {
          this.author = response;
        });

        // Load category
        this.post.categories.forEach((category) => {
          this.getCategory(category);
        });

        // Load tags
        this.post.tags.forEach((tag) => {
          this.getTag(tag, (response) => {
            this.tags.push(response);
          });
        });

        window.eventHub.$emit('page-title', this.post.title.rendered);
        window.eventHub.$emit('track-ga');
      });

      this.isSingle = true;
    } else {
      this.post = this.object;
    }
  },

  updated() {

  },

  methods: {
    getDate(date, format) {
      let localFormat = format;
      if (typeof localFormat === 'undefined') {
        localFormat = 'lll';
      }
      return moment(date).format(localFormat);
    },
  },

  route: {
    canReuse() {
      return false;
    },
  },
};
</script>
