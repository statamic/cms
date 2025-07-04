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
                            <toggle-input
                                :model-value="site.enabled"
                                @update:model-value="site.enabled = $event"
                                class="ltr:mr-4 rtl:ml-4"
                            />
                            {{ __(site.name) }}
                        </div>
                    </td>
                    <td class="text-sm">
                        <Select
                            class="w-full"
                            :options="siteOriginOptions(site)"
                            :model-value="site.origin"
                            @update:model-value="site.origin = $event"
                        />
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
import Fieldtype from '../fieldtypes/Fieldtype.vue';
import { Select } from '@statamic/ui';

export default {
    mixins: [Fieldtype],

    components: {
        Select,
    },

    inject: ['storeName'],

    data() {
        return {
            sites: this.value,
        };
    },

    created() {},

    watch: {
        sites(sites) {
            this.update(sites);
        },
    },

    methods: {
        siteOriginOptions(site) {
            return this.sites
                .map((s) => ({ value: s.handle, label: __(s.name) }))
                .filter((s) => s.value !== site.handle);
        },
    },
};
</script>
