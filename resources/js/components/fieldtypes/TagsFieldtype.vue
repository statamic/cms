<template>
    <v-select
        ref="input"
        :name="name"
        :clearable="config.clearable"
        :close-on-select="true"
        :options="config.options"
        :disabled="config.disabled || isReadOnly"
        :multiple="true"
        :placeholder="__(config.placeholder)"
        :searchable="true"
        :select-on-key-codes="[9, 13, 188]"
        :taggable="true"
        :append-to-body="true"
        :model-value="value"
        :dropdown-should-open="({ open }) => open && config.options.length > 0"
        @update:model-value="update"
        @search:focus="$emit('focus')"
        search:blur="$emit('blur-sm')"
    >
        <template #selected-option-container><i class="hidden"></i></template>
        <template #search="{ events, attributes }">
            <input
                :placeholder="config.placeholder"
                class="vs__search"
                type="search"
                v-on="events"
                v-bind="attributes"
                @paste="onPaste"
            />
        </template>
        <template #no-options>
            <div
                class="px-4 py-2 text-sm text-gray-700 ltr:text-left rtl:text-right"
                v-text="__('No options to choose from.')"
            />
        </template>
        <template #footer="{ deselect }">
            <sortable-list
                item-class="sortable-item"
                handle-class="sortable-item"
                :model-value="value"
                :distance="5"
                :mirror="false"
                @update:model-value="update"
            >
                <div class="vs__selected-options-outside flex flex-wrap">
                    <span v-for="tag in value" :key="tag" class="vs__selected sortable-item mt-2">
                        {{ tag }}
                        <button
                            @click="deselect(tag)"
                            type="button"
                            :aria-label="__('Remove tag')"
                            class="vs__deselect"
                        >
                            <span>×</span>
                        </button>
                    </span>
                </div>
            </sortable-list>
        </template>
    </v-select>
</template>

<style scoped>
.draggable-source--is-dragging {
    @apply border-dashed bg-transparent opacity-75;
}
</style>

<script>
import Fieldtype from './Fieldtype.vue';
import HasInputOptions from './HasInputOptions.js';
import { SortableList } from '../sortable/Sortable';

export default {
    components: {
        SortableList,
    },

    mixins: [Fieldtype, HasInputOptions],

    methods: {
        focus() {
            this.$refs.input.focus();
        },

        onPaste(event) {
            const pastedValue = event.clipboardData.getData('text');

            this.update([...this.value, ...pastedValue.split(',')]);

            event.preventDefault();
        },
    },
};
</script>
