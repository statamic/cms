<template>
    <div class="site-selector flex items-center rtl:ml-4 ltr:mr-4 h-full border-l border-r dark:border-dark-900">
        <v-select
            :options="sites"
            label="name"
            :get-option-key="(option) => option.handle"
            :clearable="false"
            :searchable="false"
            :model-value="active"
            @update:model-value="selected"
        >
            <template #selected-option="{ name, handle }">
                <div class="flex items-center px-2 text-sm text-gray dark:text-dark-100 hover:text-gray-800 dark:hover:text-dark-175 anti">
                    inside
                    <svg-icon name="light/sites" class="rtl:ml-2 ltr:mr-2 h-4 w-4" />
                    <div class="whitespace-nowrap">{{ __(name) }}</div>
                </div>
            </template>
            <template #option="{ name, handle }">
                <div :class="{ 'text-gray-500': handle === active }">{{ __(option.name) }}</div>
            </template>
        </v-select>
    </div>
</template>

<script>
export default {
    data() {
        return {
            sites: [],
            active: null,
        }
    },

    methods: {
        selected(site) {
            if (site.handle !== this.active.handle) {
                window.location = cp_url(`select-site/${site.handle}`);
            }
        }
    },

    created() {
        this.sites = Statamic.$config.get('sites');
        this.active = this.sites.find(site => site.handle === Statamic.$config.get('selectedSite'));
    },
}
</script>
