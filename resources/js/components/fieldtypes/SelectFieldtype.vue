<template>
    <v-select
        ref="input"
        @input="update"
        :clearable="config.clearable"
        :disabled="config.disabled"
        :options="options"
        :placeholder="config.placeholder"
        :reduce="selection => selection.value"
        :searchable="config.searchable"
        :taggable="config.taggable"
        :push-tags="config.push_tags"
        :multiple="config.multiple"
        :value="value" />
</template>

<script>
import HasInputOptions from './HasInputOptions.js'

export default {

    mixins: [Fieldtype, HasInputOptions],

    computed: {
        options() {
            return this.normalizeInputOptions(this.config.options);
        }
    },

    methods: {
        handleUpdate(value) {
            this.update(value.value)
        },

        focus() {
            this.$refs.input.focus();
        },

        getReplicatorPreviewText() {
            // @TODO
        },
    }
};
</script>
