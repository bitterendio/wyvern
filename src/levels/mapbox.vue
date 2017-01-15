<style>
    .marker {
        display: block;
        border: none;
        border-radius: 50%;
        cursor: pointer;
        padding: 0;
    }
</style>

<template>
    <div class="level level-mapbox">
        <nav id="filter-group" class="filter-group"></nav>

        <div id="map"></div>
    </div>
</template>

<script>
    import MapboxComponent from '../components/mapbox/mapbox.vue';

    export default {

        extends: MapboxComponent,

        props: ['level', 'bound1', 'bound2'],

        methods: {
            initData() {
                var map  = this.getMap(),
                    self = this;

                var filterGroup = document.getElementById('filter-group');

                map.on('load', function () {

                    self.getPlaces(function (places) {

                        // Add a GeoJSON source containing place coordinates and information.
                        map.addSource("places", {
                            "type": "geojson",
                            "data": places
                        });

                        places.features.forEach(function (feature) {
                            var symbol  = feature.properties['icon'];
                            var type    = feature.properties['charakter'];
                            var layerID = 'poi-' + type;

                            // Add a layer for this symbol type if it hasn't been added already.
                            if (!map.getLayer(layerID)) {
                                map.addLayer({
                                    "id"    : layerID,
                                    "type"  : "symbol",
                                    "source": "places",
                                    "layout": {
                                        "icon-image"        : symbol + "-15",
                                        "icon-allow-overlap": true
                                    },
                                    "filter": ["==", "charakter", type]
                                });

                                // Add checkbox and label elements for the layer.
                                var input     = document.createElement('input');
                                input.type    = 'checkbox';
                                input.id      = layerID;
                                input.checked = true;
                                filterGroup.appendChild(input);

                                var label = document.createElement('label');
                                label.setAttribute('for', layerID);
                                label.textContent = type;
                                filterGroup.appendChild(label);

                                // When the checkbox changes, update the visibility of the layer.
                                input.addEventListener('change', function (e) {
                                    map.setLayoutProperty(layerID, 'visibility',
                                        e.target.checked ? 'visible' : 'none');
                                });
                            }
                        });

                    });

                });

            },

            /**
               * {
                        "type": "FeatureCollection",
                        "features": [{
                            "type": "Feature",
                            "properties": {
                                "icon": "theatre",
                                "iconSize": [60, 60]
                            },
                            "geometry": {
                                "type": "Point",
                                "coordinates": [-77.038659, 38.931567]
                            }
                        }
                    }
             */

            getPlaces(callback) {

              /**
               * el.addEventListener('click', function(marker) {
                    self.$router.push({ path: self.url2Slug(post.link) })

                    var locobj = JSON.parse( post.acf.location );
                    if ( typeof locobj[0].lat != 'undefined' && typeof locobj[0].lng != 'undefined' ) {
                        map.setCenter([post.acf.location[0].lat, post.acf.location[0].lng]);
                    }
                });
               */

              var features = [];

                this.getPlacePosts(function(data){

                    data.forEach(function(post){

                        if ( typeof post.acf === 'undefined' ) return;
                        if ( typeof post.acf.location === 'undefined' ) return;
                        if ( post.acf.location === '' ) return;

                        var locobj = JSON.parse( post.acf.location );

                        if ( typeof locobj[0] == 'undefined' ) return;

                        if ( typeof locobj[0].lat == 'undefined' ) return;
                        if ( typeof locobj[0].lng == 'undefined' ) return;

                        // Get layer identifier
                        var charakter = 'test';
                        if ( typeof post.acf.charakter !== 'undefined' )
                            charakter = post.acf.charakter;

                        var iconUrl = '';

                        if ( typeof post.acf.obrazky[0] != 'undefined' ) {
                            if ( typeof post.acf.obrazky[0].obrazek != 'undefined' ) {
                                if (typeof post.acf.obrazky[0].obrazek.sizes != 'undefined') {
                                    iconUrl = post.acf.obrazky[0].obrazek.sizes.thumbnail;
                                }
                            }
                        }

                        features.push({
                            "type": "Feature",
                            "properties": {
                                "icon": {
                                    "iconSize": [40, 40],
                                    "iconUrl": iconUrl,
                                    "iconAnchor": [20, 20],
                                },
                                "charakter": charakter
                            },
                            "geometry": {
                                "type": "Point",
                                "coordinates": [locobj[0].lat, locobj[0].lng]
                            }
                        })

                    });

                    var geojson = {
                        "type": "FeatureCollection",
                        "features" : features
                    };

                    if ( typeof callback == 'function' )
                        callback(geojson)

                });

            }
        },

        data() {
            return {
                container: 'map',
                token: wp.extras.mapbox_key,
                style: wp.extras.mapbox_style,
                center: [14.453131, 50.096297],
                zoom: 3,
                directions: true
            }
        }
    }
</script>