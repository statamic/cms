<script setup>
import { Form } from '@inertiajs/vue3';
import Head from '@/pages/layout/Head.vue';
import { Header, Panel, Card, Table, TableRow, TableCell, Button } from '@/components/ui';

defineProps({
    duplicates: Object,
    regenerateUrl: String,
});
</script>

<template>
    <Head :title="__('Duplicate IDs')" />

    <Header icon="duplicate" :title="__('Duplicate IDs')"></Header>

    <div
        v-if="duplicates.length === 0"
        class="rounded-lg border border-dashed border-gray-300 p-6 text-center text-gray-500"
        v-text="__('No items with duplicate IDs.')"
    />

    <Panel v-else v-for="(paths, id) in duplicates" :heading="id">
        <Card class="py-0!">
            <Table>
                <TableRow v-for="path in paths">
                    <TableCell class="font-mono">
                        {{ path }}
                    </TableCell>
                    <TableCell class="flex items-center justify-end">
                        <Form method="POST" :action="regenerateUrl">
                            <input type="hidden" name="path" :value="path" />
                            <Button size="sm" type="submit">{{ __('Regenerate') }}</Button>
                        </Form>
                    </TableCell>
                </TableRow>
            </Table>
        </Card>
    </Panel>
</template>
