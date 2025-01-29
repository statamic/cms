<template>

    <publish-field
        :config="fieldConfig"
        :model-value="modelValue"
        :errors="fieldErrors"
        @update:model-value="$emit('update:model-value', $event)"
    />

</template>

<script>
import PublishField from './Field.vue';

export default {
    emits: ['update:model-value'],

    components: { PublishField },

    props: {

        modelValue: {},

        fieldtype: {
            type: String,
            default: 'text',
        },

        handle: {
            type: String,
            required: true,
        },

        display: {
            type: String,
            required: true,
        },

        instructions: {
            type: String
        },

        width: {
            default: 100
        },

        focus: {
            type: Boolean
        },

        autoselect: {
            type: Boolean
        },

        errors: {
            type: Array
        },

        // A single error, useful when using this component directly in Blade views.
        error: {
            type: String
        },

        config: {
            type: Object
        }

    },

    computed: {

        fieldConfig() {
            return {
                ...this.config,
                type: this.fieldtype,
                handle: this.handle,
                display: this.display,
                instructions: this.instructions,
                width: this.width,
                focus: this.focus,
                autoselect: this.autoselect,
            };
        },

        fieldErrors() {
            return this.error ? [this.error] : this.errors;
        }

    }

}
</script>
