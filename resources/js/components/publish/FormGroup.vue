<template>

    <publish-field
        :config="fieldConfig"
        :value="value"
        :errors="fieldErrors"
        @input="$emit('input', $event)"
    />

</template>

<script>
import PublishField from './Field.vue';

export default {

    components: { PublishField },

    props: {

        value: {},

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
