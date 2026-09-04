<script setup lang="ts">
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import axios from 'axios';
import { keys, preferences } from '@api';
import { Button, Skeleton, ToggleGroup, ToggleItem, Widget } from '@ui';
import { injectListingContext } from '@/components/ui/Listing/Listing.vue';
import { SortableList } from '@/components/sortable/Sortable.js';
import { clone, utf8btoa } from '@/bootstrap/globals.js';
import { useFieldNumberingPreference } from '@/composables/forms/field-numbering';
import FieldNumberingToggle from '@/components/forms/FieldNumberingToggle.vue';
import ChartWidget from './ChartWidget.vue';
import EditChrome from './EditChrome.vue';
import FieldPicker from './FieldPicker.vue';
import type { ChartConfig, MetaChart, MetaField, Summary, SummaryField } from './types';
import { ChartMetric } from './types';

interface SummaryWidget {
    id: string;
    config: ChartConfig | null;
    field: SummaryField | null;
}

const props = withDefaults(
    defineProps<{
        form: string;
        summaryUrl: string;
        chartsUpdateUrl: string;
        canEdit?: boolean;
    }>(),
    {
        canEdit: false,
    },
);

let abortController: AbortController | null = null;
let saveBinding: { destroy: () => void } | null = null;

const { showFieldNumbers } = useFieldNumberingPreference();
const { activeFilters, searchQuery, preferencesPrefix } = injectListingContext();

const summary = ref<Summary | null>(null);
const editing = ref<boolean>(false);
const draftLayout = ref<ChartConfig[]>([]);
const saving = ref<boolean>(false);
const loadingPreviews = ref<string[]>([]);
const isDragging = ref<boolean>(false);
const metric = ref<ChartMetric>(preferences.get(`${preferencesPrefix.value}.summary.chart_metric`, ChartMetric.Percent));

const sortableOptions = {
    swapAnimation: {
        duration: 220,
        easingFunction: 'cubic-bezier(0.22, 1, 0.36, 1)',
        horizontal: true,
        vertical: true,
    },
    mirror: {
        constrainDimensions: true,
        appendTo: 'body',
    },
};

const availableCharts = computed<MetaChart[]>(() => summary.value?.meta?.charts ?? []);
const chartableFields = computed<MetaField[]>(() => summary.value?.meta?.fields ?? []);

const addableFields = computed<MetaField[]>(() => {
    const used = draftLayout.value.map((item) => item.field);

    return chartableFields.value.filter((field) => !used.includes(field.handle));
});

const widgets = computed<SummaryWidget[]>(() => {
    if (!editing.value) {
        return (summary.value?.fields ?? []).map((field) => ({
            id: field.handle,
            config: null,
            field,
        }));
    }

    return draftLayout.value.map((item) => ({
        id: item.field,
        config: item,
        field: summarizedField(item.field) ?? null,
    }));
});

const missingPreviews = computed<ChartConfig[]>(() =>
    draftLayout.value.filter((item) => !isSummarized(item) && !loadingPreviews.value.includes(item.field)),
);

function chartableField(handle: string): MetaField | undefined {
    return chartableFields.value.find((field) => field.handle === handle);
}

function summarizedField(handle: string): SummaryField | undefined {
    return summary.value?.fields.find((field) => field.handle === handle);
}

function isSummarized(config: ChartConfig): boolean {
    return summarizedField(config.field)?.chart.handle === config.chart;
}

function parameters(): object {
    const params = {};

    if (searchQuery.value) params.search = searchQuery.value;
    if (Object.keys(activeFilters.value).length) params.filters = utf8btoa(JSON.stringify(activeFilters.value));
    if (editing.value) params.charts = draftLayout.value;

    return params;
}

async function fetchSummary() {
    abortController?.abort();
    abortController = new AbortController();

    try {
        const response = await axios.get(props.summaryUrl, {
            params: parameters(),
            signal: abortController.signal,
        });
        summary.value = response.data;
    } catch (error) {
        if (!axios.isCancel(error)) {
            Statamic.$toast.error(error?.response?.data?.message ?? __('Something went wrong'));
        }
    }
}

async function fetchPreviews(charts: ChartConfig[]) {
    const handles = charts.map((item) => item.field);

    loadingPreviews.value.push(...handles);

    try {
        const response = await axios.get(props.summaryUrl, { params: { ...parameters(), charts } });
        const previews: SummaryField[] = response.data.fields;

        summary.value!.fields = [
            ...summary.value!.fields.filter((field) => !previews.some((preview) => preview.handle === field.handle)),
            ...previews,
        ];
    } catch (error) {
        Statamic.$toast.error(error?.response?.data?.message ?? __('Something went wrong'));
    } finally {
        loadingPreviews.value = loadingPreviews.value.filter((handle) => !handles.includes(handle));
    }
}

function startEditing() {
    draftLayout.value = (summary.value?.fields ?? []).map((field) => ({
        field: field.handle,
        chart: field.chart.handle,
    }));

    editing.value = true;
}

function cancelEditing() {
    editing.value = false;
    draftLayout.value = [];
    fetchSummary();
}

const fieldPicked = (field: MetaField) => draftLayout.value.push({ field: field.handle, chart: field.default_chart });

const setChart = (index: number, chart: string) => draftLayout.value.splice(index, 1, { ...draftLayout.value[index], chart });

const removeChart = (index: number) => draftLayout.value.splice(index, 1);

function onSort(sortedWidgets: SummaryWidget[]) {
    if (!editing.value) return;

    draftLayout.value = sortedWidgets.map((widget) => clone(widget.config!));
}

async function save() {
    if (saving.value) return;

    saving.value = true;

    try {
        await axios.patch(props.chartsUpdateUrl, { charts: draftLayout.value });
        editing.value = false;
        Statamic.$toast.success(__('Saved'));
        await fetchSummary();
    } catch (error) {
        Statamic.$toast.error(error?.response?.data?.message ?? __('Something went wrong'));
    } finally {
        saving.value = false;
    }
}

watch(editing, (editing): void => {
    saveBinding?.destroy();
    saveBinding = null;

    if (editing) {
        saveBinding = keys.bindGlobal(['mod+s'], (e: KeyboardEvent) => {
            e.preventDefault();
            save();
        });
    }
});

watch([activeFilters, searchQuery], fetchSummary, { deep: true, immediate: true });

watch(missingPreviews, (missing) => editing.value && missing.length && fetchPreviews(missing));

watch(metric, (metric: ChartMetric) => preferences.set(`${preferencesPrefix.value}.summary.chart_metric`, metric));

onBeforeUnmount(() => saveBinding?.destroy());

defineExpose({ refresh: fetchSummary });
</script>

<template>
    <div data-submission-summary class="mt-1.5 pb-8">
        <div class="flex w-full items-center gap-2 mb-3">
            <FieldNumberingToggle />
            <ToggleGroup v-model="metric" size="xs">
                <ToggleItem :value="ChartMetric.Percent" label="%" :aria-label="__('Percentage')" v-tooltip="__('Percentage')" />
                <ToggleItem
                    :value="ChartMetric.Count"
                    icon="layout-list-small"
                    :aria-label="__('Response count')"
                    v-tooltip="__('Response count')"
                />
            </ToggleGroup>
            <p v-if="summary" class="ms-1 text-sm text-gray-500 dark:text-gray-500">
                {{ __(':count responses', { count: $number.format(summary.total) }) }}
            </p>
            <div class="ms-auto flex items-center gap-2">
                <template v-if="editing">
                    <FieldPicker v-if="addableFields.length" :fields="addableFields" @picked="fieldPicked" />
                    <Button size="sm" :text="__('Cancel')" @click="cancelEditing" />
                    <Button size="sm" :text="__('Save')" variant="primary" :disabled="saving" @click="save" />
                </template>
                <Button
                    v-else-if="canEdit && summary"
                    size="sm"
                    :text="__('Customize Charts')"
                    icon="edit"
                    @click="startEditing"
                />
            </div>
        </div>

        <div v-if="!summary" class="@container/widgets widgets flex flex-wrap gap-y-6 -mx-2 sm:-mx-3">
            <div v-for="n in 4" :key="n" class="w-full min-h-61 min-[1100px]:w-1/2 px-3">
                <Skeleton class="h-61 w-full rounded-xl" />
            </div>
        </div>

        <SortableList
            v-else
            :model-value="widgets"
            item-class="summary-chart-sortable"
            handle-class="summary-chart-handle"
            :disabled="!editing"
            :animate="true"
            :constrain-dimensions="true"
            :distance="8"
            :options="sortableOptions"
            @dragstart="isDragging = true"
            @dragend="isDragging = false"
            @update:model-value="onSort"
        >
            <div class="@container/widgets widgets flex flex-wrap gap-y-6 -mx-2 sm:-mx-3">
                <div
                    v-for="(widget, index) in widgets"
                    :key="widget.id"
                    class="summary-chart-sortable w-full min-h-61 min-[1100px]:w-1/2 px-3"
                    :class="{ 'starting-style-transition': !editing && !isDragging }"
                >
                    <ChartWidget
                        v-if="widget.field"
                        :field="widget.field"
                        :metric="metric"
                        :show-number="showFieldNumbers"
                        :editing="editing"
                    >
                        <template v-if="editing" #chrome>
                            <EditChrome
                                :config="widget.config"
                                :charts="availableCharts"
                                :loading="loadingPreviews.includes(widget.config.field)"
                                @update:chart="setChart(index, $event)"
                                @remove="removeChart(index)"
                            />
                        </template>
                    </ChartWidget>
                    <Widget
                        v-else
                        :title="__(chartableField(widget.config.field)?.display ?? widget.config.field)"
                        title-tag="h2"
                        class="h-full"
                        :class="{ 'shadow-none! ring-0!': editing }"
                        :header-class="editing ? 'summary-chart-handle cursor-grab rounded-t-xl border border-dashed border-gray-400 dark:border-gray-700' : undefined"
                        :icon="chartableField(widget.config.field)?.icon"
                        icon-class="hidden @xs/widget:block size-4 text-gray-500"
                    >
                        <template v-if="editing" #actions>
                            <EditChrome
                                :config="widget.config"
                                :charts="availableCharts"
                                :loading="loadingPreviews.includes(widget.config.field)"
                                @update:chart="setChart(index, $event)"
                                @remove="removeChart(index)"
                            />
                        </template>
                        <div
                            class="relative flex-1 overflow-hidden rounded-b-xl"
                            :class="
                                editing
                                    ? 'summary-chart-handle cursor-grab border border-t-0 border-dashed border-gray-400 dark:border-gray-700 active:cursor-grabbing'
                                    : undefined
                            "
                        >
                            <Skeleton class="h-full w-full" />
                        </div>
                    </Widget>
                </div>
                <div
                    v-if="!widgets.length"
                    class="w-full mx-3 flex min-h-61 flex-col items-center justify-center gap-3 rounded-xl border border-dashed border-gray-300 dark:border-gray-600"
                >
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ editing ? __('statamic::messages.form_summary_add_chart_instructions') : __('No charts to show.') }}
                    </p>
                    <FieldPicker v-if="editing && addableFields.length" :fields="addableFields" @picked="fieldPicked" />
                </div>
            </div>
        </SortableList>
    </div>
</template>
