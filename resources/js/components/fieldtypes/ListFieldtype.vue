<template>
<ul>
	<li v-for="(item, $index) in data" :key="$index" :class="{ editing: (editing == $index) }">
		<span v-if="editing == $index">
			<input
				type="text"
				v-model="data[$index]"
				class="input-text"
				@keydown.enter="updateItem(item, $index, $event)"
				@keyup.up="goUp"
				@keyup.down="goDown"
			/>
		</span>
		<span v-if="editing != $index" @dblclick="editItem($index, $event)">
		    {{ item }}
			<i class="delete" @click="deleteItem($index)"></i>
		</span>
	</li>
	<li>
		<input type="text" class="input-text new-item" v-model="newItem"
            :placeholder="`${__('Add another item')}...`"
            @keydown.enter.prevent="addItem"
            @blur="addItem"
            @keyup.up="goUp"
		/>
	</li>
</ul>
</template>

<script>
export default {

    mixins: [Fieldtype],

    data: function () {
        return {
            data: this.value || [],
            newItem: '',
            editing: null,
        }
    },

    watch: {

        value(value) {
            this.data = value;
        },

        data(value) {
            this.update(value);
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
