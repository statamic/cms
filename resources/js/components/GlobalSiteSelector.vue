<template>

    <div class="site-selector flex items-center rtl:ml-4 ltr:mr-4 h-full border-l border-r dark:border-dark-900">
        <v-select
            :options="sites"
            label="name"
            :get-option-key="(option) => option.handle"
            :value="activeName"
            :clearable="false"
            :searchable="false"
            @input="selected"
        >
            <template #selected-option="option">
                <div class="flex items-center px-2 text-sm text-gray dark:text-dark-100 hover:text-gray-800 dark:hover:text-dark-175 anti">
                    <svg-icon name="light/sites" class="rtl:ml-2 ltr:mr-2 h-4 w-4" />
                    <div class="whitespace-nowrap">{{ __(option.name) }}</div>
                </div>
            </template>
            <template #option="{ name, handle }">
                <div :class="{ 'text-gray-500': handle === active }">{{ __(name) }}</div>
            </template>
        </v-select>
    </div>

</template>

<script>
export default {

    computed: {
        sites() {
            return Statamic.$config.get('sites');
        },

        active() {
            return Statamic.$config.get('selectedSite');
        },

        activeName() {
            return _.findWhere(this.sites, { handle: this.active }).name;
        }
    },

    methods: {
        selected(site) {
            if (site.handle !== this.active) {
                window.location = cp_url(`select-site/${site.handle}`);
            }
        }
    }

}
</script>
