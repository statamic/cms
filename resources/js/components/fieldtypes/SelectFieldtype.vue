<template>
    <v-select
        ref="input"
        @input="update"
        :name="name"
        :clearable="config.clearable"
        :disabled="config.disabled || isReadOnly"
        :options="options"
        :placeholder="config.placeholder"
        :reduce="selection => selection.value"
        :searchable="config.searchable"
        :taggable="config.taggable"
        :push-tags="config.push_tags"
        :multiple="config.multiple"
        :reset-on-options-change="resetOnOptionsChange"
        :close-on-select="!config.taggable"
        :value="value" />
</template>

<script>
import HasInputOptions from './HasInputOptions.js'

export default {

    mixins: [Fieldtype, HasInputOptions],

    computed: {
        options() {
            return this.normalizeInputOptions(this.config.options);
        },

        resetOnOptionsChange() {
            // Reset logic should only happen when the config value is true.
            // Nothing should be reset when it's false or undefined.
            if (this.config.reset_on_options_change !== true) return false;

            // Reset the value if the value doesn't exist in the new set of options.
            return (options, old, val) => {
                let opts = options.map(o => o.value);
                return !val.some(v => opts.includes(v.value));
            };
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
