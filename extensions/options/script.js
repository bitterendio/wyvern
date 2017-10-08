var vm = new Vue({
  el: '#wyvern-options',
  data: function() {
    return {
      loaded: false,
      options: wyvernOptions.options,
      nonce: wyvernOptions.nonce,
      root: wyvernOptions.root,
    };
  },
  created: function() {
    var options = [];
    for (var key in this.options) {
      var option = this.options[key];
      if (typeof option !== 'undefined') {
        if (option.slug) {
          options[option.slug] = option;
        } else {
          options[key]      = option;
          options[key].slug = key;
        }
      }
    }
    this.$set(this.options, options);
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
      this.$set(this.options, '', {
        name: '',
        value: '',
        slug: '',
        private: true,
      });
    },
    togglePrivate: function(option) {
      this.$set(option, !option.private);
    },
  },
});