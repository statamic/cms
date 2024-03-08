<template>

    <div>
        <table class="grid-table">
            <thead>
                <tr>
                    <th>{{ __('Site') }}</th>
                    <th>{{ __('Origin') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="site in sites" :key="site.handle">
                    <td>
                        <div class="flex items-center text-sm">
                            <toggle-input v-model="site.enabled" class="rtl:ml-4 ltr:mr-4" />
                            {{ __(site.name) }}
                        </div>
                    </td>
                    <td class="text-sm">
                        <v-select
                            :options="siteOriginOptions(site)"
                            :value="site.origin"
                            :searchable="false"
                            :reduce="opt => opt.value"
                            @input="site.origin = $event"
                        />
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

</template>

<script>
export default {

    mixins: [Fieldtype],

    inject: ['storeName'],

    data() {
        return {
            sites: this.value
        }
    },

    created() {
    },

    watch: {

        sites(sites) {
            this.update(sites);
        }

    },

    methods: {

        siteOriginOptions(site) {
            return this.sites
                .map(s => ({ value: s.handle, label: __(s.name) }))
                .filter(s => s.value !== site.handle)
        }

    }

}
</script>
