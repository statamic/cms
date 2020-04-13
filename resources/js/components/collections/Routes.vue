<template>

    <div>
        <div v-if="hasMultipleSites">
            <div class="radio-fieldtype mb-1">
                <radio-fieldtype handle="route_mode" v-model="routeMode" :config="{
                    inline: true,
                    options: {
                        single: 'Single route',
                        multiple: 'Separate route for each site',
                    }
                }" />
            </div>
            <table class="grid-table" v-if="perSiteRouting">
                <thead>
                    <tr>
                        <th>Site</th>
                        <th class="w-2/3">Route</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="site in sites" :key="site.handle">
                        <td class="align-middle" v-text="site.name" />
                        <td>
                            <text-input
                                class="font-mono text-xs"
                                :value="value[site.handle]"
                                @input="updateSiteRoute(site.handle, $event)" />
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="!hasMultipleSites || !perSiteRouting">
            <text-input v-model="singleRoute" class="font-mono text-xs" />
        </div>
    </div>

</template>

<script>
export default {

    mixins: [Fieldtype],

    inject: ['storeName'],

    data() {
        return {
            routeMode: null,
            singleRoute: null,
        }
    },

    created() {
        this.routeMode = this.identicalRoutes ? 'single' : 'multiple';
    },

    computed: {

        sites() {
            let state = this.$store.state.publish[this.storeName];

            if (!state.values.sites) return [];

            return state.values.sites.map((handle, i) => {
                return {
                    handle,
                    name: state.meta.sites.data[i].title
                }
            });
        },

        hasMultipleSites() {
            return this.sites.length > 1;
        },

        perSiteRouting() {
            return this.routeMode === 'multiple';
        },

        identicalRoutes() {
            return _.uniq(Object.values(this.value)).length === 1;
        }

    },

    watch: {

        singleRoute(route) {
            this.update(route);
        },

        routeMode(mode) {
            if (mode === 'single') {
                this.singleRoute = Object.values(this.value)[0];
            } else {
                let value = {};
                this.sites.forEach(site => value[site.handle] = '');
                this.update(value);
            }
        }

    },

    methods: {

        updateSiteRoute(site, route) {
            let value = this.value;
            value[site] = route;
            this.update(value);
        }

    }

}
</script>
