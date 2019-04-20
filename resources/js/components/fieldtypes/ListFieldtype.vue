<template>
    <sortable-list
        v-model="data"
        :vertical="true"
        item-class="sortable-row"
        handle-class="sortable-handle"
    >
        <ul ref="list">
            <li class="sortable-row"
                v-for="(item, index) in data"
                :key="item._id"
                :class="{ editing: (editing === index) }"
            >
                <span v-if="editing === index">
                    <input
                        type="text"
                        class="w-full"
                        v-model="data[index].value"
                        @keydown.enter.prevent="saveAndAddNewItem"
                        @keyup.up="previousItem"
                        @keyup.down="nextItem"
                        @focus="editItem(index)"
                    />
                </span>
                <span v-else @click.prevent="editItem(index)">
                    <span class="sortable-handle">{{ item.value }}</span>
                    <i class="delete" @click="deleteItem(index)"></i>
                </span>
            </li>
            <li>
                <input
                    type="text"
                    class="w-full"
                    v-model="newItem"
                    ref="newItem"
                    :placeholder="`${__('Add an item')}...`"
                    @keydown.enter.prevent="addItem"
                    @blur="addItem"
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
        }
    },

    created() {
        this.data = this.arrayToSortable(this.value || []);
    },

    watch: {
        data: {
            deep: true,
            handler (data) {
                this.update(this.sortableToArray(data));
            }
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

        focusItem() {
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
