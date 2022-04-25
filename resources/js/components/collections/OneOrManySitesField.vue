<template>

    <div>
        <div v-if="hasMultipleSites">
            <div class="radio-fieldtype mb-1">
                <radio-fieldtype :handle="`${handle}_mode`" :value="mode" @input="setMode" :config="{
                    inline: true,
                    options: {
                        single: __('Single'),
                        multiple: __('Per-site'),
                    }
                }" />
            </div>
            <table class="grid-table" v-if="inMultipleMode">
                <thead>
                    <tr>
                        <th v-text="__('Site')" />
                        <th class="w-2/3" v-text="columnHeader" />
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="site in sites" :key="site.handle">
                        <td class="align-middle" v-text="site.name" />
                        <td>
                            <text-input
                                class="font-mono text-xs"
                                :value="value[site.handle]"
                                @input="updateSiteValue(site.handle, $event)" />
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="!hasMultipleSites || !inMultipleMode">
            <text-input :value="value" @input="update" class="font-mono text-xs" />
        </div>
    </div>

</template>

<script>
export default {

    props: ['handle', 'value', 'state', 'columnHeader'],

    computed: {

        mode() {
            return (this.value === null || typeof this.value === 'string') ? 'single' : 'multiple';
        },

        sites() {
            let state = this.state;

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

        inMultipleMode() {
            return this.mode === 'multiple';
        },

    },

    methods: {

        setMode(mode) {
            if (mode === this.mode) return;

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

            this.update(value);
        },

        updateSiteValue(site, siteValue) {
            let value = this.value;
            value[site] = siteValue;
            this.update(value);
        },

        update(value) {
            this.$emit('input', value);
        },

    }
}
</script>
