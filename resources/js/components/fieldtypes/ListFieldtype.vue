<template>
    <sortable-list
        v-model="data"
        :vertical="true"
        item-class="sortable-row"
        handle-class="sortable-handle"
        @dragstart="$emit('focus')"
        @dragend="$emit('blur')"
    >
        <ul ref="list" class="outline-none">
            <li class="sortable-row outline-none"
                v-for="(item, index) in data"
                :key="item._id"
                :class="{ editing: (editing === index) }"
            >
                <span v-if="isReadOnly">
                    {{ data[index].value }}
                </span>
                <template v-if="!isReadOnly">
                    <span v-if="editing === index">
                        <input
                            type="text"
                            class="w-full"
                            v-model="data[index].value"
                            :readonly="isReadOnly"
                            :id="fieldId"
                            @keydown.enter.prevent="saveAndAddNewItem"
                            @keyup.up="previousItem"
                            @keyup.down="nextItem"
                            @focus="editItem(index)"
                            @blur="focused = false"
                        />
                    </span>
                    <span v-else @click.prevent="editItem(index)">
                        <span class="sortable-handle">{{ item.value }}</span>
                        <i class="delete" @click="deleteItem(index)"></i>
                    </span>
                </template>
            </li>
            <li>
                <input
                    v-if="!isReadOnly"
                    type="text"
                    class="w-full"
                    v-model="newItem"
                    ref="newItem"
                    :placeholder="`${__('Add an item')}...`"
                    @keydown.enter.prevent="addItem"
                    @blur="newItemInputBlurred"
                    @paste="newItemInputPaste"
                    @focus="editItem(data.length)"
                    @keyup.up="previousItem"
                />
            </li>
        </ul>
    </sortable-list>
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
            data: [],
            newItem: '',
            editing: null,
            focused: false,
            mounted: false,
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

    methods: {
        addItem() {
            if (this.newItem === '') {
                return;
            }

            this.data.push(this.newSortableValue(this.newItem));
            this.newItem = '';
            this.editing = this.data.length;
        },

        editItem(index) {
            this.editing = index;

            this.$nextTick(function () {
                this.focusItem();
            });
        },

        newItemInputPaste(event) {
            if (this.newItem !== '') {
                return;
            }

            const value = event.clipboardData.getData('text');
            if (!value.includes("\n")) {
                return;                
            }
            
            value.split("\n").forEach((item) => {
                this.data.push(this.newSortableValue(item));
            });

            event.preventDefault();
        },

        newItemInputBlurred() {
            this.addItem();
            this.focused = false;
        },

        focusItem() {
            this.focused = true;

            return this.editing === this.data.length
                ? this.$refs.newItem.focus()
                : this.$refs.list.querySelector('.editing input').select();
        },

        previousItem() {
            this.deleteIfEmpty();

            this.editItem(Math.max(this.editing - 1, 0));
        },

        nextItem() {
            let deletedAdjustment = this.deleteIfEmpty() ? 1 : 0;

            this.editItem(this.editing + 1 - deletedAdjustment);
        },

        saveAndAddNewItem() {
            this.deleteIfEmpty();

            this.editItem(this.data.length);
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

        getReplicatorPreviewText() {
            return this.data.map(item => item.value).join(', ');
        }
    }
};
</script>
