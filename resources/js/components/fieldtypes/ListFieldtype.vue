<template>
    <div class="table-field">
        <table class="table-fieldtype-table" v-if="data.length > 0">
            <sortable-list
                v-model="data"
                :vertical="true"
                item-class="sortable-row"
                handle-class="sortable-handle"
                :mirror="false"
                @dragstart="$emit('focus')"
                @dragend="$emit('blur')"
            >
                <tbody>
                    <tr class="sortable-row" v-for="(element, index) in data" :key="element._id">
                        <td class="sortable-handle table-drag-handle" v-if="!isReadOnly" :class="{'rounded-tl': index === 0 }"></td>
                        <td>
                            <input
                                type="text"
                                ref="listItem"
                                class="input-text"
                                v-model="element.value"
                                :readonly="isReadOnly"
                                @blur="focused = false"
                                @focus="editItemWithoutFocusing(index)"
                                @keydown.enter.prevent="nextItem"
                                @keyup.up="previousItem"
                                @keyup.down="nextItem"
                                @paste="newItemInputPaste"
                            />
                        </td>
                        <td class="row-controls" v-if="!isReadOnly">
                            <button @click="deleteValue(index)" class="inline text-lg antialiased text-gray-600 hover:text-gray-800 ">&times;</button>
                        </td>
                    </tr>
                </tbody>
            </sortable-list>
        </table>

        <button class="btn btn-sm" @click="addItem" v-if="!isReadOnly">
            {{ addButton }}
        </button>
    </div>
</template>

<script>
import Fieldtype from './Fieldtype.vue';
import { SortableList, SortableItem, SortableHelpers } from '../sortable/Sortable';

export default {

    mixins: [Fieldtype, SortableHelpers],

    components: {
        SortableList,
        SortableItem
    },

    data() {
        return {
            data: [],
            editing: null,
            focused: false,
            mounted: false,
            deleting: false
        }
    },

    mounted() {
        this.$nextTick(() => this.mounted = true);
    },

    watch: {
        data: {
            deep: true,
            handler(data) {
                if (!this.mounted) return;
                this.updateDebounced(this.sortableToArray(data));
            }
        },


        value: {
            immediate: true,
            handler(value) {
                if (JSON.stringify(value) == JSON.stringify(this.sortableToArray(this.data))) return;
                this.data = this.arrayToSortable(value);
            }
        },

        focused(focused, oldFocused) {
            if (focused === oldFocused) return;

            if (focused) return this.$emit('focus');

            setTimeout(() => {
                if (!this.$el.contains(document.activeElement)) {
                    this.$emit('blur');
                    this.editing = null;
                }
            }, 1);
        }
    },

    computed: {
        addButton() {
            return __(this.config.add_button || 'Add Item');
        },
    },

    methods: {
        addItem() {
            this.data.push(this.newSortableValue());
            this.$nextTick(() => this.editItem(this.data.length - 1));
        },

        deleteValue(index) {
            this.data.splice(index, 1);
        },

        editItem(index) {
            this.editItemWithoutFocusing(index);
            this.$refs.listItem[index].focus();
        },

        editItemWithoutFocusing(index) {
            this.editing = index;
        },

        newItemInputPaste(event) {
            const value = event.clipboardData.getData('text');
            if (!value.includes("\n")) {
                return;
            }

            this.deleteIfEmpty()

            value.split("\n").forEach((item) => {
                this.data.push(this.newSortableValue(item));
            });

            this.$nextTick(() => this.editItem(this.data.length - 1));

            event.preventDefault();
        },


        previousItem() {
            this.deleteIfEmpty();

            this.editItem(Math.max(this.editing - 1, 0));
        },

        nextItem() {
            let deletedAdjustment = this.deleteIfEmpty() ? 1 : 0;

            if (this.editing + 1 >= this.data.length) {
                this.addItem();
            } else {
                this.editItem(this.editing + 1 - deletedAdjustment);
            }

        },

        deleteIfEmpty() {
            if (data_get(this.data[this.editing], 'value', true)) {
                return;
            }

            return this.deleteItem(this.editing);
        },

        deleteItem(index) {
            return this.data.splice(index, 1);
        },
    }
};
</script>
