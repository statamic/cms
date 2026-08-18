<script setup>
import { ref, computed, watch, useId } from 'vue';
import axios from 'axios';
import Head from '@/pages/layout/Head.vue';
import { Header, Dropdown, DropdownMenu, DropdownItem, Button, Modal, RadioGroup, Radio, CommandPaletteItem, ToggleGroup, ToggleItem, Widget, Pagination, Icon, Switch } from '@ui';
import FormStatusIndicator from '@/components/forms/FormStatusIndicator.vue';
import ResourceDeleter from '@/components/ResourceDeleter.vue';
import FormSubmissionListing from '@/components/forms/SubmissionListing.vue';
import Layout from '@/pages/layout/Layout.vue';
import PanelLayout from '@/pages/layout/PanelLayout.vue';
import FormsLayout from './Layout.vue';
import useSummaryChartType from '@/composables/use-summary-chart-type.js';
import useSummaryChartMetric from '@/composables/use-summary-chart-metric.js';
import FieldNumberingToggle from '@/components/forms/FieldNumberingToggle.vue';

defineOptions({ layout: [Layout, PanelLayout, FormsLayout] });

const props = defineProps([
    'form',
    'can',
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
const imageChoiceBarChart1CaptionId = useId();
const imageChoicePieChart1LegendId = useId();
const checkboxesBarChart1CaptionId = useId();
const yesNoBarChart1CaptionId = useId();
const yesNoPieChart1LegendId = useId();
const dictionaryChart1CaptionId = useId();
const dictionaryChart1Page2CaptionId = useId();
const numberBarChart1CaptionId = useId();
const starRatingChart1CaptionId = useId();
const starRatingChart2CaptionId = useId();
const toggleBarChart1CaptionId = useId();

const dictionaryChartPage = ref(1);
const dictionaryChartPerPage = 5;
const dictionaryChartTotal = 8;

const dictionaryChartMeta = computed(() => {
    const from = (dictionaryChartPage.value - 1) * dictionaryChartPerPage + 1;
    const to = Math.min(dictionaryChartPage.value * dictionaryChartPerPage, dictionaryChartTotal);

    return {
        current_page: dictionaryChartPage.value,
        last_page: Math.ceil(dictionaryChartTotal / dictionaryChartPerPage),
        per_page: dictionaryChartPerPage,
        total: dictionaryChartTotal,
        from,
        to,
    };
});

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

const pieChart2AccessibleLabel = computed(() => {
    if (wakeMeUpChartPage.value === 1) {
        return __('Alarm, no mercy 68%, Hit snooze 18%, Woken by someone else 9%, Other 5%');
    }

    return __('Other breakdown: Bohemian Rhapsody 3%, Stairway to Heaven 1%, Wonderwall 1%');
});

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

const {
    chartType: yesNoChart1Type,
    setChartType: setYesNoChart1Type,
} = useSummaryChartType(props.form.handle, 'yes-no-1');

const {
    chartType: imageChoiceChart1Type,
    setChartType: setImageChoiceChart1Type,
} = useSummaryChartType(props.form.handle, 'image-choice-1');

const { metric: summaryChartMetric } = useSummaryChartMetric(props.form.handle);
</script>

<template>
    <Head :title="[__('Results'), __(form.title), __('Forms')]" />

    <div class="max-w-5xl 3xl:max-w-6xl mx-auto" data-max-width-wrapper>
        <Header>
            <template #title>
                <FormStatusIndicator :status="form.status" />
                {{ __(form.title) }}
            </template>

            <Dropdown v-if="can.edit || can.delete" placement="left-start" class="me-2">
                <DropdownMenu>
                    <DropdownItem v-if="can.edit" :text="__('Configure Form')" icon="cog" :href="form.editUrl" />
                    <DropdownItem
                        v-if="can.delete"
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
                v-if="can.delete"
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
                <div data-submission-summary :data-chart-metric="summaryChartMetric" class="mt-3 pb-8">
                    <div class="flex w-full items-center gap-2 mb-3">
                        <FieldNumberingToggle />
                        <ToggleGroup v-model="summaryChartMetric" size="xs">
                            <ToggleItem
                                value="percent"
                                label="%"
                                :aria-label="__('Percentage')"
                                v-tooltip="__('Percentage')"
                            />
                            <ToggleItem
                                value="count"
                                icon="layout-list-small"
                                :aria-label="__('Response count')"
                                v-tooltip="__('Response count')"
                            />
                        </ToggleGroup>
                        <p class="ms-1 text-sm text-gray-500 dark:text-gray-500">
                            {{ __(':count responses', { count: $number.format(248) }) }}
                        </p>
                    </div>
                    <div class="@container/widgets widgets flex flex-wrap gap-y-6 -mx-2 sm:-mx-3">
                    <div class="starting-style-transition w-full min-h-61 min-[1100px]:w-1/2 px-3">
                        <Widget
                            :title="__('Wake me up')"
                            title-tag="h2"
                            class="h-full"
                            icon="fieldtype-radio"
                            icon-class="hidden @xs/widget:block size-4 text-gray-500"
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
                                    <span class="chart-metric | pie-chart__label | pie-chart__label--1" aria-hidden="true" data-percent="45%" data-count="112"></span>
                                    <span class="chart-metric | pie-chart__label | pie-chart__label--2" aria-hidden="true" data-percent="30%" data-count="74"></span>
                                    <span class="chart-metric | pie-chart__label | pie-chart__label--3" aria-hidden="true" data-percent="15%" data-count="37"></span>
                                    <span class="chart-metric | pie-chart__label | pie-chart__label--4" aria-hidden="true" data-percent="10%" data-count="25"></span>
                                </div>
                                <!-- Pie Chart 1 Legend -->
                                <figcaption :id="pieChart1LegendId" class="pie-chart-legend">
                                    <ol class="pie-chart-legend__list">
                                        <li class="pie-chart-legend__item">
                                            <span class="chart-metric | pie-chart-legend__value" data-percent="45%" data-count="112"></span>
                                            <div class="pie-chart-legend__swatch pie-chart-legend__swatch--1" />
                                            <span>Before you Go Go</span>
                                        </li>
                                        <li class="pie-chart-legend__item">
                                            <span class="chart-metric | pie-chart-legend__value" data-percent="30%" data-count="74"></span>
                                            <div class="pie-chart-legend__swatch pie-chart-legend__swatch--2" />
                                            <span>Bring me Back to Life</span>
                                        </li>
                                        <li class="pie-chart-legend__item">
                                            <span class="chart-metric | pie-chart-legend__value" data-percent="15%" data-count="37"></span>
                                            <div class="pie-chart-legend__swatch pie-chart-legend__swatch--3" />
                                            <span>When September Ends</span>
                                        </li>
                                        <li class="pie-chart-legend__item">
                                            <span class="chart-metric | pie-chart-legend__value" data-percent="10%" data-count="25"></span>
                                            <div class="pie-chart-legend__swatch pie-chart-legend__swatch--4" />
                                            <span>Never</span>
                                        </li>
                                    </ol>
                                </figcaption>
                            </figure>
                        </Widget>
                    </div>
                    <!-- Example of a Multiple Choice field type (Pie Chart) with Pagination. The idea here is when there are _more_ than four response options, we can paginate the chart. In such a case, the fourth option shows as "Other", and the second widget shows the "other" segment broken down into the remaining response options.

                    We should dynamically generate the ids to be unique here, so that everything remains accessible.-->
                    <div class="starting-style-transition w-full min-h-61 min-[1100px]:w-1/2 px-3">
                        <Widget
                            :title="__('Pie chart pagination demo')"
                            title-tag="h2"
                            class="h-full"
                            icon="fieldtype-radio"
                            icon-class="hidden @xs/widget:block size-4 text-gray-500"
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
                            <p class="sr-only" aria-live="polite">{{ pieChart2AccessibleLabel }}</p>
                            <figure v-if="wakeMeUpChartPage === 1" class="pie-chart-figure">
                                <div
                                    class="pie-chart"
                                    style="--1: 68; --2: 18; --3: 9; --4: 5;"
                                    role="img"
                                    :aria-labelledby="pieChart2LegendId"
                                >
                                    <div class="pie-chart__disc" aria-hidden="true" />
                                    <!-- aria-hidden because the labels are already in the figure caption -->
                                    <span class="chart-metric | pie-chart__label | pie-chart__label--1" aria-hidden="true" data-percent="68%" data-count="169"></span>
                                    <span class="chart-metric | pie-chart__label | pie-chart__label--2" aria-hidden="true" data-percent="18%" data-count="45"></span>
                                    <span class="chart-metric | pie-chart__label | pie-chart__label--3" aria-hidden="true" data-percent="9%" data-count="22"></span>
                                    <span class="chart-metric | pie-chart__label | pie-chart__label--4" aria-hidden="true" data-percent="5%" data-count="12"></span>
                                </div>
                                <figcaption :id="pieChart2LegendId" class="pie-chart-legend">
                                    <ul class="pie-chart-legend__list">
                                        <li class="pie-chart-legend__item">
                                            <span class="chart-metric | pie-chart-legend__value" data-percent="68%" data-count="169"></span>
                                            <div class="pie-chart-legend__swatch pie-chart-legend__swatch--1" />
                                            <span>Alarm, no mercy</span>
                                        </li>
                                        <li class="pie-chart-legend__item">
                                            <span class="chart-metric | pie-chart-legend__value" data-percent="18%" data-count="45"></span>
                                            <div class="pie-chart-legend__swatch pie-chart-legend__swatch--2" />
                                            <span>Hit snooze</span>
                                        </li>
                                        <li class="pie-chart-legend__item">
                                            <span class="chart-metric | pie-chart-legend__value" data-percent="9%" data-count="22"></span>
                                            <div class="pie-chart-legend__swatch pie-chart-legend__swatch--3" />
                                            <span>Woken by someone else</span>
                                        </li>
                                        <li class="pie-chart-legend__item">
                                            <button
                                                type="button"
                                                class="pie-chart-legend__link"
                                                @click="selectNextChartPage"
                                            >
                                                <span class="chart-metric | pie-chart-legend__value" data-percent="5%" data-count="12"></span>
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
                                    <span class="chart-metric | pie-chart__label | pie-chart__label--1" aria-hidden="true" data-percent="68%" data-count="169"></span>
                                    <span class="chart-metric | pie-chart__label | pie-chart__label--2" aria-hidden="true" data-percent="18%" data-count="45"></span>
                                    <span class="chart-metric | pie-chart__label | pie-chart__label--3" aria-hidden="true" data-percent="9%" data-count="22"></span>
                                    <span class="chart-metric | pie-chart__label | pie-chart__label--4" aria-hidden="true" data-percent="5%" data-count="12"></span>
                                </div>
                                <figcaption :id="pieChart2LegendPage2Id" class="pie-chart-legend">
                                    <ul class="pie-chart-legend__list">
                                        <li class="pie-chart-legend__item">
                                            <span class="chart-metric | pie-chart-legend__value" data-percent="3%" data-count="7"></span>
                                            <div class="pie-chart-legend__swatch pie-chart-legend__swatch--4" />
                                            <span>Bohemian Rhapsody</span>
                                        </li>
                                        <li class="pie-chart-legend__item">
                                            <span class="chart-metric | pie-chart-legend__value" data-percent="1%" data-count="2"></span>
                                            <div class="pie-chart-legend__swatch pie-chart-legend__swatch--4" />
                                            <span>Stairway to Heaven</span>
                                        </li>
                                        <li class="pie-chart-legend__item">
                                            <span class="chart-metric | pie-chart-legend__value" data-percent="1%" data-count="2"></span>
                                            <div class="pie-chart-legend__swatch pie-chart-legend__swatch--4" />
                                            <span>Wonderwall</span>
                                        </li>
                                    </ul>
                                </figcaption>
                            </figure>
                        </Widget>
                    </div>
                    <!-- Example of a Ranking field type (Horizontal Lollipop Chart). We should dynamically generate the ids to be unique here, so that everything remains accessible. -->
                    <div class="starting-style-transition w-full min-h-61 min-[1100px]:w-1/2 px-3">
                        <Widget
                            :title="__('Rank your favourite seasons')"
                            title-tag="h2"
                            class="h-full"
                            icon="rank"
                            icon-class="hidden @xs/widget:block size-4 text-gray-500"
                        >
                            <figure class="grid p-6" :aria-labelledby="lollipopChart1CaptionId">
                                <ol class="grid grid-cols-[auto_auto_max-content_1fr] items-center list-none m-0 gap-x-2.25 gap-y-2.5 p-0 [&:not(:has(>_:nth-child(5)))]:pt-4" aria-hidden="true">
                                    <li class="contents">
                                        <span class="text-xs text-end font-medium tabular-nums text-gray-700 dark:text-gray-50">1</span>
                                        <span class="size-2.5 rounded-xs bg-chart-1" />
                                        <span class="max-w-25 truncate me-2 text-xs text-gray-900 dark:text-gray-50">Summer</span>
                                        <div class="flex items-center gap-1">
                                            <div class="h-px w-[55%] bg-gray-200 dark:bg-gray-600" />
                                            <div class="size-2 rounded-full bg-chart-1" />
                                            <span class="chart-metric | min-w-8.5 ms-1 text-[0.75rem] font-medium tabular-nums text-gray-700 dark:text-gray-50" data-percent="55%" data-count="136"></span>
                                        </div>
                                    </li>
                                    <li class="contents">
                                        <span class="text-xs text-end font-medium tabular-nums text-gray-700 dark:text-gray-50">2</span>
                                        <span class="size-2.5 rounded-xs bg-chart-2" />
                                        <span class="max-w-25 truncate me-2 text-xs text-gray-900 dark:text-gray-50">Autumn</span>
                                        <div class="flex items-center gap-1">
                                            <div class="h-px w-[25%] bg-gray-200 dark:bg-gray-600" />
                                            <div class="size-2 rounded-full bg-chart-2" />
                                            <span class="chart-metric | min-w-8.5 ms-1 text-[0.75rem] font-medium tabular-nums text-gray-700 dark:text-gray-50" data-percent="25%" data-count="62"></span>
                                        </div>
                                    </li>
                                    <li class="contents">
                                        <span class="text-xs text-end font-medium tabular-nums text-gray-700 dark:text-gray-50">3</span>
                                        <span class="size-2.5 rounded-xs bg-chart-3" />
                                        <span class="max-w-25 truncate me-2 text-xs text-gray-900 dark:text-gray-50">Spring</span>
                                        <div class="flex items-center gap-1">
                                            <div class="h-px w-[15%] bg-gray-200 dark:bg-gray-600" />
                                            <div class="size-2 rounded-full bg-chart-3" />
                                            <span class="chart-metric | min-w-8.5 ms-1 text-[0.75rem] font-medium tabular-nums text-gray-700 dark:text-gray-50" data-percent="15%" data-count="37"></span>
                                        </div>
                                    </li>
                                    <li class="contents">
                                        <span class="text-xs text-end font-medium tabular-nums text-gray-700 dark:text-gray-50">4</span>
                                        <span class="size-2.5 rounded-xs bg-chart-4-legend" />
                                        <span class="max-w-25 truncate me-2 text-xs text-gray-900 dark:text-gray-50">Winter</span>
                                        <div class="flex items-center gap-1">
                                            <div class="h-px w-[10%] bg-gray-200 dark:bg-gray-600" />
                                            <div class="size-2 rounded-full bg-chart-4-legend" />
                                            <span class="chart-metric | min-w-8.5 ms-1 text-[0.75rem] font-medium tabular-nums text-gray-700 dark:text-gray-50" data-percent="10%" data-count="25"></span>
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
                    <div class="starting-style-transition w-full min-h-61 min-[1100px]:w-1/2 px-3">
                        <Widget
                            :title="__('Have you seen us live before?')"
                            title-tag="h2"
                            class="h-full"
                            icon="fieldtype-select"
                            icon-class="hidden @xs/widget:block size-4 text-gray-500"
                        >
                            <figure class="grid p-6" :aria-labelledby="horizontalBarChart1CaptionId">
                                <ol class="grid grid-cols-[auto_auto_max-content_1fr] items-center list-none m-0 gap-x-2.25 gap-y-2.5 p-0 [&:not(:has(>_:nth-child(5)))]:pt-4" aria-hidden="true">
                                    <li class="contents">
                                        <span class="chart-metric | text-xs text-end font-medium tabular-nums text-gray-700 dark:text-gray-50" data-percent="55%" data-count="136"></span>
                                        <span class="size-2.5 rounded-xs bg-chart-1" />
                                        <span class="max-w-25 truncate me-2 text-xs text-gray-900 dark:text-gray-50">Yep</span>
                                        <div class="h-2.5 w-[55%] rounded-full bg-chart-1" />
                                    </li>
                                    <li class="contents">
                                        <span class="chart-metric | text-xs text-end font-medium tabular-nums text-gray-700 dark:text-gray-50" data-percent="35%" data-count="87"></span>
                                        <span class="size-2.5 rounded-xs bg-chart-2" />
                                        <span class="max-w-25 truncate me-2 text-xs text-gray-900 dark:text-gray-50">Nope</span>
                                        <div class="h-2.5 w-[35%] rounded-full bg-chart-2" />
                                    </li>
                                    <li class="contents">
                                        <span class="chart-metric | text-xs text-end font-medium tabular-nums text-gray-700 dark:text-gray-50" data-percent="10%" data-count="25"></span>
                                        <span class="size-2.5 rounded-xs bg-chart-3" />
                                        <span class="max-w-25 truncate me-2 text-xs text-gray-900 dark:text-gray-50">Maybe</span>
                                        <div class="h-2.5 w-[10%] rounded-full bg-chart-3" />
                                    </li>
                                </ol>
                                <figcaption :id="horizontalBarChart1CaptionId" class="sr-only">
                                    {{ __('Have you seen us live before?: Yep 55%, Nope 35%, Maybe 10%') }}
                                </figcaption>
                            </figure>
                        </Widget>
                    </div>
                    <!-- Example of an Opinion Scale field type (Vertical Bar Chart Widget). We should dynamically generate the ids to be unique here, so that everything remains accessible. -->
                    <div class="starting-style-transition w-full min-h-61 min-[1100px]:w-1/2 px-3">
                        <Widget
                            :title="__('How likely are you to recommend us?')"
                            title-tag="h2"
                            class="h-full"
                            icon="scale-up"
                            icon-class="hidden @xs/widget:block size-4 text-gray-500"
                        >
                            <figure class="vertical-bar-chart-figure" :aria-labelledby="verticalBarChart1CaptionId">
                                <div aria-hidden="true">
                                    <div class="inline-flex items-center gap-2 px-2 py-1.25 rounded-md border border-gray-200 dark:border-gray-700">
                                        <span class="text-md font-semibold st-text-trim-cap tabular-nums text-green-600 dark:text-green-400">8.1</span> <span class="text-[0.75rem] text-gray-500 dark:text-gray-300">Average</span>
                                    </div>
                                </div>
                                <ol class="vertical-bar-chart" style="--max-value: 61;" aria-hidden="true">
                                    <li class="vertical-bar-chart__bar" style="--value: 2;">
                                        <div class="vertical-bar-chart__plot">
                                            <span class="chart-metric | vertical-bar-chart__value" data-percent="2%" data-count="5"></span>
                                            <div class="vertical-bar-chart__fill" />
                                        </div>
                                        <span class="vertical-bar-chart__scale-label">0</span>
                                    </li>
                                    <li class="vertical-bar-chart__bar" style="--value: 1;">
                                        <div class="vertical-bar-chart__plot">
                                            <span class="chart-metric | vertical-bar-chart__value" data-percent="1%" data-count="2"></span>
                                            <div class="vertical-bar-chart__fill" />
                                        </div>
                                        <span class="vertical-bar-chart__scale-label">1</span>
                                    </li>
                                    <li class="vertical-bar-chart__bar" style="--value: 3;">
                                        <div class="vertical-bar-chart__plot">
                                            <span class="chart-metric | vertical-bar-chart__value" data-percent="3%" data-count="7"></span>
                                            <div class="vertical-bar-chart__fill" />
                                        </div>
                                        <span class="vertical-bar-chart__scale-label">2</span>
                                    </li>
                                    <li class="vertical-bar-chart__bar" style="--value: 4;">
                                        <div class="vertical-bar-chart__plot">
                                            <span class="chart-metric | vertical-bar-chart__value" data-percent="4%" data-count="10"></span>
                                            <div class="vertical-bar-chart__fill" />
                                        </div>
                                        <span class="vertical-bar-chart__scale-label">3</span>
                                    </li>
                                    <li class="vertical-bar-chart__bar" style="--value: 6;">
                                        <div class="vertical-bar-chart__plot">
                                            <span class="chart-metric | vertical-bar-chart__value" data-percent="6%" data-count="15"></span>
                                            <div class="vertical-bar-chart__fill" />
                                        </div>
                                        <span class="vertical-bar-chart__scale-label">4</span>
                                    </li>
                                    <li class="vertical-bar-chart__bar" style="--value: 8;">
                                        <div class="vertical-bar-chart__plot">
                                            <span class="chart-metric | vertical-bar-chart__value" data-percent="8%" data-count="20"></span>
                                            <div class="vertical-bar-chart__fill" />
                                        </div>
                                        <span class="vertical-bar-chart__scale-label">5</span>
                                    </li>
                                    <li class="vertical-bar-chart__bar" style="--value: 12;">
                                        <div class="vertical-bar-chart__plot">
                                            <span class="chart-metric | vertical-bar-chart__value" data-percent="12%" data-count="30"></span>
                                            <div class="vertical-bar-chart__fill" />
                                        </div>
                                        <span class="vertical-bar-chart__scale-label">6</span>
                                    </li>
                                    <li class="vertical-bar-chart__bar" style="--value: 20;">
                                        <div class="vertical-bar-chart__plot">
                                            <span class="chart-metric | vertical-bar-chart__value" data-percent="20%" data-count="50"></span>
                                            <div class="vertical-bar-chart__fill" />
                                        </div>
                                        <span class="vertical-bar-chart__scale-label">7</span>
                                    </li>
                                    <li class="vertical-bar-chart__bar" style="--value: 35;">
                                        <div class="vertical-bar-chart__plot">
                                            <span class="chart-metric | vertical-bar-chart__value" data-percent="35%" data-count="87"></span>
                                            <div class="vertical-bar-chart__fill" />
                                        </div>
                                        <span class="vertical-bar-chart__scale-label">8</span>
                                    </li>
                                    <li class="vertical-bar-chart__bar" style="--value: 48;">
                                        <div class="vertical-bar-chart__plot">
                                            <span class="chart-metric | vertical-bar-chart__value" data-percent="48%" data-count="119"></span>
                                            <div class="vertical-bar-chart__fill" />
                                        </div>
                                        <span class="vertical-bar-chart__scale-label">9</span>
                                    </li>
                                    <li class="vertical-bar-chart__bar" style="--value: 61;">
                                        <div class="vertical-bar-chart__plot">
                                            <span class="chart-metric | vertical-bar-chart__value" data-percent="61%" data-count="151"></span>
                                            <div class="vertical-bar-chart__fill" />
                                        </div>
                                        <span class="vertical-bar-chart__scale-label">10</span>
                                    </li>
                                </ol>
                                <figcaption :id="verticalBarChart1CaptionId" class="sr-only">
                                    {{ __('Average 8.1. Recommendation score distribution: 0: 2%, 1: 1%, 2: 3%, 3: 4%, 4: 6%, 5: 8%, 6: 12%, 7: 20%, 8: 35%, 9: 48%, 10: 61%') }}
                                </figcaption>
                            </figure>
                        </Widget>
                    </div>
                    <!-- Example of an Image Choice field type with a chart type chooser. We should dynamically generate the ids to be unique here, so that everything remains accessible. -->
                    <div class="starting-style-transition w-full min-h-61 min-[1100px]:w-1/2 px-3">
                        <Widget
                            :title="__('What is your spirit animal?')"
                            title-tag="h2"
                            class="h-full"
                            icon="image-select"
                            icon-class="hidden @xs/widget:block size-4 text-gray-500"
                        >
                            <template #actions>
                                <ToggleGroup :model-value="imageChoiceChart1Type" @update:model-value="setImageChoiceChart1Type" size="sm">
                                    <ToggleItem
                                        value="bar"
                                        icon="charts-bar-horizontal"
                                        :aria-label="__('Bar chart')"
                                        v-tooltip="__('Bar chart')"
                                    />
                                    <ToggleItem
                                        value="pie"
                                        icon="money-graph-pie-chart"
                                        :aria-label="__('Pie chart')"
                                        v-tooltip="__('Pie chart')"
                                    />
                                </ToggleGroup>
                            </template>
                            <figure
                                v-if="imageChoiceChart1Type === 'bar'"
                                class="grid p-6"
                                :aria-labelledby="imageChoiceBarChart1CaptionId"
                            >
                                <ol class="grid grid-cols-[auto_2.5rem_auto_max-content_1fr] items-center list-none m-0 gap-x-2.25 gap-y-2.5 p-0 pt-4" aria-hidden="true">
                                    <li class="contents">
                                        <span class="chart-metric | text-xs text-end font-medium tabular-nums text-gray-700 dark:text-gray-50" data-percent="55%" data-count="136"></span>
                                        <img class="size-10 shrink-0 object-cover rounded-full" src="https://picsum.photos/id/159/320/320" alt="" />
                                        <span class="flex size-6 shrink-0 items-center justify-center rounded-md border border-gray-300 bg-white text-xs font-bold text-gray-800 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200">A</span>
                                        <span class="max-w-25 truncate me-2 text-xs text-gray-900 dark:text-gray-50">Actually</span>
                                        <div class="summary-bar-chart__fill h-2.5 w-[55%] rounded-full bg-chart-1" />
                                    </li>
                                    <li class="contents">
                                        <span class="chart-metric | text-xs text-end font-medium tabular-nums text-gray-700 dark:text-gray-50" data-percent="45%" data-count="112"></span>
                                        <img class="size-10 shrink-0 object-cover rounded-full" src="https://picsum.photos/id/485/320/320" alt="" />
                                        <span class="flex size-6 shrink-0 items-center justify-center rounded-md border border-gray-300 bg-white text-xs font-bold text-gray-800 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200">B</span>
                                        <span class="max-w-25 truncate me-2 text-xs text-gray-900 dark:text-gray-50">Nope</span>
                                        <div class="summary-bar-chart__fill h-2.5 w-[45%] rounded-full bg-chart-2" />
                                    </li>
                                </ol>
                                <figcaption :id="imageChoiceBarChart1CaptionId" class="sr-only">
                                    {{ __('What is your spirit animal?: Actually 55%, Nope 45%') }}
                                </figcaption>
                            </figure>
                            <figure v-else class="image-pie-chart-figure">
                                <div
                                    class="image-pie-chart"
                                    style="--1: 55; --2: 45;"
                                    role="img"
                                    :aria-labelledby="imageChoicePieChart1LegendId"
                                >
                                    <div class="image-pie-chart__disc" aria-hidden="true">
                                        <div class="image-pie-chart__slice image-pie-chart__slice--1" style="--image: url('https://picsum.photos/id/159/320/320')" />
                                        <div class="image-pie-chart__slice image-pie-chart__slice--2" style="--image: url('https://picsum.photos/id/485/320/320')" />
                                    </div>
                                    <!-- aria-hidden because the labels are already in the figure caption -->
                                    <span class="chart-metric | image-pie-chart__label image-pie-chart__label--1" aria-hidden="true" data-percent="55%" data-count="136"></span>
                                    <span class="chart-metric | image-pie-chart__label image-pie-chart__label--2" aria-hidden="true" data-percent="45%" data-count="112"></span>
                                </div>
                                <figcaption :id="imageChoicePieChart1LegendId" class="image-pie-chart-legend">
                                    <p class="sr-only">Actually (A) 55%, Nope (B) 45%</p>
                                    <ol class="grid grid-cols-[auto_2.5rem_auto_1fr] items-center justify-items-start list-none m-0 gap-x-2.25 gap-y-2.5 p-0 pt-3" aria-hidden="true">
                                        <span class="chart-metric | text-xs text-end font-medium tabular-nums text-gray-700 dark:text-gray-50" data-percent="55%" data-count="136"></span>
                                        <img class="size-10 shrink-0 object-cover rounded-full" src="https://picsum.photos/id/159/320/320" alt="" />
                                        <span class="flex size-6 shrink-0 items-center justify-center rounded-md border border-gray-300 bg-white text-xs font-bold text-gray-800 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200">A</span>
                                        <span class="max-w-25 truncate me-2 text-xs text-gray-900 dark:text-gray-50">Actually</span>
                                        <span class="chart-metric | text-xs text-end font-medium tabular-nums text-gray-700 dark:text-gray-50" data-percent="45%" data-count="112"></span>
                                        <img class="size-10 shrink-0 object-cover rounded-full" src="https://picsum.photos/id/485/320/320" alt="" />
                                        <span class="flex size-6 shrink-0 items-center justify-center rounded-md border border-gray-300 bg-white text-xs font-bold text-gray-800 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200">B</span>
                                        <span class="max-w-25 truncate me-2 text-xs text-gray-900 dark:text-gray-50">Nope</span>
                                    </ol>
                                </figcaption>
                            </figure>
                        </Widget>
                    </div>
                    <!-- Example of a Checkboxes field type (Horizontal Bar Chart with checkbox icons). We should dynamically generate the ids to be unique here, so that everything remains accessible. -->
                    <div class="starting-style-transition w-full min-h-61 min-[1100px]:w-1/2 px-3">
                        <Widget
                            :title="__('Who have you seen live?')"
                            title-tag="h2"
                            class="h-full"
                            icon="fieldtype-checkboxes"
                            icon-class="hidden @xs/widget:block size-4 text-gray-500"
                        >
                            <figure class="grid p-6" :aria-labelledby="checkboxesBarChart1CaptionId">
                                <ol class="grid grid-cols-[auto_auto_max-content_1fr] items-center list-none m-0 gap-x-2.25 gap-y-2.5 p-0 [&:not(:has(>_:nth-child(5)))]:pt-4" aria-hidden="true">
                                    <li class="contents">
                                        <span class="chart-metric | text-xs text-end font-medium tabular-nums text-gray-700 dark:text-gray-50" data-percent="55%" data-count="136"></span>
                                        <Icon name="checkbox-filled" class="summary-bar-chart__icon-stroke summary-bar-chart__icon-stroke--1 size-3.5 shrink-0" />
                                        <span class="max-w-25 truncate me-2 text-xs text-gray-900 dark:text-gray-50">Radiohead</span>
                                        <div class="h-2.5 w-[55%] rounded-full bg-chart-1" />
                                    </li>
                                    <li class="contents">
                                        <span class="chart-metric | text-xs text-end font-medium tabular-nums text-gray-700 dark:text-gray-50" data-percent="35%" data-count="87"></span>
                                        <Icon name="checkbox-filled" class="summary-bar-chart__icon-stroke summary-bar-chart__icon-stroke--2 size-3.5 shrink-0" />
                                        <span class="max-w-25 truncate me-2 text-xs text-gray-900 dark:text-gray-50">Death Cab for Cutie</span>
                                        <div class="h-2.5 w-[35%] rounded-full bg-chart-2" />
                                    </li>
                                    <li class="contents">
                                        <span class="chart-metric | text-xs text-end font-medium tabular-nums text-gray-700 dark:text-gray-50" data-percent="5%" data-count="12"></span>
                                        <Icon name="checkbox-filled" class="summary-bar-chart__icon-stroke summary-bar-chart__icon-stroke--3 size-3.5 shrink-0" />
                                        <span class="max-w-25 truncate me-2 text-xs text-gray-900 dark:text-gray-50">Olivia Rodrigo</span>
                                        <div class="h-2.5 w-[5%] rounded-full bg-chart-3" />
                                    </li>
                                    <li class="contents">
                                        <span class="chart-metric | text-xs text-end font-medium tabular-nums text-gray-700 dark:text-gray-50" data-percent="5%" data-count="12"></span>
                                        <Icon name="checkbox-filled" class="summary-bar-chart__icon-stroke summary-bar-chart__icon-stroke--4 size-3.5 shrink-0" />
                                        <span class="max-w-25 truncate me-2 text-xs text-gray-900 dark:text-gray-50">The Cure</span>
                                        <div class="box-content h-2.5 w-[5%] rounded-full bg-chart-4-legend" />
                                    </li>
                                </ol>
                                <figcaption :id="checkboxesBarChart1CaptionId" class="sr-only">
                                    {{ __('Who have you seen live?: Radiohead 55%, Death Cab for Cutie 35%, Olivia Rodrigo 5%, The Cure 5%') }}
                                </figcaption>
                            </figure>
                        </Widget>
                    </div>
                    <!-- Example of a yes/no field type with a chart type chooser. We should dynamically generate the ids to be unique here, so that everything remains accessible. -->
                    <div class="starting-style-transition w-full min-h-61 min-[1100px]:w-1/2 px-3">
                        <Widget
                            :title="__('Do you fancy a pint?')"
                            title-tag="h2"
                            class="h-full"
                            icon="checkmark-circle"
                            icon-class="hidden @xs/widget:block size-4 text-gray-500"
                        >
                            <template #actions>
                                <ToggleGroup :model-value="yesNoChart1Type" @update:model-value="setYesNoChart1Type" size="sm">
                                    <ToggleItem
                                        value="bar"
                                        icon="charts-bar-horizontal"
                                        :aria-label="__('Bar chart')"
                                        v-tooltip="__('Bar chart')"
                                    />
                                    <ToggleItem
                                        value="pie"
                                        icon="money-graph-pie-chart"
                                        :aria-label="__('Pie chart')"
                                        v-tooltip="__('Pie chart')"
                                    />
                                </ToggleGroup>
                            </template>
                            <figure
                                v-if="yesNoChart1Type === 'bar'"
                                class="grid p-6"
                                :aria-labelledby="yesNoBarChart1CaptionId"
                            >
                                <ol class="grid grid-cols-[auto_auto_max-content_1fr] items-center list-none m-0 gap-x-2.25 gap-y-2.75 p-0 pt-4" aria-hidden="true">
                                    <li class="contents">
                                        <span class="chart-metric | text-xs text-end font-medium tabular-nums text-gray-700 dark:text-gray-50" data-percent="55%" data-count="136"></span>
                                        <Icon name="checkmark-circle-filled" class="summary-bar-chart__icon-stroke summary-bar-chart__icon-stroke--1 size-3.5 shrink-0" />
                                        <span class="max-w-25 truncate me-2 text-xs text-gray-900 dark:text-gray-50">I’ll get my coat</span>
                                        <div class="summary-bar-chart__fill h-2.5 w-[55%] rounded-full bg-chart-1" />
                                    </li>
                                    <li class="contents">
                                        <span class="chart-metric | text-xs text-end font-medium tabular-nums text-gray-700 dark:text-gray-50" data-percent="45%" data-count="112"></span>
                                        <Icon name="delete-circle-filled" class="summary-bar-chart__icon-stroke summary-bar-chart__icon-stroke--2 size-3.5 shrink-0" />
                                        <span class="max-w-25 truncate me-2 text-xs text-gray-900 dark:text-gray-50">Coffee might be better</span>
                                        <div class="summary-bar-chart__fill h-2.5 w-[45%] rounded-full bg-chart-2" />
                                    </li>
                                </ol>
                                <figcaption :id="yesNoBarChart1CaptionId" class="sr-only">
                                    {{ __('Do you fancy a pint?: I’ll get my coat 55%, Coffee might be better 45%') }}
                                </figcaption>
                            </figure>
                            <figure v-else class="pie-chart-figure">
                                <div
                                    class="pie-chart"
                                    style="--1: 55; --2: 45; --3: 0; --4: 0;"
                                    role="img"
                                    :aria-labelledby="yesNoPieChart1LegendId"
                                >
                                    <div class="pie-chart__disc" aria-hidden="true" />
                                    <span class="chart-metric | pie-chart__label | pie-chart__label--1" aria-hidden="true" data-percent="55%" data-count="136"></span>
                                    <span class="chart-metric | pie-chart__label | pie-chart__label--2" aria-hidden="true" data-percent="45%" data-count="112"></span>
                                </div>
                                <figcaption :id="yesNoPieChart1LegendId" class="pie-chart-legend">
                                    <p class="sr-only">{{ __('Do you fancy a pint?: I’ll get my coat 55%, Coffee might be better 45%') }}</p>
                                    <ul class="pie-chart-legend__list" aria-hidden="true">
                                        <li class="pie-chart-legend__item">
                                            <span class="chart-metric | pie-chart-legend__value" data-percent="55%" data-count="136"></span>
                                            <Icon name="checkmark-circle-filled" class="summary-bar-chart__icon-stroke summary-bar-chart__icon-stroke--1 size-3.5 shrink-0" />
                                            <span>I’ll get my coat</span>
                                        </li>
                                        <li class="pie-chart-legend__item">
                                            <span class="chart-metric | pie-chart-legend__value" data-percent="45%" data-count="112"></span>
                                            <Icon name="delete-circle-filled" class="summary-bar-chart__icon-stroke summary-bar-chart__icon-stroke--2 size-3.5 shrink-0" />
                                            <span>Coffee might be better</span>
                                        </li>
                                    </ul>
                                </figcaption>
                            </figure>
                        </Widget>
                    </div>
                    <!-- Example of a Dictionary field type (Horizontal Lollipop Chart with icons) with Pagination. We should dynamically generate the ids to be unique here, so that everything remains accessible. -->
                    <div class="starting-style-transition w-full min-h-61 min-[1100px]:w-1/2 px-3">
                        <Widget
                            :title="__('What’s your favourite country?')"
                            title-tag="h2"
                            class="h-full"
                            icon="fieldtype-dictionary"
                            icon-class="hidden @xs/widget:block size-4 text-gray-500"
                        >
                            <template #actions>
                                <Pagination
                                    :resource-meta="dictionaryChartMeta"
                                    :show-totals="false"
                                    :show-page-links="false"
                                    :show-per-page-selector="false"
                                    :scroll-to-top="false"
                                    @page-selected="dictionaryChartPage = $event"
                                />
                            </template>
                            <p v-if="dictionaryChartPage === 1" class="sr-only" aria-live="polite">Japan 40%, Italy 35%, USA 10%, UK 8%, France 3%</p>
                            <p v-else class="sr-only" aria-live="polite">Germany 2%, Spain 1%, Portugal 1%</p>
                            <figure v-if="dictionaryChartPage === 1" class="grid p-6" :aria-labelledby="dictionaryChart1CaptionId">
                                <ol class="grid grid-cols-[auto_max-content_1fr] items-center list-none m-0 gap-x-2.25 [&:not(:has(>_:nth-child(5)))]:pt-3" aria-hidden="true">
                                    <li class="contents">
                                        <span class="text-xs font-medium tabular-nums text-gray-700 dark:text-gray-400">1</span>
                                        <span class="max-w-25 truncate me-2 text-xs text-gray-900 dark:text-gray-50">Japan</span>
                                        <div class="flex items-center gap-1">
                                            <div class="h-px w-[40%] bg-gray-200 dark:bg-gray-600" />
                                            <div class="text-lg">🇯🇵</div>
                                            <span class="chart-metric | min-w-8.5 ms-1 text-[0.75rem] font-medium tabular-nums text-gray-700 dark:text-gray-50" data-percent="40%" data-count="99"></span>
                                        </div>
                                    </li>
                                    <li class="contents">
                                        <span class="text-xs font-medium tabular-nums text-gray-700 dark:text-gray-400">2</span>
                                        <span class="max-w-25 truncate me-2 text-xs text-gray-900 dark:text-gray-50">Italy</span>
                                        <div class="flex items-center gap-1">
                                            <div class="h-px w-[35%] bg-gray-200 dark:bg-gray-600" />
                                            <div class="text-lg">🇮🇹</div>
                                            <span class="chart-metric | min-w-8.5 ms-1 text-[0.75rem] font-medium tabular-nums text-gray-700 dark:text-gray-50" data-percent="35%" data-count="87"></span>
                                        </div>
                                    </li>
                                    <li class="contents">
                                        <span class="text-xs font-medium tabular-nums text-gray-700 dark:text-gray-400">3</span>
                                        <span class="max-w-25 truncate me-2 text-xs text-gray-900 dark:text-gray-50">USA</span>
                                        <div class="flex items-center gap-1">
                                            <div class="h-px w-[10%] bg-gray-200 dark:bg-gray-600" />
                                            <div class="text-lg">🇺🇸</div>
                                            <span class="chart-metric | min-w-8.5 ms-1 text-[0.75rem] font-medium tabular-nums text-gray-700 dark:text-gray-50" data-percent="10%" data-count="25"></span>
                                        </div>
                                    </li>
                                    <li class="contents">
                                        <span class="text-xs font-medium tabular-nums text-gray-700 dark:text-gray-400">4</span>
                                        <span class="max-w-25 truncate me-2 text-xs text-gray-900 dark:text-gray-50">UK</span>
                                        <div class="flex items-center gap-1">
                                            <div class="h-px w-[8%] bg-gray-200 dark:bg-gray-600" />
                                            <div class="text-lg">🇬🇧</div>
                                            <span class="chart-metric | min-w-8.5 ms-1 text-[0.75rem] font-medium tabular-nums text-gray-700 dark:text-gray-50" data-percent="8%" data-count="20"></span>
                                        </div>
                                    </li>
                                    <li class="contents">
                                        <span class="text-xs font-medium tabular-nums text-gray-700 dark:text-gray-400">5</span>
                                        <span class="max-w-25 truncate me-2 text-xs text-gray-900 dark:text-gray-50">France</span>
                                        <div class="flex items-center gap-1">
                                            <div class="h-px w-[3%] bg-gray-200 dark:bg-gray-600" />
                                            <div class="text-lg">🇫🇷</div>
                                            <span class="chart-metric | min-w-8.5 ms-1 text-[0.75rem] font-medium tabular-nums text-gray-700 dark:text-gray-50" data-percent="3%" data-count="7"></span>
                                        </div>
                                    </li>
                                </ol>
                                <figcaption :id="dictionaryChart1CaptionId" class="sr-only">
                                    {{ __('What’s your favourite country?: Japan 40%, Italy 35%, USA 10%, UK 8%, France 3%') }}
                                </figcaption>
                            </figure>
                            <figure v-else-if="dictionaryChartPage === 2" class="grid p-6" :aria-labelledby="dictionaryChart1Page2CaptionId">
                                <ol class="grid grid-cols-[auto_max-content_1fr] items-center list-none m-0 gap-x-2.25 [&:not(:has(>_:nth-child(5)))]:pt-3" aria-hidden="true">
                                    <li class="contents">
                                        <span class="text-xs font-medium tabular-nums text-gray-700 dark:text-gray-400">6</span>
                                        <span class="max-w-25 truncate me-2 text-xs text-gray-900 dark:text-gray-50">Germany</span>
                                        <div class="flex items-center gap-1">
                                            <div class="h-px w-[2%] bg-gray-200 dark:bg-gray-600" />
                                            <div class="text-lg">🇩🇪</div>
                                            <span class="chart-metric | min-w-8.5 ms-1 text-[0.75rem] font-medium tabular-nums text-gray-700 dark:text-gray-50" data-percent="2%" data-count="5"></span>
                                        </div>
                                    </li>
                                    <li class="contents">
                                        <span class="text-xs font-medium tabular-nums text-gray-700 dark:text-gray-400">7</span>
                                        <span class="max-w-25 truncate me-2 text-xs text-gray-900 dark:text-gray-50">Spain</span>
                                        <div class="flex items-center gap-1">
                                            <div class="h-px w-[1%] bg-gray-200 dark:bg-gray-600" />
                                            <div class="text-lg">🇪🇸</div>
                                            <span class="chart-metric | min-w-8.5 ms-1 text-[0.75rem] font-medium tabular-nums text-gray-700 dark:text-gray-50" data-percent="1%" data-count="2"></span>
                                        </div>
                                    </li>
                                    <li class="contents">
                                        <span class="text-xs font-medium tabular-nums text-gray-700 dark:text-gray-400">8</span>
                                        <span class="max-w-25 truncate me-2 text-xs text-gray-900 dark:text-gray-50">Portugal</span>
                                        <div class="flex items-center gap-1">
                                            <div class="h-px w-[1%] bg-gray-200 dark:bg-gray-600" />
                                            <div class="text-lg">🇵🇹</div>
                                            <span class="chart-metric | min-w-8.5 ms-1 text-[0.75rem] font-medium tabular-nums text-gray-700 dark:text-gray-50" data-percent="1%" data-count="2"></span>
                                        </div>
                                    </li>
                                </ol>
                                <figcaption :id="dictionaryChart1Page2CaptionId" class="sr-only">
                                    {{ __('What’s your favourite country?: Germany 2%, Spain 1%, Portugal 1%') }}
                                </figcaption>
                            </figure>
                        </Widget>
                    </div>
                    <!-- Example of a Number field type (Horizontal Bar Chart with info). We should dynamically generate the ids to be unique here, so that everything remains accessible. -->
                    <div class="starting-style-transition w-full min-h-61 min-[1100px]:w-1/2 px-3">
                        <Widget
                            :title="__('How old are you?')"
                            title-tag="h2"
                            class="h-full"
                            icon="number"
                            icon-class="hidden @xs/widget:block size-4 text-gray-500"
                        >
                            <figure class="grid p-6 pt-3 ps-4" :aria-labelledby="numberBarChart1CaptionId">
                                <div aria-hidden="true" class="flex gap-2.5 pb-5 -ms-1">
                                    <div class="inline-flex items-center gap-2 px-2 py-1 rounded-md border border-gray-200 dark:border-gray-700">
                                        <span class="text-xs font-semibold st-text-trim-cap tabular-nums">4–44</span>
                                        <span class="text-[0.75rem] text-gray-500 dark:text-gray-300">{{ __('Min–Max') }}</span>
                                    </div>
                                    <div class="inline-flex items-center gap-2 px-2 py-1 rounded-md border border-gray-200 dark:border-gray-700">
                                        <span class="text-xs font-semibold st-text-trim-cap tabular-nums">26.2</span>
                                        <span class="text-[0.75rem] text-gray-500 dark:text-gray-300">{{ __('Average') }}</span>
                                    </div>
                                </div>
                                <ol class="grid grid-cols-[auto_1fr_max-content] items-center list-none m-0 gap-x-3 gap-y-1 p-0 [&:not(:has(>_:nth-child(5)))]:pt-4 text-end" aria-hidden="true">
                                    <li class="contents">
                                        <span class="text-xs tabular-nums text-gray-700 dark:text-gray-400">0–9</span>
                                        <div class="h-2.5 w-[20%] rounded-full bg-chart-1" />
                                        <span class="chart-metric | text-xs font-medium tabular-nums text-gray-700 dark:text-gray-400" data-percent="20%" data-count="50"></span>
                                    </li>
                                    <li class="contents">
                                        <span class="text-xs tabular-nums text-gray-700 dark:text-gray-400">10–19</span>
                                        <div class="h-2.5 w-[15%] rounded-full bg-chart-1" />
                                        <span class="chart-metric | text-xs font-medium tabular-nums text-gray-700 dark:text-gray-400" data-percent="15%" data-count="37"></span>
                                    </li>
                                    <li class="contents">
                                        <span class="text-xs tabular-nums text-gray-700 dark:text-gray-400">20–29</span>
                                        <div class="h-2.5 w-[30%] rounded-full bg-chart-1" />
                                        <span class="chart-metric | text-xs font-medium tabular-nums text-gray-700 dark:text-gray-400" data-percent="30%" data-count="74"></span>
                                    </li>
                                    <li class="contents">
                                        <span class="text-xs tabular-nums text-gray-700 dark:text-gray-400">30–39</span>
                                        <div class="h-2.5 w-[20%] rounded-full bg-chart-1" />
                                        <span class="chart-metric | text-xs font-medium tabular-nums text-gray-700 dark:text-gray-400" data-percent="20%" data-count="50"></span>
                                    </li>
                                    <li class="contents">
                                        <span class="text-xs tabular-nums text-gray-700 dark:text-gray-400">40–49</span>
                                        <div class="h-2.5 w-[10%] rounded-full bg-chart-1" />
                                        <span class="chart-metric | text-xs font-medium tabular-nums text-gray-700 dark:text-gray-400" data-percent="20%" data-count="50"></span>
                                    </li>
                                </ol>
                                <figcaption :id="numberBarChart1CaptionId" class="sr-only">
                                    {{ __('How old are you?: Min–Max 4–44, Average 26.2. Age distribution: 0–9: 20%, 10–19: 15%, 20–29: 30%, 30–39: 20%, 40–49: 20%') }}
                                </figcaption>
                            </figure>
                        </Widget>
                    </div>
                    <!-- Example of a Star Rating field type (Horizontal Star Rating Bar Chart with info). We should dynamically generate the ids to be unique here, so that everything remains accessible. -->
                    <div class="starting-style-transition w-full min-h-61 min-[1100px]:w-1/2 px-3">
                        <Widget
                            :title="__('How would you rate the hotel?')"
                            title-tag="h2"
                            class="h-full"
                            icon="star"
                            icon-class="hidden @xs/widget:block size-4 text-gray-500"
                        >
                            <figure class="grid p-6 pt-3 ps-4" :aria-labelledby="starRatingChart1CaptionId">
                                <div aria-hidden="true" class="pb-5">
                                    <div class="inline-flex items-center gap-2 px-2 py-1 -ms-1 rounded-md border border-gray-200 dark:border-gray-700">
                                        <Icon name="star-filled" class="size-3.5 shrink-0 text-gray-950 dark:text-gray-300" />
                                        <Icon name="star-filled" class="size-3.5 shrink-0 text-gray-950 dark:text-gray-300" />
                                        <Icon name="star-filled" class="size-3.5 shrink-0 text-gray-950 dark:text-gray-300" />
                                        <Icon name="star" class="size-3.5 shrink-0 text-gray-950 dark:text-gray-300" />
                                        <Icon name="star" class="size-3.5 shrink-0 text-gray-950 dark:text-gray-300" />
                                        <span class="text-[0.75rem] text-gray-500 dark:text-gray-300">3/5 Average</span>
                                    </div>
                                </div>
                                <!--
                                    Star row colors use oklch relative lightness from --color-chart-1-legend:
                                    - Midpoint star uses chart-1-legend (3/5 in the hotel demo; 4/10 in the restaurant demo).
                                    - 5 stars or fewer: darker stars use l*0.9, l*0.8 (−0.1 per step); lighter stars use l*1.2, l*1.4 (+0.2 per step).
                                    - 6 stars or more: darker stars use −0.05 per step (e.g. l*0.9, l*0.85, l*0.8, l*0.75, l*0.7); lighter stars use l*1.1, l*1.2, l*1.4 (+0.1, then +0.2).
                                 -->
                                <ol class="grid grid-cols-[auto_auto_1fr_max-content] items-center list-none m-0 gap-x-3 gap-y-1 p-0 [&:not(:has(>_:nth-child(5)))]:pt-4" aria-hidden="true">
                                    <li class="contents">
                                        <span class="text-xs tabular-nums text-gray-700 dark:text-gray-400">5</span>
                                        <Icon name="star-filled" class="size-3.5 shrink-0 -ms-0.75 text-[oklch(from_var(--color-chart-1-legend)_calc(l*0.8)_c_h)]" />
                                        <div class="h-2.5 w-[20%] rounded-full bg-[oklch(from_var(--color-chart-1-legend)_calc(l*0.8)_c_h)]" />
                                        <span class="chart-metric | text-xs font-medium tabular-nums text-gray-700 dark:text-gray-400" data-percent="20%" data-count="50"></span>
                                    </li>
                                    <li class="contents">
                                        <span class="text-xs tabular-nums text-gray-700 dark:text-gray-400">4</span>
                                        <Icon name="star-filled" class="size-3.5 shrink-0 -ms-0.75 text-[oklch(from_var(--color-chart-1-legend)_calc(l*0.9)_c_h)]" />
                                        <div class="h-2.5 w-[15%] rounded-full bg-[oklch(from_var(--color-chart-1-legend)_calc(l*0.9)_c_h)]" />
                                        <span class="chart-metric | text-xs font-medium tabular-nums text-gray-700 dark:text-gray-400" data-percent="15%" data-count="37"></span>
                                    </li>
                                    <li class="contents">
                                        <span class="text-xs tabular-nums text-gray-700 dark:text-gray-400">3</span>
                                        <Icon name="star-filled" class="size-3.5 shrink-0 -ms-0.75 text-chart-1-legend" />
                                        <div class="h-2.5 w-[30%] rounded-full bg-chart-1-legend" />
                                        <span class="chart-metric | text-xs font-medium tabular-nums text-gray-700 dark:text-gray-400" data-percent="30%" data-count="74"></span>
                                    </li>
                                    <li class="contents">
                                        <span class="text-xs tabular-nums text-gray-700 dark:text-gray-400">2</span>
                                        <Icon name="star-filled" class="size-3.5 shrink-0 -ms-0.75 text-[oklch(from_var(--color-chart-1-legend)_calc(l*1.2)_c_h)]" />
                                        <div class="h-2.5 w-[20%] rounded-full bg-[oklch(from_var(--color-chart-1-legend)_calc(l*1.2)_c_h)]" />
                                        <span class="chart-metric | text-xs font-medium tabular-nums text-gray-700 dark:text-gray-400" data-percent="20%" data-count="50"></span>
                                    </li>
                                    <li class="contents">
                                        <span class="text-xs tabular-nums text-gray-700 dark:text-gray-400">1</span>
                                        <Icon name="star-filled" class="size-3.5 shrink-0 -ms-0.75 text-[oklch(from_var(--color-chart-1-legend)_calc(l*1.4)_c_h)]" />
                                        <div class="h-2.5 w-[10%] rounded-full bg-[oklch(from_var(--color-chart-1-legend)_calc(l*1.4)_c_h)]" />
                                        <span class="chart-metric | text-xs font-medium tabular-nums text-gray-700 dark:text-gray-400" data-percent="20%" data-count="50"></span>
                                    </li>
                                </ol>
                                <figcaption :id="starRatingChart1CaptionId" class="sr-only">
                                    {{ __('How would you rate the hotel?: 3/5 average. Rating distribution: 5 stars 20%, 4 stars 15%, 3 stars 30%, 2 stars 20%, 1 star 20%') }}
                                </figcaption>
                            </figure>
                        </Widget>
                    </div>
                    <!-- Example of a Star Rating field type greater than 5 stars. It should switch to a vertical bar chart when it's greater than 5 stars). We should dynamically generate the ids to be unique here, so that everything remains accessible. -->
                    <div class="starting-style-transition w-full min-h-61 min-[1100px]:w-1/2 px-3">
                        <Widget
                            :title="__('How would you rate the restaurant?')"
                            title-tag="h2"
                            class="h-full"
                            icon="star"
                            icon-class="hidden @xs/widget:block size-4 text-gray-500"
                        >
                            <figure class="vertical-bar-chart-figure" :aria-labelledby="starRatingChart2CaptionId">
                                <div aria-hidden="true" class="pb-5">
                                    <div class="inline-flex items-center gap-2 px-2 py-1 -ms-1 rounded-md border border-gray-200 dark:border-gray-700">
                                        <Icon name="star-filled" class="size-3.5 shrink-0 text-gray-950 dark:text-gray-300" />
                                        <Icon name="star-filled" class="size-3.5 shrink-0 text-gray-950 dark:text-gray-300" />
                                        <Icon name="star-filled" class="size-3.5 shrink-0 text-gray-950 dark:text-gray-300" />
                                        <Icon name="star-filled" class="size-3.5 shrink-0 text-gray-950 dark:text-gray-300" />
                                        <Icon name="star-filled" class="size-3.5 shrink-0 text-gray-950 dark:text-gray-300" />
                                        <Icon name="star-filled" class="size-3.5 shrink-0 text-gray-950 dark:text-gray-300" />
                                        <Icon name="star-filled" class="size-3.5 shrink-0 text-gray-950 dark:text-gray-300" />
                                        <Icon name="star" class="size-3.5 shrink-0 text-gray-950 dark:text-gray-300" />
                                        <Icon name="star" class="size-3.5 shrink-0 text-gray-950 dark:text-gray-300" />
                                        <Icon name="star" class="size-3.5 shrink-0 text-gray-950 dark:text-gray-300" />
                                        <span class="text-[0.75rem] text-gray-500 dark:text-gray-300">7/10 Average</span>
                                    </div>
                                </div>
                                <ol class="vertical-bar-chart" aria-hidden="true">
                                    <li class="vertical-bar-chart__bar">
                                        <div class="vertical-bar-chart__plot">
                                            <span class="chart-metric | vertical-bar-chart__value" data-percent="4%" data-count="10"></span>
                                            <div class="vertical-bar-chart__fill" style="flex: 0 0 22%;" />
                                        </div>
                                        <span class="vertical-bar-chart__scale-label">1</span>
                                    </li>
                                    <li class="vertical-bar-chart__bar">
                                        <div class="vertical-bar-chart__plot">
                                            <span class="chart-metric | vertical-bar-chart__value" data-percent="5%" data-count="12"></span>
                                            <div class="vertical-bar-chart__fill" style="flex: 0 0 27%;" />
                                        </div>
                                        <span class="vertical-bar-chart__scale-label">2</span>
                                    </li>
                                    <li class="vertical-bar-chart__bar">
                                        <div class="vertical-bar-chart__plot">
                                            <span class="chart-metric | vertical-bar-chart__value" data-percent="6%" data-count="15"></span>
                                            <div class="vertical-bar-chart__fill" style="flex: 0 0 33%;" />
                                        </div>
                                        <span class="vertical-bar-chart__scale-label">3</span>
                                    </li>
                                    <li class="vertical-bar-chart__bar">
                                        <div class="vertical-bar-chart__plot">
                                            <span class="chart-metric | vertical-bar-chart__value" data-percent="7%" data-count="17"></span>
                                            <div class="vertical-bar-chart__fill" style="flex: 0 0 38%;" />
                                        </div>
                                        <span class="vertical-bar-chart__scale-label">4</span>
                                    </li>
                                    <li class="vertical-bar-chart__bar">
                                        <div class="vertical-bar-chart__plot">
                                            <span class="chart-metric | vertical-bar-chart__value" data-percent="8%" data-count="20"></span>
                                            <div class="vertical-bar-chart__fill" style="flex: 0 0 44%;" />
                                        </div>
                                        <span class="vertical-bar-chart__scale-label">5</span>
                                    </li>
                                    <li class="vertical-bar-chart__bar">
                                        <div class="vertical-bar-chart__plot">
                                            <span class="chart-metric | vertical-bar-chart__value" data-percent="10%" data-count="25"></span>
                                            <div class="vertical-bar-chart__fill" style="flex: 0 0 56%;" />
                                        </div>
                                        <span class="vertical-bar-chart__scale-label">6</span>
                                    </li>
                                    <li class="vertical-bar-chart__bar">
                                        <div class="vertical-bar-chart__plot">
                                            <span class="chart-metric | vertical-bar-chart__value" data-percent="12%" data-count="30"></span>
                                            <div class="vertical-bar-chart__fill" style="flex: 0 0 67%;" />
                                        </div>
                                        <span class="vertical-bar-chart__scale-label">7</span>
                                    </li>
                                    <li class="vertical-bar-chart__bar">
                                        <div class="vertical-bar-chart__plot">
                                            <span class="chart-metric | vertical-bar-chart__value" data-percent="14%" data-count="35"></span>
                                            <div class="vertical-bar-chart__fill" style="flex: 0 0 78%;" />
                                        </div>
                                        <span class="vertical-bar-chart__scale-label">8</span>
                                    </li>
                                    <li class="vertical-bar-chart__bar">
                                        <div class="vertical-bar-chart__plot">
                                            <span class="chart-metric | vertical-bar-chart__value" data-percent="16%" data-count="40"></span>
                                            <div class="vertical-bar-chart__fill" style="flex: 0 0 89%;" />
                                        </div>
                                        <span class="vertical-bar-chart__scale-label">9</span>
                                    </li>
                                    <li class="vertical-bar-chart__bar">
                                        <div class="vertical-bar-chart__plot">
                                            <span class="chart-metric | vertical-bar-chart__value" data-percent="18%" data-count="45"></span>
                                            <div class="vertical-bar-chart__fill" style="flex: 0 0 100%;" />
                                        </div>
                                        <span class="vertical-bar-chart__scale-label">10</span>
                                    </li>
                                </ol>
                                <figcaption :id="starRatingChart2CaptionId" class="sr-only">
                                    {{ __('How would you rate the restaurant?: 7/10 average. Rating distribution: 10 stars 18%, 9 stars 16%, 8 stars 14%, 7 stars 12%, 6 stars 10%, 5 stars 8%, 4 stars 7%, 3 stars 6%, 2 stars 5%, 1 star 4%') }}
                                </figcaption>
                            </figure>
                        </Widget>
                    </div>
                    <!-- Example of a Toggle field type (Single fillable bar chart). We should dynamically generate the ids to be unique here, so that everything remains accessible. -->
                    <div class="starting-style-transition w-full min-h-61 min-[1100px]:w-1/2 px-3">
                        <Widget
                            :title="__('Sign up to our newsletter')"
                            title-tag="h2"
                            class="h-full"
                            icon="fieldtype-toggle"
                            icon-class="hidden @xs/widget:block size-4 text-gray-500"
                        >
                            <figure class="grid p-6 pt-3 ps-4" :aria-labelledby="toggleBarChart1CaptionId">
                                <div aria-hidden="true" class="pb-5">
                                    <div class="inline-flex items-center gap-2 px-2 -ms-1 py-1.25 rounded-md border border-gray-200 dark:border-gray-700">
                                        <span class="chart-metric | text-md font-semibold st-text-trim-cap tabular-nums text-gray-950 dark:text-gray-50" data-percent="20%" data-count="50"></span> <span class="text-[0.75rem] text-gray-500 dark:text-gray-300">{{ __('of users checked the toggle') }}</span>
                                    </div>
                                </div>
                                <ol class="grid grid-cols-[auto_1fr_max-content] items-center list-none m-0 gap-x-3 gap-y-1 p-0 pt-2" aria-hidden="true">
                                    <li class="contents">
                                        <Switch :model-value="true" tabindex="-1" class="pointer-events-none data-[state=checked]:border-gray-950! data-[state=checked]:bg-gray-950!" />
                                        <div class="flex h-2.5 w-full overflow-hidden rounded-full bg-[hsl(from_var(--color-chart-1-legend)_h_s_l/0.15)]">
                                            <div class="h-full w-[20%] shrink-0 rounded-full bg-chart-1-legend" />
                                        </div>
                                        <span class="chart-metric | text-xs font-medium tabular-nums text-gray-700 dark:text-gray-400" data-percent="20%" data-count="50"></span>
                                    </li>
                                </ol>
                                <figcaption :id="toggleBarChart1CaptionId" class="sr-only">
                                    {{ __('Sign up to our newsletter: 20% of users checked the toggle.') }}
                                </figcaption>
                            </figure>
                        </Widget>
                    </div>
                    </div>
                </div>
            </template>
        </FormSubmissionListing>

        <Modal :open="exportModalOpen" @update:open="exportModalOpen = $event" :title="__('Export Submissions')">
            <div class="space-y-4">
                <div>
                    <label class="block mb-1.5 text-sm font-medium">{{ __('Format') }}</label>
                    <RadioGroup v-model="exportFormat" inline>
                        <Radio v-for="format in exporters" :key="format.handle" :value="format.handle" :label="format.title" />
                    </RadioGroup>
                </div>

                <div>
                    <label class="block mb-1.5 text-sm font-medium">{{ __('Submissions') }}</label>
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
