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
    },

    data() {
        return {
            currentOption: 'listing',
        };
    },

    computed: {
        options() {
            return {
                options: {
                    listing: __('Go To Listing'),
                    continue: __('Continue Editing'),
                    another: __('Create Another'),
                },
                default: 'listing',
            };
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
