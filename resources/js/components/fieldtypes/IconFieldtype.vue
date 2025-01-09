<template>
    <div class="flex icon-fieldtype-wrapper">
        <v-select
            ref="input"
            class="w-full"
            append-to-body
            :calculate-position="positionOptions"
            clearable
            :name="name"
            :disabled="config.disabled || isReadOnly"
            :options="options"
            :placeholder="__(config.placeholder || 'Search...')"
            :searchable="true"
            :multiple="false"
            :close-on-select="true"
            :value="selectedOption"
            :create-option="(value) => ({ value, label: value })"
            @input="vueSelectUpdated"
            @search:focus="$emit('focus')"
            @search:blur="$emit('blur')">
            <template slot="option" slot-scope="option">
                <div class="flex items-center">
                    <svg-icon v-if="!option.html" :name="`${meta.set}/${option.label}`" class="w-5 h-5" />
                    <div v-if="option.html" v-html="option.html" class="w-5 h-5" />
                    <span class="text-xs rtl:mr-4 ltr:ml-4 text-gray-800 dark:text-dark-150 truncate">{{ __(option.label) }}</span>
                </div>
            </template>
            <template slot="selected-option" slot-scope="option">
                <div class="flex items-center">
                    <svg-icon v-if="!option.html" :name="`${meta.set}/${option.label}`" class="w-5 h-5 flex items-center" />
                    <div v-if="option.html" v-html="option.html" class="w-5 h-5" />
                    <span class="text-xs rtl:mr-4 ltr:ml-4 text-gray-800 dark:text-dark-150 truncate">{{ __(option.label) }}</span>
                </div>
            </template>
        </v-select>
    </div>
</template>

<script>
import Fieldtype from './Fieldtype.vue';
import PositionsSelectOptions from '../../mixins/PositionsSelectOptions';

export default {

    mixins: [Fieldtype, PositionsSelectOptions],

    computed: {
        options() {
            let options = [];
            for (let [name, html] of Object.entries(this.meta.icons)) {
                options.push({
                    value: name,
                    label: name,
                    html
                });
            }
            return options;
        },

        selectedOption() {
            return this.options.find(option => option.value === this.value);
        }
    },

    methods: {
        focus() {
            this.$refs.input.focus();
        },

        vueSelectUpdated(value) {
            if (value) {
                this.update(value.value)
            } else {
                this.update(null);
            }
        },
    }
};
</script>
