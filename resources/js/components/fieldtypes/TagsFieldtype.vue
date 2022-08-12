<template>
    <v-select
        ref="input"
        :name="name"
        :clearable="config.clearable"
        :close-on-select="false"
        :disabled="config.disabled || isReadOnly"
        :multiple="true"
        :placeholder="config.placeholder"
        :searchable="true"
        :select-on-key-codes="[9, 13, 188]"
        :taggable="true"
        :value="value"
        @input="update"
        @search:focus="$emit('focus')"
        @search:blur="$emit('blur')">
            <template #selected-option-container><i class="hidden"></i></template>
            <template #search="{ events, attributes }" v-if="config.multiple">
                <input
                    :placeholder="config.placeholder"
                    class="vs__search"
                    type="search"
                    v-on="events"
                    v-bind="attributes"
                >
            </template>
             <template #no-options>
                <div class="text-sm text-grey-70 text-left py-1 px-2" v-text="__('No options to choose from.')" />
            </template>
            <template #footer="{ deselect }">
                <sortable-list
                    item-class="sortable-item"
                    handle-class="sortable-item"
                    :value="value"
                    @input="update"
                >
                    <div class="vs__selected-options-outside flex flex-wrap">
                        <span v-for="tag in value" :key="tag" class="vs__selected mt-1 sortable-item">
                            {{ tag }}
                            <button @click="deselect(tag)" type="button" :aria-label="__('Remove tag')" class="vs__deselect">
                                <span>×</span>
                            </button>
                        </span>
                    </div>
                </sortable-list>
            </template>
    </v-select>
</template>

<script>
import HasInputOptions from './HasInputOptions.js'
import { SortableList, SortableItem } from '../sortable/Sortable';

export default {

    components: {
        SortableList,
        SortableItem,
    },

    mixins: [Fieldtype, HasInputOptions],

    methods: {
        focus() {
            this.$refs.input.focus();
        },
    },

};
</script>
