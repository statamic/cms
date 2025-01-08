<template>

    <div>
        <div v-if="hasMultipleSites">
            <div class="radio-fieldtype mb-2">
                <radio-fieldtype
                    :handle="`${handle}_mode`"
                    :value="mode"
                    @input="setMode"
                    :config="{
                        inline: true,
                        options: {
                            single: __('Single'),
                            multiple: __('Per-site'),
                        }
                    }"
                />
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
                        <td class="align-middle" v-text="__(site.name)" />
                        <td>
                            <text-input
                                dir="ltr"
                                class="slug-field"
                                :model-value="modelValue[site.handle]"
                                @update:model-value="updateSiteValue(site.handle, $event)"
                            />
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="!hasMultipleSites || !inMultipleMode">
            <text-input :model-value="modelValue" @update:model-value="update" class="slug-field" dir="ltr" />
        </div>
    </div>

</template>

<script>
export default {

    props: ['handle', 'modelValue', 'state', 'columnHeader'],

    computed: {

        mode() {
            return (this.modelValue === null || typeof this.modelValue === 'string') ? 'single' : 'multiple';
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
                this.multipleValue = this.modelValue;

                value = this.singleValue || Object.values(this.modelValue)[0];
            }

            if (mode === 'multiple') {
                this.singleValue = this.modelValue;

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
            let value = this.modelValue;
            value[site] = siteValue;
            this.update(value);
        },

        update(value) {
            this.$emit('update:model-value', value);
        },

    }
}
</script>
