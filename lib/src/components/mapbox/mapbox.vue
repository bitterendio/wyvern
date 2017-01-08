<template>
    <div class="mapbox">
        <div id="map"></div>
    </div>
</template>

<script>
    export default {

        props: ['bound1', 'bound2'],

        mounted() {

            var self = this;

            var scripts = [
                'https://api.mapbox.com/mapbox-gl-js/v0.28.0/mapbox-gl.js',
                'https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-directions/v3.0.3/mapbox-gl-directions.js'
            ]


            var styles = [
                'https://api.mapbox.com/mapbox-gl-js/v0.28.0/mapbox-gl.css',
                'https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-directions/v3.0.3/mapbox-gl-directions.css'
            ];

            if ( typeof this.bound1 !== 'undefined' && typeof this.bound2 !== 'undefined' ) {

                var bounds = [
                    JSON.parse(this.bound1),
                    JSON.parse(this.bound2)
                ]
            }

            this.loadStyles(styles);
            this.loadScripts(scripts, function(){

                mapboxgl.accessToken = self.token;

                var mapConfiguration = {
                    container: self.container,
                    style: self.style,
                    center: self.center,
                    zoom: self.zoom
                };

                if ( typeof bounds !== 'undefined' ) {
                    //mapConfiguration['maxBounds'] = bounds;
                }

                mapConfiguration['maxBounds'] = bounds;

                window.map = new mapboxgl.Map(mapConfiguration);

                self.initData();
            });

        },

        methods: {
            loadScripts(scripts, callback) {

                var total = scripts.length,
                    loaded = 0;

                for ( var key in scripts ) {

                    var script = scripts[key];
                    this.loadScript(script).onload = function(){

                        loaded++;

                        if ( loaded == total && typeof callback == 'function' )
                            callback();

                    }

                }

            },

            loadScript(url) {
                var element = document.createElement('script');
                element.setAttribute('src', url);
                document.head.appendChild(element);
                return element;
            },

            loadStyles(styles, callback) {

                for ( var key in styles ) {

                    var style = styles[key];
                    var styleobj = this.loadStyle(style);

                }

            },

            loadStyle(url) {
                var element = document.createElement('link')
                element.type = 'text/css'
                element.rel = 'stylesheet'
                element.href = url
                document.head.appendChild(element)
                return element
            },

            initData() {

            },

            getMap() {
                return window.map;
            }
        },

        data() {
            return {
                container: 'map',
                token: '{MAPBOX_TOKEN_HERE}',
                style: '{MAPBOX_STYLE_HERE}',
                center: [14.453131, 50.096297],
                zoom: 5,

                base: true,
                directions: false
            }
        }
    }
</script>