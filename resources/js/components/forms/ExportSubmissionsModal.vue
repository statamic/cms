<script setup>
import { ref, computed } from 'vue';
import { Modal, Button, RadioGroup, Radio, CheckboxGroup, Checkbox } from '@ui';

const emit = defineEmits(['close']);

const props = defineProps({
    exporters: { type: Array, required: true },
    columns: { type: Array, required: true },
    listingParameters: { type: Object, default: () => ({}) },
});

const format = ref(props.exporters[0]?.handle ?? null);
const scope = ref('all');
const selectedColumns = ref(props.columns.map((column) => column.handle));

const hasFilteredScope = computed(() => {
    const params = props.listingParameters;
    const hasSortOverride = (params.sort && params.sort !== 'datestamp') || (params.order && params.order !== 'desc');
    return !!(params.search || params.filters || hasSortOverride);
});

const selectedExporter = computed(() => props.exporters.find((exporter) => exporter.handle === format.value));

const canSelectColumns = computed(() => selectedExporter.value?.supportsColumnSelection ?? false);

const allColumnsSelected = computed(() => selectedColumns.value.length === props.columns.length);

const canExport = computed(() => selectedExporter.value && (!canSelectColumns.value || selectedColumns.value.length > 0));

function toggleAllColumns() {
    selectedColumns.value = allColumnsSelected.value ? [] : props.columns.map((column) => column.handle);
}

function exportSubmissions() {
    if (!canExport.value) return;

    const query = new URLSearchParams();

    if (scope.value === 'filtered') {
        const params = props.listingParameters;
        if (params.search) query.set('search', params.search);
        if (params.sort) query.set('sort', params.sort);
        if (params.order) query.set('order', params.order);
        if (params.filters) query.set('filters', params.filters);
    }

    if (canSelectColumns.value && !allColumnsSelected.value) {
        query.set('columns', selectedColumns.value.join(','));
    }

    let url = selectedExporter.value.downloadUrl;

    if (query.size) {
        const separator = url.includes('?') ? '&' : '?';
        url += separator + query.toString();
    }

    window.open(url, '_blank');
    emit('close');
}
</script>

<template>
    <Modal :title="__('Export Submissions')" open @update:open="emit('close')">
        <div class="space-y-6">
            <div>
                <label class="text-sm font-medium mb-1.5 block">{{ __('Format') }}</label>
                <RadioGroup v-model="format" inline>
                    <Radio v-for="exporter in exporters" :key="exporter.handle" :value="exporter.handle" :label="exporter.title" />
                </RadioGroup>
            </div>

            <div>
                <label class="text-sm font-medium mb-1.5 block">{{ __('Submissions') }}</label>
                <RadioGroup v-model="scope">
                    <Radio value="all" :label="__('All Submissions')" />
                    <Radio
                        value="filtered"
                        :label="__('Filtered Submissions')"
                        :description="__('statamic::messages.form_export_filtered_description')"
                        :disabled="!hasFilteredScope"
                    />
                </RadioGroup>
            </div>

            <div v-if="canSelectColumns">
                <div class="flex items-center justify-between mb-1.5">
                    <label class="text-sm font-medium block">{{ __('Columns') }}</label>
                    <button
                        type="button"
                        class="cursor-pointer text-xs text-gray-500 hover:text-gray-800 dark:hover:text-gray-200"
                        @click="toggleAllColumns"
                    >
                        {{ allColumnsSelected ? __('Deselect All') : __('Select All') }}
                    </button>
                </div>
                <div class="max-h-48 overflow-y-auto rounded-lg border border-gray-200 dark:border-gray-700 p-3">
                    <CheckboxGroup v-model="selectedColumns">
                        <Checkbox v-for="column in columns" :key="column.handle" :value="column.handle" :label="column.title" />
                    </CheckboxGroup>
                </div>
            </div>
        </div>

        <template #footer>
            <div class="flex justify-end p-2">
                <Button variant="primary" :text="__('Export')" :disabled="!canExport" @click="exportSubmissions" />
            </div>
        </template>
    </Modal>
</template>
