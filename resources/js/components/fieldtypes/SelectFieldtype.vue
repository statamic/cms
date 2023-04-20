<template>
    <div class="flex">
        <v-select
            ref="input"
            class="flex-1"
            :name="name"
            :clearable="config.clearable"
            :disabled="config.disabled || isReadOnly || (config.multiple && limitReached)"
            :options="options"
            :placeholder="config.placeholder"
            :searchable="config.searchable || config.taggable"
            :taggable="config.taggable"
            :push-tags="config.push_tags"
            :multiple="config.multiple"
            :reset-on-options-change="resetOnOptionsChange"
            :close-on-select="true"
            :value="selectedOptions"
            :create-option="(value) => ({ value, label: value })"
            @input="vueSelectUpdated"
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
                <template #option="{ label }">
                    <div v-if="config.label_html" v-html="label"></div>
                    <template v-else v-text="label"></template>
                </template>
                <template #selected-option="{ label }">
                    <div v-if="config.label_html" v-html="label"></div>
                    <template v-else v-text="label"></template>
                </template>
                <template #no-options>
                    <div class="text-sm text-grey-70 text-left py-1 px-2" v-text="__('No options to choose from.')" />
                </template>
                <template #footer="{ deselect }" v-if="config.multiple">
                    <div class="vs__selected-options-outside flex flex-wrap">
                        <span v-for="option in selectedOptions" :key="option.value" class="vs__selected mt-1">
                            <div v-if="config.label_html" v-html="option.label"></div>
                            <template v-else>{{ option.label }}</template>
                            <button v-if="!readOnly" @click="deselect(option)" type="button" :aria-label="__('Deselect option')" class="vs__deselect">
                                <span>×</span>
                            </button>
                            <button v-else type="button" class="vs__deselect">
                                <span class="opacity-50">×</span>
                            </button>
                        </span>
                    </div>
                </template>
        </v-select>
        <div class="text-xs ml-1 mt-1.5" :class="limitIndicatorColor" v-if="config.max_items">
            <span v-text="currentLength"></span>/<span v-text="config.max_items"></span>
        </div>
    </div>
</template>

<script>
import HasInputOptions from './HasInputOptions.js'

export default {

    mixins: [Fieldtype, HasInputOptions],

    computed: {
        selectedOptions() {
            let selections = this.value || [];
            if (typeof selections === 'string' || typeof selections === 'number') {
                selections = [selections];
            }
            return selections.map(value => {
                return _.findWhere(this.options, {value}) || { value, label: value };
            });
        },

        options() {
            return this.normalizeInputOptions(this.config.options);
        },

        replicatorPreview() {
            return this.selectedOptions.map(option => option.label).join(', ');
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
        },

        limitReached() {
            if (! this.config.max_items) return false;

            return this.currentLength >= this.config.max_items;
        },

        limitExceeded() {
            if (! this.config.max_items) return false;

            return this.currentLength > this.config.max_items;
        },

        currentLength() {
            if (this.value) {
                return (typeof this.value == 'string') ? 1 : this.value.length;
            }

            return 0;
        },

        limitIndicatorColor() {
            if (this.limitExceeded) {
                return 'text-red';
            } else if (this.limitReached) {
                return 'text-green';
            }

            return 'text-grey';
        }
    },

    methods: {
        focus() {
            this.$refs.input.focus();
        },

        vueSelectUpdated(value) {
            if (this.config.multiple) {
                this.update(value.map(v => v.value));
            } else {
                if (value) {
                    this.update(value.value)
                } else {
                    this.update(null);
                }
            }
        }
    }
};
</script>
