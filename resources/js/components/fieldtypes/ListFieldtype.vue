<template>
    <sortable-list
        v-model="data"
        :vertical="true"
        item-class="sortable-row"
        handle-class="sortable-handle"
    >
        <ul>
            <li class="sortable-row"
                v-for="(item, index) in data"
                ref="items"
                :key="item._id"
                :class="{ editing: (editing === index) }"
            >
                <span v-if="editing === index">
                    <input
                        type="text"
                        class="list-input"
                        v-model="data[index].value"
                        @keydown.enter.prevent="updateItem(index)"
                        @keyup.up="goUp"
                        @keyup.down="goDown"
                        @focus="editItem(index)"
                    />
                </span>
                <span v-if="editing !== index" @click.prevent="editItem(index)">
                    <span class="sortable-handle">{{ item.value }}</span>
                    <i class="delete" @click="deleteItem(index)"></i>
                </span>
            </li>
            <li>
                <input
                    type="text"
                    class="list-input new-item"
                    v-model="newItem"
                    ref="newItem"
                    :placeholder="`${__('Add an item')}...`"
                    @keydown.enter.prevent="addItem"
                    @blur="addItem"
                    @keyup.up="goUp"
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
                this.focusItem(index);
            });
        },

        focusItem(index) {
            return this.editing === this.data.length
                ? this.$refs.newItem.focus()
                : this.$refs.items[this.editing].querySelector('input').select();
        },

        goUp() {
            this.editItem(Math.max(this.editing - 1, 0));
        },

        goDown() {
            this.editItem(this.editing + 1);
        },

        updateItem(index) {
            this.editItem(this.data.length);
        },

        deleteItem(index) {
            this.data.splice(index, 1);
        },

        removeEmptyValues() {
            this.data.forEach((item, index) => {
                if (item.value === '') {
                    this.deleteItem(index);
                }
            });
        },

        getReplicatorPreviewText() {
            return this.data.join(', ');
        }
    }
};
</script>
