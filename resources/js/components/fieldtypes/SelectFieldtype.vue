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
        :value="value"
        @search:focus="$emit('focus')"
        @search:blur="$emit('blur')">
            <template #selected-option-container v-if="config.multiple"><i class="hidden"></i></template>
            <template #search="{ events, attributes }" v-if="config.multiple">
                <input
                    :placeholder="config.placeholder"
                    class="vs__search"
                    type="search"
                    v-on="events"
                    v-bind="attributes"
                >
            </template>
            <template #footer="{ deselect }" v-if="config.multiple">
                <div class="vs__selected-options-outside flex flex-wrap">
                    <span v-for="option in value" class="vs__selected mt-1">
                        {{ getLabel(option) }}
                        <button @click="deselect(getOption(option))" type="button" :aria-label="__('Deselect option')" class="vs__deselect">
                            <span>×</span>
                        </button>
                    </span>
                </div>
            </template>
    </v-select>
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
        focus() {
            this.$refs.input.focus();
        },

        getOption(value) {
            return _.findWhere(this.options, {value});
        },

        getLabel(handle) {
            return this.getOption(handle).label;
        }
    }
};
</script>
