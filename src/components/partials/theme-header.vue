<style lang="scss">
  .search-results {
    list-style-type: none;
    padding-left: 0;
  }

  .search-results__title {
    margin-top: 0;
    margin-bottom: 0.5em;
  }

  .search-results__excerpt p {
    margin-top: 0.5em;
  }

  .search-results__result {
    margin-bottom: 1em;
  }
</style>

<template>
  <header class="header">
    <div class="cols">
      <div>
        <input v-model="term" placeholder="Search">
        <button @click="clearSearch" v-if="term !== ''">Clear search</button>
      </div>
      <div class="text-right">
        <button @click="showResults" v-if="results.length > 0 && !showResultList">Show results</button>
        <button @click="hideResults" v-else-if="results.length > 0 && showResultList">Hide results</button>
      </div>
    </div>
    <ul class="search-results" v-if="showResultList">
      <li v-for="result in results" class="search-results__result">
        <h3 class="search-results__title">
          <router-link :to="url2Slug(result.link)">
            {{ result.title.rendered }}
          </router-link>
        </h3>
        <cite>{{ result.link }}</cite>
        <div v-html="result.excerpt.rendered" class="search-results__excerpt"></div>
      </li>
    </ul>
  </header>
</template>

<script>
  import { mapActions, mapGetters } from 'vuex';

  export default {
    props: {
    },
    data() {
      return {
        showResultList: false,
        term: '',
      };
    },
    computed: mapGetters({
      results: 'allResults',
    }),
    methods: {
      ...mapActions([
        'setTerm',
      ]),
      /* eslint func-names: 0 */
      debouncedSetTerm: _.debounce(function (value) {
        this.setTerm(value);
        if (value !== '') {
          this.showResults();
        } else {
          this.hideResults();
        }
      }, 1000),
      clearSearch() {
        this.term = '';
      },
      hideResults() {
        this.showResultList = false;
      },
      showResults() {
        this.showResultList = true;
      },
      toggleResults() {
        this.showResultList = !this.showResultList;
      },
    },
    watch: {
      term: 'debouncedSetTerm',
      $route: 'hideResults',
    },
  };
</script>
