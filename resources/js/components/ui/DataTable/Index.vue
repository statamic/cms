<script setup>
import { ref } from 'vue';
import { cva } from 'cva';

const events = ref([
    { title: 'Miami, Florida', slug: 'miami-florida', date: '2025-03-16', author: 'Jack McDade' },
    { title: 'Tampa, Florida', slug: 'tampa-fl', date: '2025-03-17', author: 'Jack McDade' },
    { title: 'Orlando, Florida', slug: 'orlando-fl', date: '2025-03-18', author: 'Jack McDade' },
    { title: 'Fort Lauderdale, Florida', slug: 'fort-lauderdale-fl', date: '2025-03-19', author: 'Jack McDade' },
    { title: 'Miami, Florida', slug: 'miami-florida', date: '2025-03-16', author: 'Jack McDade' },
    { title: 'Tampa, Florida', slug: 'tampa-fl', date: '2025-03-17', author: 'Jack McDade' },
    { title: 'Orlando, Florida', slug: 'orlando-fl', date: '2025-03-18', author: 'Jack McDade' },
    { title: 'Fort Lauderdale, Florida', slug: 'fort-lauderdale-fl', date: '2025-03-19', author: 'Jack McDade' },
    { title: 'Miami, Florida', slug: 'miami-florida', date: '2025-03-16', author: 'Jack McDade' },
    { title: 'Tampa, Florida', slug: 'tampa-fl', date: '2025-03-17', author: 'Jack McDade' },
    { title: 'Orlando, Florida', slug: 'orlando-fl', date: '2025-03-18', author: 'Jack McDade' },
    { title: 'Fort Lauderdale, Florida', slug: 'fort-lauderdale-fl', date: '2025-03-19', author: 'Jack McDade' },
    { title: 'Miami, Florida', slug: 'miami-florida', date: '2025-03-16', author: 'Jack McDade' },
    { title: 'Tampa, Florida', slug: 'tampa-fl', date: '2025-03-17', author: 'Jack McDade' },
    { title: 'Orlando, Florida', slug: 'orlando-fl', date: '2025-03-18', author: 'Jack McDade' },
    { title: 'Fort Lauderdale, Florida', slug: 'fort-lauderdale-fl', date: '2025-03-19', author: 'Jack McDade' },
    { title: 'Miami, Florida', slug: 'miami-florida', date: '2025-03-16', author: 'Jack McDade' },
    { title: 'Tampa, Florida', slug: 'tampa-fl', date: '2025-03-17', author: 'Jack McDade' },
    { title: 'Orlando, Florida', slug: 'orlando-fl', date: '2025-03-18', author: 'Jack McDade' },
    { title: 'Fort Lauderdale, Florida', slug: 'fort-lauderdale-fl', date: '2025-03-19', author: 'Jack McDade' },
    { title: 'Miami, Florida', slug: 'miami-florida', date: '2025-03-16', author: 'Jack McDade' },
    { title: 'Tampa, Florida', slug: 'tampa-fl', date: '2025-03-17', author: 'Jack McDade' },
    { title: 'Orlando, Florida', slug: 'orlando-fl', date: '2025-03-18', author: 'Jack McDade' },
    { title: 'Fort Lauderdale, Florida', slug: 'fort-lauderdale-fl', date: '2025-03-19', author: 'Jack McDade' },
    { title: 'Miami, Florida', slug: 'miami-florida', date: '2025-03-16', author: 'Jack McDade' },
    { title: 'Tampa, Florida', slug: 'tampa-fl', date: '2025-03-17', author: 'Jack McDade' },
    { title: 'Orlando, Florida', slug: 'orlando-fl', date: '2025-03-18', author: 'Jack McDade' },
    { title: 'Fort Lauderdale, Florida', slug: 'fort-lauderdale-fl', date: '2025-03-19', author: 'Jack McDade' },
    { title: 'Miami, Florida', slug: 'miami-florida', date: '2025-03-16', author: 'Jack McDade' },
    { title: 'Tampa, Florida', slug: 'tampa-fl', date: '2025-03-17', author: 'Jack McDade' },
    { title: 'Orlando, Florida', slug: 'orlando-fl', date: '2025-03-18', author: 'Jack McDade' },
    { title: 'Fort Lauderdale, Florida', slug: 'fort-lauderdale-fl', date: '2025-03-19', author: 'Jack McDade' },
    { title: 'Miami, Florida', slug: 'miami-florida', date: '2025-03-16', author: 'Jack McDade' },
    { title: 'Tampa, Florida', slug: 'tampa-fl', date: '2025-03-17', author: 'Jack McDade' },
    { title: 'Orlando, Florida', slug: 'orlando-fl', date: '2025-03-18', author: 'Jack McDade' },
    { title: 'Fort Lauderdale, Florida', slug: 'fort-lauderdale-fl', date: '2025-03-19', author: 'Jack McDade' },
    { title: 'Miami, Florida', slug: 'miami-florida', date: '2025-03-16', author: 'Jack McDade' },
    { title: 'Tampa, Florida', slug: 'tampa-fl', date: '2025-03-17', author: 'Jack McDade' },
    { title: 'Orlando, Florida', slug: 'orlando-fl', date: '2025-03-18', author: 'Jack McDade' },
    { title: 'Fort Lauderdale, Florida', slug: 'fort-lauderdale-fl', date: '2025-03-19', author: 'Jack McDade' },
    { title: 'Miami, Florida', slug: 'miami-florida', date: '2025-03-16', author: 'Jack McDade' },
    { title: 'Tampa, Florida', slug: 'tampa-fl', date: '2025-03-17', author: 'Jack McDade' },
    { title: 'Orlando, Florida', slug: 'orlando-fl', date: '2025-03-18', author: 'Jack McDade' },
    { title: 'Fort Lauderdale, Florida', slug: 'fort-lauderdale-fl', date: '2025-03-19', author: 'Jack McDade' },
    { title: 'Miami, Florida', slug: 'miami-florida', date: '2025-03-16', author: 'Jack McDade' },
    { title: 'Tampa, Florida', slug: 'tampa-fl', date: '2025-03-17', author: 'Jack McDade' },
    { title: 'Orlando, Florida', slug: 'orlando-fl', date: '2025-03-18', author: 'Jack McDade' },
    { title: 'Fort Lauderdale, Florida', slug: 'fort-lauderdale-fl', date: '2025-03-19', author: 'Jack McDade' },
    { title: 'Miami, Florida', slug: 'miami-florida', date: '2025-03-16', author: 'Jack McDade' },
    { title: 'Tampa, Florida', slug: 'tampa-fl', date: '2025-03-17', author: 'Jack McDade' },
    { title: 'Orlando, Florida', slug: 'orlando-fl', date: '2025-03-18', author: 'Jack McDade' },
    { title: 'Fort Lauderdale, Florida', slug: 'fort-lauderdale-fl', date: '2025-03-19', author: 'Jack McDade' },
    { title: 'Miami, Florida', slug: 'miami-florida', date: '2025-03-16', author: 'Jack McDade' },
    { title: 'Tampa, Florida', slug: 'tampa-fl', date: '2025-03-17', author: 'Jack McDade' },
    { title: 'Orlando, Florida', slug: 'orlando-fl', date: '2025-03-18', author: 'Jack McDade' },
    { title: 'Fort Lauderdale, Florida', slug: 'fort-lauderdale-fl', date: '2025-03-19', author: 'Jack McDade' },
    { title: 'Miami, Florida', slug: 'miami-florida', date: '2025-03-16', author: 'Jack McDade' },
    { title: 'Tampa, Florida', slug: 'tampa-fl', date: '2025-03-17', author: 'Jack McDade' },
    { title: 'Orlando, Florida', slug: 'orlando-fl', date: '2025-03-18', author: 'Jack McDade' },
    { title: 'Fort Lauderdale, Florida', slug: 'fort-lauderdale-fl', date: '2025-03-19', author: 'Jack McDade' },
    { title: 'Miami, Florida', slug: 'miami-florida', date: '2025-03-16', author: 'Jack McDade' },
    { title: 'Tampa, Florida', slug: 'tampa-fl', date: '2025-03-17', author: 'Jack McDade' },
    { title: 'Orlando, Florida', slug: 'orlando-fl', date: '2025-03-18', author: 'Jack McDade' },
    { title: 'Fort Lauderdale, Florida', slug: 'fort-lauderdale-fl', date: '2025-03-19', author: 'Jack McDade' },
]);

const props = defineProps({
    variant: { type: String, default: 'normal' },
});

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
})

const headerCellClasses = cva({
    base: 'text-sm font-medium text-gray-800 dark:text-gray-300',
    variants: {
        variant: {
            normal: 'px-4 py-2',
            compact: 'px-2 py-1.5',
        },
    },
})({ ...props });

const bodyCellClasses = cva({
    base: '',
    variants: {
        variant: {
            normal: 'px-4 py-3',
            compact: 'px-2 py-1.5',
        },
    },
})({ ...props });

const titleClasses = cva({
    base: 'font-medium text-gray-800 dark:text-white',
    variants: {
        variant: {
            normal: 'text-base',
            compact: 'text-sm',
        },
    },
})({ ...props });

</script>

<template>
<ui-panel class="relative overflow-x-auto overscroll-x-contain">
    <table data-ui-data-table class="w-full text-gray-500 antialiased table-fixed border-separate border-spacing-y-0 whitespace-nowrap min-w-full">
        <thead>
            <tr>
                <th class="text-left" :class="headerCellClasses">Title</th>
                <th class="text-left" :class="headerCellClasses">Slug</th>
                <th class="text-left" :class="headerCellClasses">Date</th>
                <th class="text-left" :class="headerCellClasses">Author</th>
                <th class="text-right w-14" :class="headerCellClasses"></th>
            </tr>
        </thead>
        <tbody class="text-sm bg-white dark:bg-gray-900 rounded-xl shadow-sm">
            <tr v-for="(event, index) in events" :key="event.id">
                <td :class="[bodyCellClasses, tableCellVariants({ position: index === 0 ? 'first' : index === events.length - 1 ? 'last' : 'left' })]">
                    <div :class="titleClasses">{{ event.title }}</div>
                </td>
                <td :class="[bodyCellClasses, tableCellVariants()]">{{ event.slug }}</td>
                <td :class="[bodyCellClasses, tableCellVariants()]">{{ event.date }}</td>
                <td :class="[bodyCellClasses, tableCellVariants()]">
                    <div class="flex items-center gap-2">
                        <img class="size-5 rounded-full" src="https://unavatar.io/jackmcdade" />
                        {{ event.author }}
                    </div>
                </td>
                <td class="text-right" :class="[bodyCellClasses, tableCellVariants({ position: index === 0 ? 'firstRight' : index === events.length - 1 ? 'lastRight' : 'right' })]">
                    <ui-button icon="dots" variant="ghost" size="xs" />
                </td>
            </tr>
        </tbody>
    </table>
</ui-panel>
</template>

<style>

</style>
