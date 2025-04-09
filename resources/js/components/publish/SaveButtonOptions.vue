<template>
    <component :is="wrapperComponent">
        <slot />
        <ui-dropdown v-if="showOptions" align="end">
            <template #trigger>
                <ui-button variant="primary" icon="chevron-down" />
            </template>
            <ui-dropdown-menu>
                <ui-dropdown-label v-text="__('After Saving')" />
                <ui-radio-group v-model="currentOption">
                    <ui-radio-item :label="__('Go To Listing')" value="listing" />
                    <ui-radio-item :label="__('Continue Editing')" value="continue_editing" />
                    <ui-radio-item :label="__('Create Another')" value="create_another" />
                </ui-radio-group>
            </ui-dropdown-menu>
        </ui-dropdown>
    </component>
</template>

<script>
export default {
    props: {
        showOptions: { type: Boolean, default: true },
        preferencesPrefix: { type: String, required: true },
    },

    data() {
        return {
            currentOption: null,
        };
    },

    computed: {
        preferencesKey() {
            return `${this.preferencesPrefix}.after_save`;
        },

        wrapperComponent() {
            return this.showOptions ? 'ui-button-group' : 'div';
        },
    },

    mounted() {
        this.setInitialValue();

        this.$watch('currentOption', (value) => this.setPreference(value));
    },

    methods: {
        setInitialValue() {
            this.currentOption = this.$preferences.get(this.preferencesKey) || 'listing';
        },

        setPreference(value) {
            if (value === this.$preferences.get(this.preferencesKey)) return;

            value === 'listing'
                ? this.$preferences.remove(this.preferencesKey)
                : this.$preferences.set(this.preferencesKey, value);
        },
    },
};
</script>
