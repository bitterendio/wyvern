import { routes, Vue, VueRouter, capitalize, getTemplateHierarchy } from './app'

// Override component example
// import Post from './post.vue'
// Vue.component('Post', Post)

// Import styles
import './../style.scss'

// Create router instance
var router = new VueRouter({
    mode: 'history',
    routes: routes.get()
});

// Start app
const App = new Vue({
    el: '#app',

    template: '<div class="template-wrapper"' +
        '<theme-header></theme-header>' +
        '<router-view></router-view>' +
        '<theme-footer></theme-footer>' +
    '</div>',

    router: router,

    data() {
        return {
        }
    },

    mounted() {
        this.updateTitle('')
        this.trackGA()
    },

    methods: {
        updateTitle(pageTitle) {
            document.title = (pageTitle ? pageTitle + ' - ' : '') + wp.site_name
        },
        trackGA() {
            if ( typeof ga == 'function' ) {
                ga('set', 'page', '/' + window.location.pathname.substr(1))
                ga('send', 'pageview');
            }
        }
    },

    // Create listeners
    created: function () {
        window.eventHub.$on('page-title', this.updateTitle)
        window.eventHub.$on('track-ga', this.trackGA)
    },

    // It's good to clean up event listeners before
    // a component is destroyed.
    beforeDestroy: function () {
        window.eventHub.$off('page-title', this.updateTitle)
        window.eventHub.$off('track-ga', this.trackGA)
    },

    watch: {
        // Changed route
        '$route' (to, from) {
            window.eventHub.$emit('changed-route')

            console.log('Changed route', to, from)
        }
    }
});