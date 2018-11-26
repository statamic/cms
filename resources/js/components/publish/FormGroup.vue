<template>

    <publish-field
        :config="fieldConfig"
        :value="value"
        :errors="errors"
        @updated="updated"
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

        autofocus: {
            type: Boolean
        },

        errors: {
            type: Array
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
                autofocus: this.autofocus
            };
        }

    },

    methods: {

        updated(handle, value) {
            this.$emit('input', value);
        }

    }

}
</script>
