<template>

    <div class="site-selector flex items-center mr-2 h-full border-l border-r">
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
                <div class="flex items-center px-1 text-sm text-grey hover:text-grey-80 anti">
                    <svg-icon name="sites" class="site-selector-icon mr-1 h-4 w-4" />
                    <div class="whitespace-no-wrap">{{ option.name }}</div>
                </div>
            </template>
            <template #option="{ name, handle }">
                <div :class="{ 'text-grey-50': handle === active }">{{ name }}</div>
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
