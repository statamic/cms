<script setup>
import Head from '@/pages/layout/Head.vue';
import { Header, Button, CardPanel, Table, TableColumns, TableColumn, TableRows, TableRow, TableCell, Badge, ErrorMessage, DocsCallout } from '@ui';
import { router } from '@inertiajs/vue3';

const props = defineProps(['indexes', 'updateUrl', 'errors']);

function updateAll() {
    const indexes = props.indexes.map(index => `${index.name}::${index.locale}`);
    router.post(props.updateUrl, { indexes });
}

function updateIndex(index) {
    const indexes = [`${index.name}::${index.locale}`];
    router.post(props.updateUrl, { indexes });
}
</script>

<template>
    <Head :title="__('Search')" />

    <Header :title="__('Search')" icon="magnifying-glass">
        <Button variant="primary" @click="updateAll">
            {{ __('Update Indexes') }}
        </Button>
    </Header>

    <CardPanel :heading="__('Search Indexes')">
        <ErrorMessage v-if="errors.indexes" :text="errors.indexes[0]" class="p-4" />

        <Table>
            <TableColumns>
                <TableColumn v-text="__('Index')" />
                <TableColumn v-text="__('Driver')" />
                <TableColumn v-text="__('Searchables')" />
                <TableColumn v-text="__('Fields')" />
                <TableColumn />
            </TableColumns>
            <TableRows>
                <TableRow v-for="index in indexes" :key="`${index.name}::${index.locale}`">
                    <TableCell>
                        <div class="flex items-start">
                            <div class="-mt-0.5 flex size-6 shrink-0 me-2 text-gray-500" v-html="index.driverIcon" />
                            <span class="text-gray-900 dark:text-gray-200" v-text="index.title" />
                        </div>
                    </TableCell>
                    <TableCell>
                        {{ index.driver.charAt(0).toUpperCase() + index.driver.slice(1) }}
                    </TableCell>
                    <TableCell>
                        <div v-if="typeof index.searchables === 'string'" class="flex flex-wrap">
                            <Badge v-text="index.searchables" />
                        </div>
                        <div v-else class="flex flex-wrap gap-1 text-sm text-gray">
                            <Badge v-for="searchable in index.searchables" :key="searchable" v-text="searchable" />
                        </div>
                    </TableCell>
                    <TableCell>
                        <div class="flex flex-wrap gap-2">
                            <Badge v-for="field in index.fields" :key="field" v-text="field" />
                        </div>
                    </TableCell>
                    <TableCell class="text-right rtl:text-left">
                        <Button size="sm" @click="updateIndex(index)">
                            {{ __('Update') }}
                        </Button>
                    </TableCell>
                </TableRow>
            </TableRows>
        </Table>
    </CardPanel>

    <DocsCallout :topic="__('Search Indexes')" url="search#indexes" />
</template>