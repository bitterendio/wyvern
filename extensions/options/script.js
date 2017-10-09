var vm = new Vue({
  el: '#wyvern-options',
  data: function() {
    return {
      loaded: false,
      options: wyvernOptions.options ? wyvernOptions.options : [],
      nonce: wyvernOptions.nonce,
      root: wyvernOptions.root,
    };
  },
  created: function() {
    this.loaded = true;
  },
  methods: {
    update: function() {
      var self = this;
      jQuery.ajax( {
        url: '/wp-json/wyvern/v1/options/update/',
        method: 'POST',
        beforeSend: function ( xhr ) {
          xhr.setRequestHeader( 'X-WP-Nonce', self.nonce );
        },
        data: {
          'options': self.options
        }
      } ).done( function ( response ) {
        self.options = response;
      } );
    },
    add: function() {
      this.$set(this.options, this.options.length, {
        name: '',
        value: '',
        slug: '',
        private: false,
      });
    },
    slugify: function(option) {
      const slug =  option.name.toString().toLowerCase()
          .replace(/\s+/g, '_')           // Replace spaces with -
          .replace(/[^\w\-]+/g, '')       // Remove all non-word chars
          .replace(/\-\-+/g, '_')         // Replace multiple - with single -
          .replace(/^-+/, '')             // Trim - from start of text
          .replace(/-+$/, '');            // Trim - from end of text
      option.slug = slug;
    },
    togglePrivate: function(option, index) {
      option.private = !option.private;
      this.$set(this.options, index, option);
    },
  },
});