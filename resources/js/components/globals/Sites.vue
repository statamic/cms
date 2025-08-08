<template>
    <table class="grid-table">
        <thead>
            <tr>
                <th scope="col">
                    <div class="flex items-center justify-between">
                        {{ __('Site') }}
                    </div>
                </th>
                <th scope="col">
                    <div class="flex items-center justify-between">
                        {{ __('Origin') }}
                    </div>
                </th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="site in sites" :key="site.handle">
                <td class="grid-cell">
                    <div class="flex items-center gap-2">
                        <Switch v-model="site.enabled" />
                        <Heading :text="__(site.name)" />
                    </div>
                </td>
                <td class="grid-cell">
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
</template>

<script>
import Fieldtype from '../fieldtypes/Fieldtype.vue';
import { Switch, Heading, Select } from '@statamic/ui';

export default {
    mixins: [Fieldtype],

    components: {
        Switch,
        Heading,
        Select,
    },

    data() {
        return {
            sites: this.value,
        };
    },

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
