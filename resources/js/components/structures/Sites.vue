<template>

    <div>
        <div v-if="hasMultipleSites">
            <div class="help-block -mt-1">
                Select which sites this structure will be allowed in. A tree's pages may be defined per site, or it can inherit from another.
            </div>
            <table class="grid-table">
                <thead>
                    <tr>
                        <th>Site</th>
                        <th>Route</th>
                        <th>Tree</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="site in sites" :key="site.handle">
                        <td class="align-middle">
                            <div class="flex items-center">
                                <toggle-fieldtype name="enabled" v-model="site.enabled" />
                                <div class="ml-2">{{ site.name }}</div>
                            </div>
                        </td>
                        <td><text-input v-model="site.route" :is-read-only="!site.enabled" /></td>
                        <td>
                            <v-select
                                v-model="site.inherit"
                                :options="sites"
                                label="name"
                                :reduce="opt => opt.handle"
                                :placeholder="__('Unique')"
                            />
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="!hasMultipleSites">
            <text-input v-model="sites[0].route" />
        </div>
    </div>

</template>

<script>
export default {

    mixins: [Fieldtype],

    data() {
        return {
            sites: this.value,
        }
    },

    computed: {

        hasMultipleSites() {
            return this.$config.get('sites').length > 1;
        }

    },

    watch: {
        sites(sites) {
            this.update(sites);
        }
    }

}
</script>
