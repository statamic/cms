<template>
    <table class="data-table">
        <thead>
            <tr>
            <th class="checkbox-column" v-if="allowBulkActions"></th>
            <th
                v-for="column in sharedState.visibleColumns"
                :key="column"
                class="cursor-pointer hover:bg-grey-lighter"
                @click.prevent="changeSortColumn(column)"
            >
                <span :class="{ 'font-bold': sharedState.sortColumn === column }">{{ column }}</span>
                <template v-if="sharedState.sortColumn === column">
                    <span v-show="sharedState.sortDirection === 'asc'">asc</span>
                    <span v-show="sharedState.sortDirection === 'desc'">desc</span>
                </template>
            </th>
            <th class="actions-column"></th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="row in rows">
                <td class="checkbox-column" v-if="allowBulkActions">
                    <input type="checkbox" :value="row.id" v-model="sharedState.checkedIds">
                </td>
                <td v-for="column in sharedState.visibleColumns">
                    {{ row[column] }}
                </td>
                <td class="text-right">
                    <slot name="actions" :row="row"></slot>
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
