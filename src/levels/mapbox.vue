<style>

</style>

<template>
    <div class="level level-mapbox">
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
                    self = this

                map.on('load', function () {

                })

            }
        },

        data() {
            return {
                container: 'map',
                token: wp.extras.mapbox_key,
                style: wp.extras.mapbox_style,
                center: [14.453131, 50.096297],
                zoom: 3
            }
        }
    }
</script>