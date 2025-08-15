<template>
    <div>
        <ui-input-group v-if="isSingle">
            <ui-input-group-prepend>
                <select
                    class="appearance-none border-0 bg-transparent text-sm shadow-none outline-hidden"
                    @input="setKey($event.target.value)"
                >
                    <option
                        v-for="(element, index) in keyedData"
                        v-text="keys[element.key] || element.key"
                        :key="element._id"
                        :value="element.key"
                        :selected="element.key === selectedKey"
                    />
                </select>
                <ui-icon name="ui/chevron-down" class="size-3 ms-1" />
            </ui-input-group-prepend>
            <template v-for="(element, index) in keyedData">
                <ui-input
                    v-if="element.key === selectedKey"
                    v-model="data[index].value"
                    class="border-l-0"
                    :key="element._id"
                    :id="fieldId + '__' + element.key"
                    :readonly="isReadOnly"
                />
            </template>
        </ui-input-group>

        <table class="table-contained" v-else-if="isKeyed">
            <tbody>
                <tr v-if="data" v-for="(element, index) in keyedData" :key="element._id">
                    <th class="w-1/4">
                        <label :for="fieldId + '__' + element.key">{{ keys[element.key] || element.key }}</label>
                    </th>
                    <td>
                        <input
                            type="text"
                            class="w-full input-text"
                            :id="fieldId + '__' + element.key"
                            v-model="data[index].value"
                            :readonly="isReadOnly"
                        />
                    </td>
                </tr>
            </tbody>
        </table>

        <table class="table-contained" v-if="isDynamic && valueCount">
            <thead>
                <tr>
                    <th class="grid-drag-handle-header" v-if="!isReadOnly"></th>
                    <th class="w-1/4">{{ keyHeader }}</th>
                    <th class="">{{ valueHeader }}</th>
                    <th class="row-controls" v-if="!isReadOnly"></th>
                </tr>
            </thead>

            <sortable-list
                v-model="data"
                :vertical="true"
                item-class="sortable-row"
                handle-class="sortable-handle"
                :mirror="false"
            >
                <tbody>
                    <tr class="sortable-row" v-for="(element, index) in data" :key="element._id">
                        <td class="sortable-handle table-drag-handle" v-if="!isReadOnly"></td>
                        <td>
                            <input
                                type="text"
                                class="input-text font-medium"
                                v-model="element.key"
                                :readonly="isReadOnly"
                            />
                        </td>
                        <td>
                            <input
                                type="text"
                                class="input-text"
                                v-model="element.value"
                                :readonly="isReadOnly"
                            />
                        </td>
                        <td class="row-controls" v-if="!isReadOnly">
                            <button
                                @click="deleteOrConfirm(index)"
                                class="inline text-lg antialiased opacity-25 hover:opacity-75 cursor-pointer"
                            >
                                &times;
                            </button>
                        </td>
                    </tr>
                </tbody>
            </sortable-list>
        </table>

        <Button @click="addValue" icon="plus" size="sm" :disabled="atMax" v-if="!isReadOnly && !isSingle && !isKeyed">
            {{ addButton }}
        </Button>

        <confirmation-modal
            v-if="deleting !== false"
            :title="__('Delete Value')"
            :bodyText="__('Are you sure you want to delete this value?')"
            :buttonText="__('Delete')"
            :danger="true"
            @confirm="deleteValue(deleting)"
            @cancel="deleteCancelled"
        />
    </div>
</template>

<script>
import Fieldtype from './Fieldtype.vue';
import { SortableList, SortableHelpers } from '../sortable/Sortable';
import { Button } from '@/components/ui';

export default {
    mixins: [Fieldtype, SortableHelpers],

    components: {
        SortableList,
        Button,
    },

    data() {
        const keys = Object.keys(this.value || {});
        const selectedKey = keys.length > 0 ? keys[0] : null;

        return {
            data: this.objectToSortable(this.value || []),
            selectedKey,
            deleting: false,
        };
    },

    watch: {
        data: {
            deep: true,
            handler(data) {
                this.updateDebounced(this.sortableToObject(data));
            },
        },

        value(value) {
            if (JSON.stringify(value) == JSON.stringify(this.sortableToObject(this.data))) return;
            this.data = this.objectToSortable(value);
        },
    },

    computed: {
        keys() {
            return this.meta.keys || this.config.keys;
        },

        isKeyed() {
            return Boolean(Object.keys(this.keys).length);
        },

        isDynamic() {
            return !this.isKeyed;
        },

        isSingle() {
            return this.config.mode === 'single';
        },

        keyedData() {
            return this.data.filter((element) => this.keys.hasOwnProperty(element.key));
        },

        maxItems() {
            return this.config.max_items || null;
        },

        valueCount() {
            return this.data.length;
        },

        atMax() {
            return this.maxItems ? this.valueCount >= this.maxItems : false;
        },

        addButton() {
            return __(this.config.add_button || 'Add Row');
        },

        keyHeader() {
            return __(this.config.key_header || 'Key');
        },

        valueHeader() {
            return __(this.config.value_header || 'Value');
        },

        replicatorPreview() {
            if (!this.showFieldPreviews || !this.config.replicator_preview) return;

            if (!this.value) return '';

            return Object.entries(this.value)
                .map(([key, value]) => `${key}: ${value}`)
                .filter(Boolean)
                .join(', ');
        },
    },

    methods: {
        addValue() {
            this.data.push(this.newSortableValue());
            this.$nextTick(() => {
                this.$el.querySelector('tr:last-child input').focus();
            });
        },

        confirmDeleteValue(index) {
            this.deleting = index;
        },

        deleteOrConfirm(index) {
            if (this.data[index].key === null && this.data[index].value === null) {
                this.deleteValue(index);
            } else {
                this.confirmDeleteValue(index);
            }
        },

        deleteValue(index) {
            this.deleting = false;

            this.data.splice(index, 1);
        },

        deleteCancelled() {
            this.deleting = false;
        },

        setKey(key) {
            this.selectedKey = key;
        },
    },
};
</script>
