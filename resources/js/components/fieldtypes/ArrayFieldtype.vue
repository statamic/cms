<template>
    <div class="array-fieldtype-container">

        <div v-if="isSingle" class="flex items-center">
            <div class="input-group">
                <div class="input-group-prepend flex items-center">
                     <select class="bg-transparent appearance-none shadow-none outline-none border-0 text-sm" @input="setKey($event.target.value)">
                        <option
                            v-for="(element, index) in keyedData"
                            v-text="config.keys[element.key] || element.key"
                            :key="element._id"
                            :value="element.key"
                            :selected="element.key === selectedKey" />
                    </select>
                    <svg-icon name="chevron-down-xs" class="w-2 ml-1" />
                </div>
                    <input
                        type="text"
                        class="input-text"
                        v-for="(element, index) in keyedData"
                        :key="element._id"
                        v-if="element.key === selectedKey"
                        :id="fieldId+'__'+element.key" v-model="data[index].value" :readonly="isReadOnly"
                    />
            </div>
        </div>

        <table v-else-if="isKeyed" class="array-table">
            <tbody>
                <tr v-if="data" v-for="(element, index) in keyedData" :key="element._id">
                    <th class="w-1/4"><label :for="fieldId+'__'+element.key">{{ config.keys[element.key] || element.key }}</label></th>
                    <td>
                        <input type="text" class="input-text-minimal" :id="fieldId+'__'+element.key" v-model="data[index].value" :readonly="isReadOnly" />
                    </td>
                </tr>
            </tbody>
        </table>

        <template v-else-if="isDynamic">
            <div class="table-field">
                <table class="table-fieldtype-table" v-if="valueCount">
                    <thead>
                        <tr>
                            <th class="grid-drag-handle-header" v-if="!isReadOnly"></th>
                            <th class="w-1/4">{{ keyHeader }}</th>
                            <th class="">{{ valueHeader }}</th>
                            <th class="row-controls"></th>
                        </tr>
                    </thead>

                    <sortable-list
                        v-model="data"
                        :vertical="true"
                        item-class="sortable-row"
                        handle-class="sortable-handle"
                    >
                        <tbody>
                            <tr class="sortable-row" v-for="(element, index) in data" :key="element._id">
                                <td class="sortable-handle table-drag-handle" v-if="!isReadOnly"></td>
                                <td>
                                    <input type="text" class="input-text font-bold" v-model="element.key" :readonly="isReadOnly" />
                                </td>
                                <td>
                                    <input type="text" class="input-text" v-model="element.value" :readonly="isReadOnly" />
                                </td>
                                <td class="row-controls">
                                    <a @click="deleteOrConfirm(index)" class="inline opacity-25 text-lg antialiased hover:opacity-75">&times;</a>
                                </td>
                            </tr>
                        </tbody>
                    </sortable-list>
                </table>

                <button class="btn" @click="addValue" :disabled="atMax">
                    {{ addButton }}
                </button>

                <confirmation-modal
                    v-if="deleting !== false"
                    :title="__('Delete Value')"
                    :bodyText="__('Are you sure you want to delete this value?')"
                    :buttonText="__('Delete')"
                    :danger="true"
                    @confirm="deleteValue(deleting)"
                    @cancel="deleteCancelled"
                >
                </confirmation-modal>
            </div>
        </template>

    </div>
</template>

<script>
import { SortableList, SortableItem, SortableHelpers } from '../sortable/Sortable';

export default {

    mixins: [Fieldtype, SortableHelpers],

    components: {
        SortableList,
        SortableItem
    },

    data() {
        return {
            data: this.objectToSortable(this.value || []),
            selectedKey:  Object.keys(this.value)[0],
            deleting: false
        }
    },

    watch: {
        data: {
            deep: true,
            handler (data) {
                this.updateDebounced(this.sortableToObject(data));
            }
        },

        value(value) {
            if (JSON.stringify(value) == JSON.stringify(this.sortableToObject(this.data))) return;
            this.data = this.objectToSortable(value);
        }
    },

    computed: {
        isKeyed() {
            return Boolean(Object.keys(this.config.keys).length);
        },

        isDynamic() {
            return ! this.isKeyed;
        },

        isSingle() {
            return this.config.mode === 'single';
        },

        keyedData() {
            return this.data.filter(element => this.config.keys.hasOwnProperty(element.key));
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
            return _.reduce(this.value, (carry, value, key) => {
                let str = `${key}: ${value}`;
                if (carry) str = carry + ', ' + str;
                return str;
            }, '');
        }
    },

    methods: {
        addValue() {
            this.data.push(this.newSortableValue());
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
            this.selectedKey = key
        }
    }

}
</script>
