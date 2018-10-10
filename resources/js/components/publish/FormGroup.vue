<template>

    <publish-field
        :config="config"
        :value="value"
        @updated="$emit('input', $event)"
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

        autofocus: {
            type: Boolean
        }

    },

    computed: {

        config() {
            return {
                type: this.fieldtype,
                handle: this.handle,
                display: this.display,
                instructions: this.instructions,
                width: this.width,
                autofocus: this.autofocus
            };
        }

    }

}
</script>
