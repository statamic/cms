<script setup>
import { ref, computed, watch, useId } from 'vue';
import axios from 'axios';
import Head from '@/pages/layout/Head.vue';
import { Header, Dropdown, DropdownMenu, DropdownItem, Button, Modal, RadioGroup, Radio, CommandPaletteItem, ToggleGroup, ToggleItem, Widget, Pagination } from '@ui';
import ResourceDeleter from '@/components/ResourceDeleter.vue';
import FormSubmissionListing from '@/components/forms/SubmissionListing.vue';
import Layout from '@/pages/layout/Layout.vue';
import PanelLayout from '@/pages/layout/PanelLayout.vue';
import FormsLayout from './Layout.vue';

defineOptions({ layout: [Layout, PanelLayout, FormsLayout] });

const props = defineProps([
    'form',
    'columns',
    'filters',
    'actionUrl',
    'generateFakeSubmissionUrl',
    'exporters',
    'redirectUrl',
]);

const preferencesKey = `forms.${props.form.handle}.submissions.view`;
const savedView = Statamic.$preferences.get(preferencesKey, 'entries');
const view = ref(['entries', 'summary'].includes(savedView) ? savedView : 'entries');

watch(view, (newView) => {
    Statamic.$preferences.set(preferencesKey, newView);
});

const deleter = ref(null);
const generatingFakeSubmission = ref(false);
const deletingFakeSubmissions = ref(false);
const submissionListing = ref();
const exportModalOpen = ref(false);
const exportFormat = ref(null);
const exportScope = ref('all');
const listingParameters = ref({});
const pieChart1LegendId = useId();
const pieChart2LegendId = useId();
const pieChart2LegendPage2Id = useId();
const lollipopChart1CaptionId = useId();
const horizontalBarChart1CaptionId = useId();
const verticalBarChart1CaptionId = useId();

const verticalBarChart1Data = [
    { label: '0', percent: 2 },
    { label: '1', percent: 1 },
    { label: '2', percent: 3 },
    { label: '3', percent: 4 },
    { label: '4', percent: 6 },
    { label: '5', percent: 8 },
    { label: '6', percent: 12 },
    { label: '7', percent: 20 },
    { label: '8', percent: 35 },
    { label: '9', percent: 48 },
    { label: '10', percent: 61 },
];

const verticalBarChart1MaxValue = computed(() => Math.max(...verticalBarChart1Data.map((bar) => bar.percent), 1));

// Mock in-widget pagination for fields with more than four response options.
const wakeMeUpChartPage = ref(1);
const wakeMeUpChartPerPage = 4;
const wakeMeUpChartTotal = 8;

const wakeMeUpChartMeta = computed(() => {
    const from = (wakeMeUpChartPage.value - 1) * wakeMeUpChartPerPage + 1;
    const to = Math.min(wakeMeUpChartPage.value * wakeMeUpChartPerPage, wakeMeUpChartTotal);

    return {
        current_page: wakeMeUpChartPage.value,
        last_page: Math.ceil(wakeMeUpChartTotal / wakeMeUpChartPerPage),
        per_page: wakeMeUpChartPerPage,
        total: wakeMeUpChartTotal,
        from,
        to,
    };
});

function selectNextChartPage() {
    if (wakeMeUpChartPage.value < wakeMeUpChartMeta.value.last_page) {
        wakeMeUpChartPage.value++;
    }
}

async function generateFakeSubmission(mode) {
    if (generatingFakeSubmission.value) {
        return;
    }

    generatingFakeSubmission.value = true;

    try {
        const { data } = await axios.post(props.generateFakeSubmissionUrl, { mode });
        Statamic.$toast.success(data.message);
        submissionListing.value?.refresh();
    } catch (error) {
        const message = error?.response?.data?.message ?? __('Something went wrong');
        Statamic.$toast.error(message, { duration: null });
    } finally {
        generatingFakeSubmission.value = false;
    }
}

async function deleteFakeSubmissions() {
    if (deletingFakeSubmissions.value) {
        return;
    }

    deletingFakeSubmissions.value = true;

    try {
        const { data } = await axios.post(props.actionUrl, {
            action: 'delete_fake_submissions',
            selections: ['_all_fake_submissions_'],
            context: { form: props.form.handle },
            values: {},
        });

        const message = data?.message ?? __('Saved');

        if (data?.success === false) {
            Statamic.$toast.error(message, { duration: null });
        } else {
            Statamic.$toast.success(message);
            submissionListing.value?.refresh();
        }
    } catch (error) {
        const message = error?.response?.data?.message ?? __('Something went wrong');
        Statamic.$toast.error(message, { duration: null });
    } finally {
        deletingFakeSubmissions.value = false;
    }
}

const hasFilteredScope = computed(() => {
    const params = listingParameters.value;
    const hasSortOverride = (params.sort && params.sort !== 'datestamp') || (params.order && params.order !== 'desc');
    return !!(params.search || params.filters || hasSortOverride);
});

function openExportModal() {
    listingParameters.value = submissionListing.value?.parameters ?? {};
    exportFormat.value = props.exporters[0]?.handle ?? null;
    exportScope.value = 'all';
    exportModalOpen.value = true;
}

function exportSubmissions() {
    const exporter = props.exporters.find((e) => e.handle === exportFormat.value);
    if (!exporter) return;

    let url = exporter.downloadUrl;

    if (exportScope.value === 'filtered') {
        const params = listingParameters.value;
        const query = new URLSearchParams();
        if (params.search) query.set('search', params.search);
        if (params.sort) query.set('sort', params.sort);
        if (params.order) query.set('order', params.order);
        if (params.filters) query.set('filters', params.filters);

        const separator = url.includes('?') ? '&' : '?';
        url += separator + query.toString();
    }

    window.open(url, '_blank');
    exportModalOpen.value = false;
}
</script>

<template>
    <Head :title="[__('Results'), __(form.title), __('Forms')]" />

    <div class="max-w-5xl 3xl:max-w-6xl mx-auto" data-max-width-wrapper>
        <Header :title="__(form.title)" icon="forms">
            <Dropdown v-if="form.canEdit || form.canDelete" placement="left-start" class="me-2">
                <DropdownMenu>
                    <DropdownItem v-if="form.canEdit" :text="__('Configure Form')" icon="cog" :href="form.editUrl" />
                    <DropdownItem
                        v-if="form.canDelete"
                        :text="__('Delete Form')"
                        icon="trash"
                        variant="destructive"
                        @click="deleter.confirm()"
                    />
                </DropdownMenu>
            </Dropdown>

            <CommandPaletteItem
                category="Actions"
                :text="__('Configure Form')"
                icon="cog"
                :url="form.editUrl"
            />

            <CommandPaletteItem
                category="Actions"
                :text="__('Delete Form')"
                icon="trash"
                :action="() => deleter.confirm()"
            />

            <ResourceDeleter
                v-if="form.canDelete"
                ref="deleter"
                :resource-title="form.title"
                :route="form.deleteUrl"
                :redirect="redirectUrl"
            />

            <Button v-if="exporters.length" :text="__('Export Submissions')" @click="openExportModal" />

            <Dropdown v-if="form.canGenerateFakeSubmissions">
                <template #trigger>
                    <Button
                        :text="__('Generate Fake Submission')"
                        :loading="generatingFakeSubmission || deletingFakeSubmissions"
                        :disabled="generatingFakeSubmission || deletingFakeSubmissions"
                    />
                </template>
                <DropdownMenu>
                    <DropdownItem
                        :text="__('Submission Only')"
                        icon="flask"
                        @click="generateFakeSubmission('cp_only')"
                    />
                    <DropdownItem
                        :text="__('Submission + All Workflows')"
                        icon="rocket"
                        @click="generateFakeSubmission('full_pipeline')"
                    />
                    <DropdownItem
                        :text="__('Delete All Fake Submissions')"
                        icon="trash"
                        variant="destructive"
                        @click="deleteFakeSubmissions"
                    />
                </DropdownMenu>
            </Dropdown>

            <CommandPaletteItem
                v-if="form.canGenerateFakeSubmissions"
                category="Actions"
                :text="__('Generate Fake Submission')"
                icon="flask"
                :action="() => generateFakeSubmission('cp_only')"
            />

            <CommandPaletteItem
                v-if="form.canGenerateFakeSubmissions"
                category="Actions"
                :text="__('Generate Fake Submission + Run All Workflows')"
                icon="rocket"
                :action="() => generateFakeSubmission('full_pipeline')"
            />

            <CommandPaletteItem
                v-if="form.canGenerateFakeSubmissions"
                category="Actions"
                :text="__('Delete All Fake Submissions')"
                icon="trash"
                :action="deleteFakeSubmissions"
            />

            <CommandPaletteItem
                v-if="exporters.length"
                category="Actions"
                :text="__('Export Submissions')"
                icon="save"
                :action="openExportModal"
                prioritize
            />
        </Header>

        <FormSubmissionListing
            ref="submissionListing"
            :view="view"
            :form="form.handle"
            :action-url="actionUrl"
            sort-column="datestamp"
            sort-direction="desc"
            :columns="columns"
            :filters="filters"
        >
            <template #toolbar-actions>
                <ToggleGroup v-model="view">
                    <ToggleItem
                        value="entries"
                        icon="layout-list"
                        :aria-label="__('Entries')"
                        v-tooltip="__('Entries')"
                    />
                    <ToggleItem
                        value="summary"
                        icon="chart-increase"
                        :aria-label="__('Summary')"
                        v-tooltip="__('Summary')"
                    />
                </ToggleGroup>
            </template>

            <template #results>
                <!-- Example of a Multiple Choice field type (Pie Chart). We should dynamically generate the ids to be unique here, so that everything remains accessible. -->
                <div class="mt-6 pb-6 widgets @container/widgets flex flex-wrap gap-y-6 -mx-2 sm:-mx-3">
                    <div class="px-3 starting-style-transition w-full min-h-61 @2xl:w-1/2 @4xl:w-1/2 @7xl:w-1/3">
                        <Widget
                            :title="__('Wake me up')"
                            title-tag="h2"
                            class="h-full"
                            icon="fieldtype-radio"
                            icon-class="size-4 text-gray-500 hidden @xs/widget:block"
                        >
                            <figure class="pie-chart-figure">
                                <div
                                    class="pie-chart"
                                    style="--1: 45; --2: 30; --3: 15; --4: 10;"
                                    role="img"
                                    :aria-labelledby="pieChart1LegendId"
                                >
                                    <div class="pie-chart__disc" aria-hidden="true" />
                                    <!-- aria-hidden because the labels are already in the figure caption -->
                                    <span class="pie-chart__label | pie-chart__label--1" aria-hidden="true">45%</span>
                                    <span class="pie-chart__label | pie-chart__label--2" aria-hidden="true">30%</span>
                                    <span class="pie-chart__label | pie-chart__label--3" aria-hidden="true">15%</span>
                                    <span class="pie-chart__label | pie-chart__label--4" aria-hidden="true">10%</span>
                                </div>
                                <!-- Pie Chart 1 Legend -->
                                <figcaption :id="pieChart1LegendId" class="pie-chart-legend">
                                    <ul class="pie-chart-legend__list">
                                        <li class="pie-chart-legend__item">
                                            <span class="pie-chart-legend__value">45%</span>
                                            <div class="pie-chart-legend__swatch pie-chart-legend__swatch--1" />
                                            <span>Before you Go Go</span>
                                        </li>
                                        <li class="pie-chart-legend__item">
                                            <span class="pie-chart-legend__value">30%</span>
                                            <div class="pie-chart-legend__swatch pie-chart-legend__swatch--2" />
                                            <span>Bring me Back to Life</span>
                                        </li>
                                        <li class="pie-chart-legend__item">
                                            <span class="pie-chart-legend__value">15%</span>
                                            <div class="pie-chart-legend__swatch pie-chart-legend__swatch--3" />
                                            <span>When September Ends</span>
                                        </li>
                                        <li class="pie-chart-legend__item">
                                            <span class="pie-chart-legend__value">10%</span>
                                            <div class="pie-chart-legend__swatch pie-chart-legend__swatch--4" />
                                            <span>Never</span>
                                        </li>
                                    </ul>
                                </figcaption>
                            </figure>
                        </Widget>
                    </div>
                    <!-- Example of a Multiple Choice field type (Pie Chart) with Pagination. The idea here is when there are _more_ than four response options, we can paginate the chart. In such a case, the fourth option shows as "Other", and the second widget shows the "other" segment broken down into the remaining response options.

                    We should dynamically generate the ids to be unique here, so that everything remains accessible.-->
                    <div class="px-3 starting-style-transition w-full min-h-61 @2xl:w-1/2 @4xl:w-1/2 @7xl:w-1/3">
                        <Widget
                            :title="__('Pie chart pagination demo')"
                            title-tag="h2"
                            class="h-full"
                            icon="fieldtype-radio"
                            icon-class="size-4 text-gray-500 hidden @xs/widget:block"
                        >
                            <template #actions>
                                <Pagination
                                    :resource-meta="wakeMeUpChartMeta"
                                    :show-totals="false"
                                    :show-page-links="false"
                                    :show-per-page-selector="false"
                                    :scroll-to-top="false"
                                    @page-selected="wakeMeUpChartPage = $event"
                                />
                            </template>
                            <figure v-if="wakeMeUpChartPage === 1" class="pie-chart-figure">
                                <div
                                    class="pie-chart"
                                    style="--1: 68; --2: 18; --3: 9; --4: 5;"
                                    role="img"
                                    :aria-labelledby="pieChart2LegendId"
                                >
                                    <div class="pie-chart__disc" aria-hidden="true" />
                                    <!-- aria-hidden because the labels are already in the figure caption -->
                                    <span class="pie-chart__label | pie-chart__label--1" aria-hidden="true">68%</span>
                                    <span class="pie-chart__label | pie-chart__label--2" aria-hidden="true">18%</span>
                                    <span class="pie-chart__label | pie-chart__label--3" aria-hidden="true">9%</span>
                                    <span class="pie-chart__label | pie-chart__label--4" aria-hidden="true">5%</span>
                                </div>
                                <figcaption :id="pieChart2LegendId" class="pie-chart-legend">
                                    <ul class="pie-chart-legend__list">
                                        <li class="pie-chart-legend__item">
                                            <span class="pie-chart-legend__value">68%</span>
                                            <div class="pie-chart-legend__swatch pie-chart-legend__swatch--1" />
                                            <span>Alarm, no mercy</span>
                                        </li>
                                        <li class="pie-chart-legend__item">
                                            <span class="pie-chart-legend__value">18%</span>
                                            <div class="pie-chart-legend__swatch pie-chart-legend__swatch--2" />
                                            <span>Hit snooze</span>
                                        </li>
                                        <li class="pie-chart-legend__item">
                                            <span class="pie-chart-legend__value">9%</span>
                                            <div class="pie-chart-legend__swatch pie-chart-legend__swatch--3" />
                                            <span>Woken by someone else</span>
                                        </li>
                                        <li class="pie-chart-legend__item">
                                            <button
                                                type="button"
                                                class="pie-chart-legend__link"
                                                @click="selectNextChartPage"
                                            >
                                                <span class="pie-chart-legend__value">5%</span>
                                                <div class="pie-chart-legend__swatch pie-chart-legend__swatch--4" />
                                                <span>Other</span>
                                            </button>
                                        </li>
                                    </ul>
                                </figcaption>
                            </figure>
                            <figure v-else-if="wakeMeUpChartPage === 2" class="pie-chart-figure">
                                <div
                                    class="pie-chart pie-chart--other-segment"
                                    style="--1: 68; --2: 18; --3: 9; --4: 5;"
                                    role="img"
                                    :aria-labelledby="pieChart2LegendPage2Id"
                                >
                                    <div class="pie-chart__disc" aria-hidden="true" />
                                    <span class="pie-chart__label | pie-chart__label--1" aria-hidden="true">68%</span>
                                    <span class="pie-chart__label | pie-chart__label--2" aria-hidden="true">18%</span>
                                    <span class="pie-chart__label | pie-chart__label--3" aria-hidden="true">9%</span>
                                    <span class="pie-chart__label | pie-chart__label--4" aria-hidden="true">5%</span>
                                </div>
                                <figcaption :id="pieChart2LegendPage2Id" class="pie-chart-legend">
                                    <ul class="pie-chart-legend__list">
                                        <li class="pie-chart-legend__item">
                                            <span class="pie-chart-legend__value">3%</span>
                                            <div class="pie-chart-legend__swatch pie-chart-legend__swatch--4" />
                                            <span>Bohemian Rhapsody</span>
                                        </li>
                                        <li class="pie-chart-legend__item">
                                            <span class="pie-chart-legend__value">1%</span>
                                            <div class="pie-chart-legend__swatch pie-chart-legend__swatch--4" />
                                            <span>Stairway to Heaven</span>
                                        </li>
                                        <li class="pie-chart-legend__item">
                                            <span class="pie-chart-legend__value">1%</span>
                                            <div class="pie-chart-legend__swatch pie-chart-legend__swatch--4" />
                                            <span>Wonderwall</span>
                                        </li>
                                    </ul>
                                </figcaption>
                            </figure>
                        </Widget>
                    </div>
                    <!-- Example of a Ranking field type (Horizontal Lollipop Chart). We should dynamically generate the ids to be unique here, so that everything remains accessible. -->
                    <div class="px-3 starting-style-transition w-full min-h-61 @2xl:w-1/2 @4xl:w-1/2 @7xl:w-1/3">
                        <Widget
                            :title="__('Rank your favourite seasons')"
                            title-tag="h2"
                            class="h-full"
                            icon="rank"
                            icon-class="size-4 text-gray-500 hidden @xs/widget:block"
                        >
                            <figure class="p-6 grid" :aria-labelledby="lollipopChart1CaptionId">
                                <ol class="m-0 list-none grid grid-cols-[auto_auto_max-content_1fr] items-center gap-x-2.25 gap-y-2.5 p-0 pt-4">
                                    <li class="contents">
                                        <span class="text-xs font-medium tabular-nums text-gray-700 dark:text-gray-400" aria-hidden="true">1</span>
                                        <span class="size-2.5 rounded-xs bg-chart-1" />
                                        <span class="truncate max-w-25 me-2 text-xs text-gray-900 dark:text-gray-100">Summer</span>
                                        <div class="flex items-center gap-1">
                                            <div class="h-px w-[55%] bg-gray-200 dark:bg-gray-600" />
                                            <div class="size-2 rounded-full bg-chart-1" />
                                            <span class="min-w-8.5 text-end text-[0.785rem] font-medium tabular-nums text-gray-700 dark:text-gray-400">55%</span>
                                        </div>
                                    </li>
                                    <li class="contents">
                                        <span class="text-xs font-medium tabular-nums text-gray-700 dark:text-gray-400" aria-hidden="true">2</span>
                                        <span class="size-2.5 rounded-xs bg-chart-2" />
                                        <span class="truncate max-w-25 me-2 text-xs text-gray-900 dark:text-gray-100">Autumn</span>
                                        <div class="flex items-center gap-1">
                                            <div class="h-px w-[25%] bg-gray-200 dark:bg-gray-600" />
                                            <div class="size-2 rounded-full bg-chart-2" />
                                            <span class="min-w-8.5 text-end text-[0.785rem] font-medium tabular-nums text-gray-700 dark:text-gray-400">25%</span>
                                        </div>
                                    </li>
                                    <li class="contents">
                                        <span class="text-xs font-medium tabular-nums text-gray-700 dark:text-gray-400" aria-hidden="true">3</span>
                                        <span class="size-2.5 rounded-xs bg-chart-3" />
                                        <span class="truncate max-w-25 me-2 text-xs text-gray-900 dark:text-gray-100">Spring</span>
                                        <div class="flex items-center gap-1">
                                            <div class="h-px w-[15%] bg-gray-200 dark:bg-gray-600" />
                                            <div class="size-2 rounded-full bg-chart-3" />
                                            <span class="min-w-8.5 text-end text-[0.785rem] font-medium tabular-nums text-gray-700 dark:text-gray-400">15%</span>
                                        </div>
                                    </li>
                                    <li class="contents">
                                        <span class="text-xs font-medium tabular-nums text-gray-700 dark:text-gray-400" aria-hidden="true">4</span>
                                        <span class="size-2.5 rounded-xs bg-chart-4-legend" />
                                        <span class="truncate max-w-25 me-2 text-xs text-gray-900 dark:text-gray-100">Winter</span>
                                        <div class="flex items-center gap-1">
                                            <div class="h-px w-[10%] bg-gray-200 dark:bg-gray-600" />
                                            <div class="size-2 rounded-full bg-chart-4-legend" />
                                            <span class="min-w-8.5 text-end text-[0.785rem] font-medium tabular-nums text-gray-700 dark:text-gray-400">10%</span>
                                        </div>
                                    </li>
                                </ol>
                                <figcaption :id="lollipopChart1CaptionId" class="sr-only">
                                    {{ __('Ranked favourite seasons: Summer 55%, Autumn 25%, Spring 15%, Winter 10%') }}
                                </figcaption>
                            </figure>
                        </Widget>
                    </div>
                    <!-- Example of a Dropdown field type (Horizontal Bar Chart Widget). We should dynamically generate the ids to be unique here, so that everything remains accessible. -->
                    <div class="px-3 starting-style-transition w-full min-h-61 @2xl:w-1/2 @4xl:w-1/2 @7xl:w-1/3">
                        <Widget
                            :title="__('Have you seen us live before?')"
                            title-tag="h2"
                            class="h-full"
                            icon="fieldtype-select"
                            icon-class="size-4 text-gray-500 hidden @xs/widget:block"
                        >
                            <figure class="p-6 grid" :aria-labelledby="horizontalBarChart1CaptionId">
                                <ol class="m-0 list-none grid grid-cols-[auto_auto_max-content_1fr] items-center gap-x-2.25 gap-y-2.5 p-0 pt-4">
                                    <li class="contents">
                                        <span class="text-xs font-medium tabular-nums text-gray-700 dark:text-gray-400" aria-hidden="true">55%</span>
                                        <span class="size-2.5 rounded-xs bg-chart-1" />
                                        <span class="truncate max-w-25 me-2 text-xs text-gray-900 dark:text-gray-100">Yep</span>
                                        <div class="h-2.5 rounded-full w-[55%] bg-chart-1" />
                                    </li>
                                    <li class="contents">
                                        <span class="text-xs font-medium tabular-nums text-gray-700 dark:text-gray-400" aria-hidden="true">35%</span>
                                        <span class="size-2.5 rounded-xs bg-chart-2" />
                                        <span class="truncate max-w-25 me-2 text-xs text-gray-900 dark:text-gray-100">Nope</span>
                                        <div class="h-2.5 rounded-full w-[35%] bg-chart-2" />
                                    </li>
                                    <li class="contents">
                                        <span class="text-xs font-medium tabular-nums text-gray-700 dark:text-gray-400" aria-hidden="true">10%</span>
                                        <span class="size-2.5 rounded-xs bg-chart-3" />
                                        <span class="truncate max-w-25 me-2 text-xs text-gray-900 dark:text-gray-100">Maybe</span>
                                        <div class="h-2.5 rounded-full w-[10%] bg-chart-3" />
                                    </li>
                                </ol>
                                <figcaption :id="horizontalBarChart1CaptionId" class="sr-only">
                                    {{ __('Have you seen us live before?: Yep 55%, Nope 35%, Maybe 10%') }}
                                </figcaption>
                            </figure>
                        </Widget>
                    </div>
                    <!-- Example of an Opinion Scale field type (Vertical Bar Chart Widget). We should dynamically generate the ids to be unique here, so that everything remains accessible. -->
                    <div class="px-3 starting-style-transition w-full min-h-61 @2xl:w-1/2 @4xl:w-1/2 @7xl:w-1/3">
                        <Widget
                            :title="__('How likely are you to recommend us?')"
                            title-tag="h2"
                            class="h-full"
                            icon="scale-up"
                            icon-class="size-4 text-gray-500 hidden @xs/widget:block"
                        >
                            <div class="mt-4 ms-3.5">
                                <div class="inline-flex items-center gap-2 py-1.25 px-2 border border-gray-200 dark:border-gray-700 rounded-md">
                                    <span class="text-md font-semibold st-text-trim-cap tabular-nums text-green-600 dark:text-green-400">8.1</span> <span class="text-xs text-gray-500 dark:text-gray-400">Average</span>
                                </div>
                            </div>
                            <figure class="vertical-bar-chart-figure" :aria-labelledby="verticalBarChart1CaptionId">
                                <ol
                                    class="vertical-bar-chart"
                                    :style="{ '--max-value': verticalBarChart1MaxValue }"
                                >
                                    <li
                                        v-for="bar in verticalBarChart1Data"
                                        :key="bar.label"
                                        class="vertical-bar-chart__bar"
                                        :style="{ '--value': bar.percent }"
                                    >
                                        <div class="vertical-bar-chart__plot" aria-hidden="true">
                                            <span class="vertical-bar-chart__value">{{ bar.percent }}%</span>
                                            <div class="vertical-bar-chart__fill" />
                                        </div>
                                        <span class="vertical-bar-chart__scale-label" aria-hidden="true">{{ bar.label }}</span>
                                    </li>
                                </ol>
                                <figcaption :id="verticalBarChart1CaptionId" class="sr-only">
                                    {{ __('Recommendation score distribution: 0: 2%, 1: 1%, 2: 3%, 3: 4%, 4: 6%, 5: 8%, 6: 12%, 7: 20%, 8: 35%, 9: 48%, 10: 61%') }}
                                </figcaption>
                            </figure>
                        </Widget>
                    </div>
                </div>
            </template>
        </FormSubmissionListing>

        <Modal :open="exportModalOpen" @update:open="exportModalOpen = $event" :title="__('Export Submissions')">
            <div class="space-y-4">
                <div>
                    <label class="text-sm font-medium mb-1.5 block">{{ __('Format') }}</label>
                    <RadioGroup v-model="exportFormat" inline>
                        <Radio v-for="format in exporters" :key="format.handle" :value="format.handle" :label="format.title" />
                    </RadioGroup>
                </div>

                <div>
                    <label class="text-sm font-medium mb-1.5 block">{{ __('Submissions') }}</label>
                    <RadioGroup v-model="exportScope">
                        <Radio value="all" :label="__('All Submissions')" />
                        <Radio value="filtered" :label="__('Filtered Submissions')" :description="__('statamic::messages.form_export_filtered_description')" :disabled="!hasFilteredScope" />
                    </RadioGroup>
                </div>
            </div>

            <template #footer>
                <div class="flex justify-end p-2">
                    <Button variant="primary" :text="__('Export')" @click="exportSubmissions" />
                </div>
            </template>
        </Modal>
    </div>
</template>
