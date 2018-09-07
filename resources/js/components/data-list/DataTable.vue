<template>
    <table class="data-table">
        <thead>
            <tr>
                <th class="checkbox-column" v-if="allowBulkActions"></th>
                <th
                    v-for="column in sharedState.visibleColumns"
                    :key="column"
                    class="sortable-column"
                    :class="{'current-column': sharedState.sortColumn === column}"
                    @click.prevent="changeSortColumn(column)"
                >
                    <span>{{ column }}</span>
                    <svg :class="sharedState.sortDirection" height="8" width="8" viewBox="0 0 10 6.5" style="enable-background:new 0 0 10 6.5;">
                        <path d="M9.9,1.4L5,6.4L0,1.4L1.4,0L5,3.5L8.5,0L9.9,1.4z"/>
                    </svg>
                </th>
                <th class="actions-column"></th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="(row, index) in rows" :key="row.id">
                <td class="checkbox-column" v-if="allowBulkActions">
                    <input
                        type="checkbox"
                        :value="row.id"
                        v-model="sharedState.selections"
                        :disabled="reachedSelectionLimit && !sharedState.selections.includes(row.id)"
                    />
                </td>
                <td v-for="column in sharedState.visibleColumns" :key="column">
                    <slot :name="`cell-${column}`" :row="row" :index="index">
                        {{ row[column] }}
                    </slot>
                </td>
                <td class="text-right">
                    <slot name="actions" :row="row" :index="index"></slot>
                </td>
            </tr>
        </tbody>
    </table>
</template>

<script>
export default {

    props: {
        allowBulkActions: {
            default: false,
            type: Boolean
        }
    },

    inject: ['sharedState'],

    computed: {

        rows() {
            return this.sharedState.rows;
        },

        reachedSelectionLimit() {
            return this.sharedState.selections.length === this.sharedState.maxSelections;
        }

    },

    methods: {

        changeSortColumn(column) {
            if (this.sharedState.sortColumn === column) this.swapSortDirection();
            this.sharedState.sortColumn = column;
            this.$emit('sorted', this.sharedState.sortColumn, this.sharedState.sortDirection);
        },

        swapSortDirection() {
            this.sharedState.sortDirection = this.sharedState.sortDirection === 'asc' ? 'desc' : 'asc';
        }

    }
}
</script>
