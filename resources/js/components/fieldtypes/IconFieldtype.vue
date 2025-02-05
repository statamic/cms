<template>
    <div class="icon-fieldtype-wrapper flex">
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
            :model-value="selectedOption"
            :create-option="(value) => ({ value, label: value })"
            @update:model-value="vueSelectUpdated"
            @search:focus="$emit('focus')"
            @search:blur="$emit('blur')"
        >
            <template #option="option">
                <div class="flex items-center">
                    <svg-icon v-if="!option.html" :name="`${meta.set}/${option.label}`" class="h-5 w-5" />
                    <div v-if="option.html" v-html="option.html" class="h-5 w-5" />
                    <span class="truncate text-xs text-gray-800 dark:text-dark-150 ltr:ml-4 rtl:mr-4">{{
                        __(option.label)
                    }}</span>
                </div>
            </template>
            <template #selected-option="option">
                <div class="flex items-center">
                    <svg-icon
                        v-if="!option.html"
                        :name="`${meta.set}/${option.label}`"
                        class="flex h-5 w-5 items-center"
                    />
                    <div v-if="option.html" v-html="option.html" class="h-5 w-5" />
                    <span class="truncate text-xs text-gray-800 dark:text-dark-150 ltr:ml-4 rtl:mr-4">{{
                        __(option.label)
                    }}</span>
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
                    html,
                });
            }
            return options;
        },

        selectedOption() {
            return this.options.find((option) => option.value === this.value);
        },
    },

    methods: {
        focus() {
            this.$refs.input.focus();
        },

        vueSelectUpdated(value) {
            if (value) {
                this.update(value.value);
            } else {
                this.update(null);
            }
        },
    },
};
</script>
