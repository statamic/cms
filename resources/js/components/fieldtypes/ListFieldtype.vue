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
        addItem: function() {
            // Blank items are losers.
            if (this.newItem !== '') {
                this.data.push(this.newItem);
                this.newItem = '';
                this.editing = this.data.length;
            }

        },

        editItem: function(index, event) {
            event.preventDefault();

            this.editing = index;

            // Async is good times.
            this.$nextTick(function () {
                $(this.$el).find('.editing input').focus().select();
            });
        },

        goUp: function() {
            if (this.editing > 0) {
                this.editing = this.editing - 1;
                this.$nextTick(function () {
                    $(this.$el).find('.editing input').focus().select();
                });
            }
        },

        goDown: function() {

            // Check if we're at the last one
            if (this.editing === this.data.length - 1) {
                this.editing = this.data.length;
                $(this.$el).find('.new-item').focus();
            } else {
                this.editing = this.editing + 1;
                this.$nextTick(function () {
                    $(this.$el).find('.editing input').focus().select();
                });
            }
        },

        updateItem: function(value, index, event) {
            event.preventDefault();

            // Let's remove blank items
            if (value == '') {
                this.data.$remove(index);
            } else {
                this.data[index] = value;
            }

            this.editing = this.data.length;

            // Back to adding new items.
            $(this.$el).find('.new-item').focus();

        },

        deleteItem: function(i) {
            this.data.splice(i, 1);
        },

        getReplicatorPreviewText() {
            return this.data.join(', ');
        }
    },

    mounted() {
        var self = this,
            start = '';
        $(this.$el).sortable({
            axis: "y",
            revert: 175,
            items: '> li:not(:last-child)',

            start: function(e, ui) {
                start = ui.item.index();
            },

            update: function(e, ui) {
                var end  = ui.item.index(),
                    swap = self.data.splice(start, 1)[0];

                self.data.splice(end, 0, swap);
            }
        });
    }
};
</script>
