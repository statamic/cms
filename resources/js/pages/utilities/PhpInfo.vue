<script setup>
import Head from '@/pages/layout/Head.vue';
import { Header, CardPanel, Table, TableRow, TableCell } from '@ui';

const props = defineProps(['phpinfo', 'version']);
</script>

<template>
    <Head :title="__('PHP Info')" />

    <Header :title="__('PHP Info')" icon="info" />

    <section class="space-y-6">
        <CardPanel :heading="__('PHP version')">
            <Table>
                <TableRow>
                    <TableCell width="30%">{{ __('PHP Version') }}</TableCell>
                    <TableCell>{{ version }}</TableCell>
                </TableRow>
            </Table>
        </CardPanel>

        <CardPanel v-for="(items, section) in phpinfo" :key="section" :heading="section">
            <Table>
                <TableRow v-for="(value, name) in items" :key="name">
                    <TableCell width="30%">{{ name }}</TableCell>
                    <TableCell>{{ Array.isArray(value) ? value.join(', ') : value }}</TableCell>
                </TableRow>
            </Table>
        </CardPanel>
    </section>
</template>
