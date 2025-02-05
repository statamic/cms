<template>
    <div :class="{ 'btn-group': showOptions }">
        <!-- Save button -->
        <slot></slot>

        <!-- Save and continue options dropdown -->
        <dropdown-list v-if="showOptions" class="ltr:text-left rtl:text-right">
            <template v-slot:trigger>
                <button :class="buttonClass" class="ltr:rounded-l-none rtl:rounded-r-none">
                    <svg-icon v-if="buttonIcon" :name="buttonIcon.name" :class="buttonIcon.class" />
                </button>
            </template>
            <h6 v-text="__('After Saving')" class="p-2" />
            <div class="publish-fields px-2 py-1">
                <div class="publish-field save-and-continue-options radio-fieldtype">
                    <radio-fieldtype
                        handle="save_and_continue_options"
                        :config="options"
                        :value="currentOption"
                        @input="currentOption = $event"
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
            default: true,
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
                    create_another: __('Create Another'),
                },
            };
        },

        buttonIcon() {
            switch (true) {
                case this.currentOption === 'listing':
                    return { name: 'micro/arrow-go-back', class: 'w-3' };
                case this.currentOption === 'continue_editing':
                    return { name: 'micro/chevron-down-xs', class: 'w-2' };
                case this.currentOption === 'create_another':
                    return { name: 'micro/add-circle', class: 'w-3' };
            }
        },

        preferencesKey() {
            return `${this.preferencesPrefix}.after_save`;
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

<style>
.save-and-continue-options input {
    margin-bottom: 9px;
}
.save-and-continue-options input {
    margin-right: 5px;
    [dir='rtl'] & {
        margin-left: 5px;
        margin-right: 0;
    }
}
</style>
