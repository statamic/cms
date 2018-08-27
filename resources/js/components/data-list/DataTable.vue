<template>
    <table class="data-table">
        <thead>
            <th class="checkbox-column" v-if="allowBulkActions"></th>
            <th v-for="column in sharedState.visibleColumns">{{ column }}</th>
            <th class="actions-column"></th>
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
        },
        rows: {
            required: true,
            type: Array
        }
    },
    inject: ['sharedState']
}
</script>
