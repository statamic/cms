<template>

    <div>
        <div v-if="hasMultipleSites">
            <div class="radio-fieldtype mb-1">
                <radio-fieldtype handle="route_mode" :value="routeMode" @input="setRouteMode" :config="{
                    inline: true,
                    options: {
                        single: 'Single route',
                        multiple: 'Separate route for each site',
                    }
                }" />
            </div>
            <table class="grid-table" v-if="hasPerSiteRouting">
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

        <div v-if="!hasMultipleSites || !hasPerSiteRouting">
            <text-input :value="value" @input="update" class="font-mono text-xs" />
        </div>
    </div>

</template>

<script>
export default {

    mixins: [Fieldtype],

    inject: ['storeName'],

    data() {
        return {
            singleValue: null,
            multipleValue: null,
        }
    },


    computed: {

        routeMode() {
            return (typeof this.value === 'string') ? 'single' : 'multiple';
        },

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

        hasPerSiteRouting() {
            return this.routeMode === 'multiple';
        },

    },

    methods: {

        setRouteMode(mode) {
            if (mode === this.routeMode) return;

            let value;

            if (mode === 'single') {
                this.multipleValue = this.value;

                value = this.singleValue || Object.values(this.value)[0];
            } 
            
            if (mode === 'multiple') {
                this.singleValue = this.value;
                
                if (this.multipleValue) {
                    value = this.multipleValue;
                } else {
                    value = {};
                    this.sites.forEach(site => value[site.handle] = '');
                }
            }

            if (value) this.update(value);
        },

        updateSiteRoute(site, route) {
            let value = this.value;
            value[site] = route;
            this.update(value);
        }

    }

}
</script>
