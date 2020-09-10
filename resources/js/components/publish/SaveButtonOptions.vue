<template>

    <div :class="{ 'btn-group': showOptions }">

        <!-- Save button -->
        <slot></slot>

        <!-- Save and continue options dropdown -->
        <dropdown-list v-if="showOptions" class="text-left">
            <template v-slot:trigger>
                <button :class="buttonClass" class="rounded-l-none">
                    <svg-icon name="chevron-down-xs" class="w-2" />
                </button>
            </template>
            <h6 v-text="__('After Saving')" class="p-1" />
            <div class="publish-fields px-1">
                <div class="publish-field save-and-continue-options">
                    <radio-fieldtype
                        handle="save_and_continue_options"
                        :config="options"
                        v-model="currentOption"
                    />
                </div>
            </div>
        </dropdown-list>

    </div>

</template>

<script>
export default {

    props: {
        showOptions: {
            type: Boolean,
            default: true
        },
        buttonClass: {
            default: 'btn-primary',
        },
        preferencesPrefix: {
            type: String,
            required: true,
        },
    },

    data() {
        return {
            currentOption: null,
        };
    },

    computed: {
        options() {
            return {
                options: {
                    listing: __('Go To Listing'),
                    continue_editing: __('Continue Editing'),
                    add_another: __('Create Another'),
                },
            };
        },

        preferencesKey() {
            return `${this.preferencesPrefix}.after_save`;
        },
    },

    watch: {
        currentOption: 'setPreference',
    },

    mounted() {
        this.setInitialValue();
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

}
</script>

<style>
    .save-and-continue-options input {
        margin-bottom: 9px;
    }
    .save-and-continue-options input {
        margin-right: 5px;
    }
</style>
