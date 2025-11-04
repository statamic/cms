<script setup>
import { ref, computed, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import Head from '@/pages/layout/Head.vue';
import { Header, Button, DocsCallout, DropdownItem, Listing } from '@ui';
import axios from 'axios';

const props = defineProps(['blueprints', 'reorderUrl', 'createUrl']);

const rows = ref(props.blueprints);
const hasBeenReordered = ref(false);

const reorderable = computed(() => rows.value.length > 1);

const columns = ref([
    { label: __('Title'), field: 'title' },
    { label: __('Handle'), field: 'handle' },
    { label: __('Fields'), field: 'fields' },
]);

watch(
    () => props.blueprints,
    (newRows) => (rows.value = newRows),
    { deep: true },
);

function reordered(newRows) {
    rows.value = newRows;
    hasBeenReordered.value = true;
}

function saveOrder() {
    const order = rows.value.map((blueprint) => blueprint.handle);

    axios
        .post(props.reorderUrl, { order })
        .then(() => {
            Statamic.$toast.success(__('Blueprints successfully reordered'));
            hasBeenReordered.value = false;
        })
        .catch(() => Statamic.$toast.error(__('Something went wrong')));
}

function reloadPage() {
    router.reload();
}

function removeRow(row) {
    const i = rows.value.findIndex((r) => r.id === row.id);
    rows.value.splice(i, 1);
}
</script>

<template>
    <Head :title="__('Blueprints')" />

    <Header :title="__('Blueprints')" icon="blueprints">
        <Button v-if="reorderable" :disabled="!hasBeenReordered" @click="saveOrder">
            {{ __('Save Order') }}
        </Button>

        <Button :text="__('Create Blueprint')" :href="createUrl" variant="primary" />
    </Header>

    <Listing
        :items="rows"
        :columns="columns"
        :allow-search="false"
        :allow-customizing-columns="false"
        :reorderable="reorderable"
        :sortable="false"
        :allow-actions-while-reordering="true"
        @refreshing="reloadPage"
        @reordered="reordered"
    >
        <template #cell-title="{ row: blueprint }">
            <div class="flex items-center">
                <div class="little-dot me-2" :class="[blueprint.hidden ? 'hollow' : 'bg-green-600']" />
                <Link :href="blueprint.edit_url" v-text="__(blueprint.title)" />

                <resource-deleter
                    :ref="`deleter_${blueprint.id}`"
                    :resource="blueprint"
                    @deleted="removeRow(blueprint)"
                />
            </div>
        </template>
        <template #cell-handle="{ value }">
            <span class="font-mono text-xs" v-text="value" />
        </template>
        <template #prepended-row-actions="{ row: blueprint }">
            <DropdownItem :text="__('Edit')" icon="edit" :href="blueprint.edit_url" />
            <DropdownItem
                :text="__('Delete')"
                icon="trash"
                variant="destructive"
                @click="$refs[`deleter_${blueprint.id}`].confirm()"
            />
        </template>
    </Listing>

    <DocsCallout :topic="__('Blueprints')" url="blueprints" />
</template>
