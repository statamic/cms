<template>
    <div class="array-fieldtype-container">

        <table v-if="isKeyed" class="array-table">
            <tbody>
                <tr v-if="data" v-for="(element, index) in data" :key="element._id">
                    <th>{{ config.keys[index].value }}</th>
                    <td>
                        <input type="text" class="input-text-minimal" v-model="data[index].value" />
                    </td>
                </tr>
            </tbody>
        </table>

        <template v-else-if="isDynamic">
            <div class="table-field">
                <table class="bordered-table" v-if="valueCount">
                    <thead>
                        <tr>
                            <th class="text-center p-1">{{ keyHeader }}</th>
                            <th class="text-center p-1">{{ valueHeader }}</th>
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
                                <td>
                                    <input type="text" class="input-text-minimal" v-model="element.key" />
                                </td>
                                <td>
                                    <input type="text" class="input-text-minimal" v-model="element.value" />
                                </td>
                                <td class="row-controls">
                                    <span class="icon icon-menu move sortable-handle"></span>
                                    <span class="icon icon-cross delete" @click="confirmDeleteValue(index)"></span>
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
import uniqid from 'uniqid';
import { SortableList, SortableItem } from '../sortable/Sortable';

export default {

    mixins: [Fieldtype],

    components: {
        SortableList,
        SortableItem
    },

    data() {
        return {
            data: [],
            deleting: false,
        }
    },

    watch: {
        data: {
            deep: true,
            handler (data) {
                this.update(data);
            }
        }
    },

    created() {
        // Assign each row a unique id that Vue can use as a v-for key.
        this.data = JSON.parse(JSON.stringify(this.value || []))
            .map(row => Object.assign(row, { _id: uniqid() }));
    },

    computed: {
        isKeyed() {
            return this.config.keys.length;
        },

        isDynamic() {
            return ! this.isKeyed;
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
            return __(this.config.add_button || 'Add Value');
        },

        keyHeader() {
            return __(this.config.key_header || 'Key');
        },

        valueHeader() {
            return __(this.config.value_header || 'Value');
        }
    },

    methods: {
        addValue() {
            this.data.push({
                _id: uniqid(),
                key: '',
                value: ''
            });
        },

        confirmDeleteValue(index) {
            this.deleting = index;
        },

        deleteValue(index) {
            this.deleting = false;

            this.data.splice(index, 1);
        },

        deleteCancelled() {
            this.deleting = false;
        }
    }

}
</script>
