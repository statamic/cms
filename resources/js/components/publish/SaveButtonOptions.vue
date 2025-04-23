<template>
    <component :is="wrapperComponent">
        <slot />
        <Dropdown v-if="showOptions" align="end">
            <template #trigger>
                <Button variant="primary" icon="ui/chevron-down" />
            </template>
            <DropdownMenu>
                <DropdownLabel v-text="__('After Saving')" />
                <RadioGroup v-model="currentOption">
                    <Radio :label="__('Go To Listing')" value="listing" />
                    <Radio :label="__('Continue Editing')" value="continue_editing" />
                    <Radio :label="__('Create Another')" value="create_another" />
                </RadioGroup>
            </DropdownMenu>
        </Dropdown>
    </component>
</template>

<script>
import { Button, Dropdown, DropdownMenu, DropdownLabel, Radio, RadioGroup } from '@statamic/ui';

export default {
    components: {
        Button,
        Dropdown,
        DropdownMenu,
        DropdownLabel,
        Radio,
        RadioGroup,
    },

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
