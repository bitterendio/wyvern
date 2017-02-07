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
                    <span>{{ post.date | moment("lll") }}</span>

                    <ul class="categories">
                        <li v-for="category in categories"><a v-bind:href="category.link">{{ category.name }}</a></li>
                    </ul>

                    <em>
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

    mounted() {
      // If post hasn't been passed by prop
      if (!this.post.id) {
        const self = this;
        this.getPost((data) => {
          self.post = data;

          // Load author
          self.getAuthor(self.post.author, (response) => {
            self.author = response;
          });

          // Load category
          self.post.categories.forEach((category) => {
            self.getCategory(category);
          });

          // Load tags
          self.post.tags.forEach((tag) => {
            self.getTag(tag, (response) => {
              self.tags.push(response);
            });
          });

          window.eventHub.$emit('page-title', self.post.title.rendered);
          window.eventHub.$emit('track-ga');
        });

        this.isSingle = true;
      }
    },

    updated() {

    },

    data() {
      return {
        assets_path: window.wp.assets_path,
        base_path: window.wp.base_path,
        isSingle: false,
        lang: window.lang,
      };
    },

    methods: {

    },

    route: {
      canReuse() {
        return false;
      },
    },
  };
</script>
