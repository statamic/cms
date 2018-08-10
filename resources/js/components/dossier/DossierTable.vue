<template>
    <div class="dossier-table-wrapper">
        <table :class="['dossier', { 'has-checkboxes': hasCheckboxes }]">
            <thead v-if="hasHeaders">
                <tr>
                    <th class="checkbox-col" v-if="hasCheckboxes">
                        <input type="checkbox" id="checkbox-all" :checked="allItemsChecked" @click="checkAllItems" />
                        <label for="checkbox-all"></label>
                    </th>

                    <th v-for="column in columns"
                        @click="sortBy(column)"
                        :class="['column-' + column.value, {'active': isColumnActive(column), 'column-sortable': !isSearching, 'extra-col': column.extra} ]"
                        :style="{ width: tableColWidth(column.width) }"
                    >
                        {{ column.header }}
                        <i v-if="isColumnActive(column)"
                           class="icon icon-chevron-{{ sortOrder === 'asc' ? 'up' : 'down' }}"></i>
                    </th>

                    <th class="column-actions" v-if="hasActions"></th>
                </tr>
            </thead>
            <tbody ref="tbody">
                <tr v-for="item in items">

                    <td class="checkbox-col" v-if="hasCheckboxes && !reordering">
                        <input type="checkbox" :id="'checkbox-' + $index" :checked="item.checked" @change="toggle(item)" />
                        <label :for="'checkbox-' + $index"></label>
                    </td>

                    <td class="checkbox-col" v-if="reordering">
                        <div class="drag-handle">
                            <i class="icon icon-menu"></i>
                        </div>
                    </td>

                    <td v-for="(i, column) in columns" :class="[
                        `cell-${column.value}`, {
                            'extra-col': column.extra,
                            'empty-col': !item[column.value],
                            'first-cell': i === 0
                        }]
                    ">
                        <span class="column-label">{{ column.header }}</span>
                        <partial name="cell"></partial>
                    </td>

                    <!-- actions -->
                    <td class="column-actions" v-if="hasActions">
                        <div class="btn-group action-more">
                            <button type="button" class="btn-more dropdown-toggle"
                                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="icon icon-dots-three-vertical"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <partial name="actions"></partial>
                            </ul>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>

        <div v-if="showBulkActions" :class="{ 'bulk-actions': true, 'no-checkboxes': !hasCheckboxes }">
            <button type="button" class="btn action" @click="uncheckAllItems">
                {{ translate('cp.uncheck_all') }}
            </button>
            <button type="button" class="btn btn-delete action" @click.prevent="call('deleteMultiple', 'foo', 'bar')">
                {{ translate('cp.delete') }} {{ checkedItems.length }} {{ translate_choice('cp.items', checkedItems.length)}}
            </button>
        </div>

        <pagination
            v-if="!isSearching && pagination.totalPages > 1"
            :total="pagination.totalPages"
            :current="pagination.currentPage"
            :segments="pagination.segments"
            @selected="paginationPageSelected">
        </pagination>

    </div>
</template>

<script>
export default {

    props: ['options', 'items', 'isSearching'],

    data: function () {
        return {
            columns: this.$parent.columns,
            reordering: false
        }
    },

    partials: {
        // The default cell markup will be a link to the edit_url with a status symbol
        // if it's the first cell. Remaining cells just get the label.
        cell: `
            <span :class="{ 'has-status-icon': $index === 0 }">
                <span v-if="$index === 0" class="status status-{{ (item.published) ? 'live' : 'hidden' }}"
                      :title="(item.published) ? translate('cp.published') : translate('cp.draft')"
                ></span>
                <a v-if="column.link" :href="item.edit_url" class="has-status-icon">
                    {{{ formatValue(item[column.value]) }}}
                </a>
                <template v-else>
                    {{{ formatValue(item[column.value]) }}}
                </template>
            </span>
        `
    },

    computed: {
        hasCheckboxes: function () {
            if (this.options.checkboxes === false) {
                return false;
            }

            return true;
        },

        itemsAreChecked: function() {
            return this.checkedItems.length > 0;
        },

        hasHeaders: function () {
            if (this.options.headers === false) {
                return false;
            }

            return true;
        },

        hasActions: function () {
            return this.options.partials.actions !== undefined
                && this.options.partials.actions !== '';
        },

        showBulkActions() {
            return (this.hasItems && this.hasCheckboxes && this.itemsAreChecked && ! this.reordering);
        },

        hasItems: function () {
            return this.$parent.hasItems;
        },

        reorderable: function () {
            return this.options.reorderable;
        },

        checkedItems: function() {
            return this.items.filter(function(item) {
                return item.checked;
            }).map(function(item) {
                return item.id;
            });
        },

        allItemsChecked: function() {
            return this.items.length === this.checkedItems.length;
        },

        pagination() {
            return this.$parent.pagination;
        },

        sortOrder() {
            return this.$parent.sortOrder;
        }
    },

    beforeCompile: function () {
        var self = this;

        _.each(self.options.partials, function (str, name) {
            self.$options.partials[name] = str;
        });
    },

    methods: {
        registerPartials: function () {
            var self = this;

            _.each(self.options.partials, function (str, name) {
                Vue.partial(name, str);
            });
        },

        sortBy: function (col) {
            if (this.isSearching) return;

            let sort = col.value;
            let sortOrder = 'desc';

            // If the current sort order was clicked again, change the direction.
            if (this.$parent.sort === sort) {
                sortOrder = (this.$parent.sortOrder === 'asc') ? 'desc' : 'asc';
            }

            this.$parent.sortBy(sort, sortOrder);
        },

        checkAllItems: function () {
            var status = ! this.allItemsChecked;

            _.each(this.items, function (item) {
                item.checked = status;
            });
        },

        uncheckAllItems: function () {
            _.each(this.items, function (item) {
                item.checked = false;
            });
        },

        toggle: function (item) {
            item.checked = !item.checked;
        },

        enableReorder: function () {
            var self = this;

            self.reordering = true;

            $(this.$refs.tbody).sortable({
                axis: 'y',
                revert: 175,
                placeholder: 'placeholder',
                handle: '.drag-handle',
                forcePlaceholderSize: true,

                start: function(e, ui) {
                    ui.item.data('start', ui.item.index())
                },

                update: function(e, ui) {
                    var start = ui.item.data('start'),
                        end   = ui.item.index();

                    self.items.splice(end, 0, self.items.splice(start, 1)[0]);
                }

            });
        },

        disableReorder: function () {
            this.reordering = false;
            $(this.$refs.tbody).sortable('destroy');
        },

        saveOrder: function () {
            this.$parent.saveOrder();
        },

        /**
         * Dynamically call a method on the parent component
         *
         * Eg. `call('foo', 'bar', 'baz')` would be the equivalent
         * of doing `this.$parent.foo('bar', 'baz')`
         */
        call: function (method) {
            var args = Array.prototype.slice.call(arguments, 1);
            this.$parent[method].apply(this, args);
        },

        /**
         * When a page was selected in the pagination.
         */
        paginationPageSelected(page) {
            this.$parent.selectedPage = page;
            this.$parent.getItems();
        },

        isColumnActive(col) {
            if (this.isSearching) return false;

            return col.value === this.$parent.sort;
        },

        tableColWidth: function(width) {
            if (! width || width === 100) return;
            if (typeof width === 'string' && width.endsWith('px')) return width;
            return `${width}%`;
        },

        formatValue(value) {
            if (value && typeof value === 'object' && !Array.isArray() && value.thumbnail) {
                let html = `<span class="img"><img src="${value.thumbnail}" alt="${value.value}" />`;
                if (value.value) html += `<span>${value.value}</span>`;
                html += `</span>`;
                return html;
            }

            return Array.isArray(value) ? value.join(', ') : value;
        }
    },

    events: {
        'reordering.start': function() {
            this.enableReorder();
        },
        'reordering.saved': function () {
            this.reordering = false;
        },
        'reordering.stop': function() {
            this.disableReorder();
        }
    }
};
</script>
