<script setup>
import { ref, provide } from 'vue';
import { cva } from 'cva';
import DataTableColumns from './Columns.vue';
import DataTableRows from './Rows.vue';
import { Panel } from '@/components/ui';

const props = defineProps({
    variant: { type: String, default: 'normal' },
});

provide('dataTableVariant', props.variant);

const tableCellVariants = cva({
    base: 'border-t border-gray-200 dark:border-white/10',
    variants: {
        position: {
            first: 'border-l rounded-tl-xl',
            firstRight: 'border-r rounded-tr-xl',
            last: 'border-l rounded-bl-xl',
            lastRight: 'border-r rounded-br-xl',
            left: 'border-l',
            right: 'border-r',
            middle: '',
        },
    },
});

const titleClasses = cva({
    base: 'font-medium text-gray-900 dark:text-white',
    variants: {
        variant: {
            normal: 'text-base',
            compact: 'text-sm',
        },
    },
})({ ...props });
</script>

<template>
    <Panel class="relative overflow-x-auto overscroll-x-contain">
        <table
            data-ui-data-table
            class="w-full min-w-full table-fixed border-separate border-spacing-y-0 whitespace-nowrap text-gray-500 antialiased"
        >
            <DataTableColumns>
                <slot name="columns" />
            </DataTableColumns>
            <DataTableRows>
                <slot name="rows" />
            </DataTableRows>
        </table>
    </Panel>
</template>

<style></style>
