<script lang="ts">
export enum View {
    Grid = 'grid',
    List = 'list',
}
</script>

<script setup lang="ts">
import { Badge, Listing, ListingTable } from '@ui';
import { Link } from '@inertiajs/vue3';

type Connector = {
    handle: string;
    title: string;
    description: string;
    icon: string;
    developer: string;
    count: number | null;
    url?: string;
}

withDefaults(defineProps<{
    connectors: Connector[],
    linkable?: boolean,
    view?: View,
}>(), {
    linkable: true,
    view: View.Grid,
});

defineEmits<{
    select: [handle: string],
}>();
</script>

<style>
#connectors-listing tbody td {
    @apply rounded-t-none border-x-0;
}
</style>

<template>
    <div class="@container">
        <div v-if="view === View.Grid" class="grid gap-4 grid-cols-2 @sm:grid-cols-3 @lg:grid-cols-4 @3xl:grid-cols-6 @6xl:grid-cols-8 @7xl:grid-cols-10">
            <div
                v-for="connector in connectors"
                :key="connector.handle"
                class="space-y-2"
            >
                <component
                    :is="linkable ? Link : 'button'"
                    :href="linkable ? connector.url : undefined"
                    type="button"
                    :aria-label="__(connector.title)"
                    class="relative flex mb-2 aspect-square w-full cursor-pointer items-center justify-center rounded-lg border border-gray-300 bg-gray-50/30 p-8 text-gray-700 hover:bg-gray-100/50 dark:border-gray-700 dark:bg-gray-950/40 dark:text-gray-300 dark:hover:bg-gray-900"
                    @click="linkable ? null : $emit('select', connector.handle)"
                >
                    <span class="[&_svg]:size-12" aria-hidden="true" v-html="connector.icon" />
                </component>
                <div class="flex items-center justify-center gap-1.5 text-gray-800 dark:text-gray-200">
                    <Badge v-if="connector.count" size="sm" color="white" pill>
                        {{ connector.count }}
                    </Badge>
                    <span class="truncate text-xs">{{ __(connector.title) }}</span>
                </div>
            </div>
        </div>
        <div v-else>
            <Listing
                id="connectors-listing"
                class="pt-1"
                :items="connectors"
                :columns="[
                    { field: 'title', label: __('Connector'), sortable: true, visible: true },
                    { field: 'description', label: __('Description'), sortable: false, visible: true },
                    { field: 'developer', label: __('Developer'), sortable: true, visible: true },
                ]"
                :searchable="false"
                :allow-customizing-columns="false"
            >
                <ListingTable>
                    <template #cell-title="{ row: connector }">
                        <component
                            :is="linkable ? Link : 'button'"
                            :href="linkable ? connector.url : undefined"
                            type="button"
                            class="flex min-w-0 items-center gap-2 cursor-pointer"
                            @click="linkable ? null : $emit('select', connector.handle)"
                        >
                            <span class="size-7 flex items-center justify-center text-gray-700 dark:text-gray-300 [&_svg]:size-5" aria-hidden="true" v-html="connector.icon" />
                            <Badge v-if="connector.count" size="sm" color="white" pill>
                                {{ connector.count }}
                            </Badge>
                            <span class="truncate text-sm text-gray-800 dark:text-gray-200">{{ __(connector.title) }}</span>
                        </component>
                    </template>
                </ListingTable>
            </Listing>
        </div>
    </div>
</template>
