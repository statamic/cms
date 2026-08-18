<script setup>
import { ref, computed, watch } from 'vue';
import axios from 'axios';
import Head from '@/pages/layout/Head.vue';
import {
    Button,
    CommandPaletteItem,
    Dropdown,
    DropdownItem,
    DropdownMenu,
    Header,
    HorizontalBarChart,
    HorizontalLollipopChart,
    Icon,
    ImagePieChart,
    Modal,
    Pagination,
    PieChart,
    Radio,
    RadioGroup,
    Switch,
    ToggleGroup,
    ToggleItem,
    VerticalBarChart,
    Widget,
} from '@ui';
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

const wakeMeUpResponses = [
    { label: 'Alarm, no mercy', percent: 68, count: 169 },
    { label: 'Hit snooze', percent: 18, count: 45 },
    { label: 'Woken by someone else', percent: 9, count: 22 },
    { label: 'Other', percent: 5, count: 12, clickable: true },
];

const chartData = {
    age: [
        { label: '0–9', percent: 20, count: 50 },
        { label: '10–19', percent: 15, count: 37 },
        { label: '20–29', percent: 30, count: 74 },
        { label: '30–39', percent: 20, count: 50 },
        { label: '40–49', percent: 20, count: 50, value: 10 },
    ],
    artists: [
        { label: 'Radiohead', percent: 55, count: 136 },
        { label: 'Death Cab for Cutie', percent: 35, count: 87 },
        { label: 'Olivia Rodrigo', percent: 5, count: 12 },
        { label: 'The Cure', percent: 5, count: 12 },
    ],
    countries: [
        { label: 'Japan', percent: 40, count: 99, emoji: '🇯🇵' },
        { label: 'Italy', percent: 35, count: 87, emoji: '🇮🇹' },
        { label: 'USA', percent: 10, count: 25, emoji: '🇺🇸' },
        { label: 'UK', percent: 8, count: 20, emoji: '🇬🇧' },
        { label: 'France', percent: 3, count: 7, emoji: '🇫🇷' },
        { label: 'Germany', percent: 2, count: 5, emoji: '🇩🇪' },
        { label: 'Spain', percent: 1, count: 2, emoji: '🇪🇸' },
        { label: 'Portugal', percent: 1, count: 2, emoji: '🇵🇹' },
    ],
    hotelRatings: [
        { label: '5', percent: 20, count: 50, markerClass: 'text-[oklch(from_var(--color-chart-1-legend)_calc(l*0.8)_c_h)]', barClass: 'bg-[oklch(from_var(--color-chart-1-legend)_calc(l*0.8)_c_h)]' },
        { label: '4', percent: 15, count: 37, markerClass: 'text-[oklch(from_var(--color-chart-1-legend)_calc(l*0.9)_c_h)]', barClass: 'bg-[oklch(from_var(--color-chart-1-legend)_calc(l*0.9)_c_h)]' },
        { label: '3', percent: 30, count: 74, markerClass: 'text-chart-1-legend', barClass: 'bg-chart-1-legend' },
        { label: '2', percent: 20, count: 50, markerClass: 'text-[oklch(from_var(--color-chart-1-legend)_calc(l*1.2)_c_h)]', barClass: 'bg-[oklch(from_var(--color-chart-1-legend)_calc(l*1.2)_c_h)]' },
        { label: '1', percent: 10, count: 50, markerClass: 'text-[oklch(from_var(--color-chart-1-legend)_calc(l*1.4)_c_h)]', barClass: 'bg-[oklch(from_var(--color-chart-1-legend)_calc(l*1.4)_c_h)]' },
    ],
    liveBefore: [
        { label: 'Yep', percent: 55, count: 136 },
        { label: 'Nope', percent: 35, count: 87 },
        { label: 'Maybe', percent: 10, count: 25 },
    ],
    pint: [
        { label: 'I’ll get my coat', percent: 55, count: 136, icon: 'checkmark-circle-filled' },
        { label: 'Coffee might be better', percent: 45, count: 112, icon: 'delete-circle-filled' },
    ],
    recommendation: [
        { label: '0', percent: 2, count: 5 },
        { label: '1', percent: 1, count: 2 },
        { label: '2', percent: 3, count: 7 },
        { label: '3', percent: 4, count: 10 },
        { label: '4', percent: 6, count: 15 },
        { label: '5', percent: 8, count: 20 },
        { label: '6', percent: 12, count: 30 },
        { label: '7', percent: 20, count: 50 },
        { label: '8', percent: 35, count: 87 },
        { label: '9', percent: 48, count: 119 },
        { label: '10', percent: 61, count: 151 },
    ],
    restaurantRatings: [
        { label: '1', percent: 4, count: 10 },
        { label: '2', percent: 5, count: 12 },
        { label: '3', percent: 6, count: 15 },
        { label: '4', percent: 7, count: 17 },
        { label: '5', percent: 8, count: 20 },
        { label: '6', percent: 10, count: 25 },
        { label: '7', percent: 12, count: 30 },
        { label: '8', percent: 14, count: 35 },
        { label: '9', percent: 16, count: 40 },
        { label: '10', percent: 18, count: 45 },
    ],
    seasons: [
        { label: 'Summer', percent: 55, count: 136 },
        { label: 'Autumn', percent: 25, count: 62 },
        { label: 'Spring', percent: 15, count: 37 },
        { label: 'Winter', percent: 10, count: 25 },
    ],
    spiritAnimals: [
        { label: 'Actually', percent: 55, count: 136, badge: 'A', image: 'https://picsum.photos/id/159/320/320' },
        { label: 'Nope', percent: 45, count: 112, badge: 'B', image: 'https://picsum.photos/id/485/320/320' },
    ],
    toggle: [
        { label: 'Checked', percent: 20, count: 50 },
    ],
    wakeMeUp: [
        { label: 'Before you Go Go', percent: 45, count: 112 },
        { label: 'Bring me Back to Life', percent: 30, count: 74 },
        { label: 'When September Ends', percent: 15, count: 37 },
        { label: 'Never', percent: 10, count: 25 },
    ],
    wakeMeUpOther: [
        { label: 'Bohemian Rhapsody', percent: 3, count: 7 },
        { label: 'Stairway to Heaven', percent: 1, count: 2 },
        { label: 'Wonderwall', percent: 1, count: 2 },
    ],
};

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

const dictionaryChartItems = computed(() => {
    const start = (dictionaryChartPage.value - 1) * dictionaryChartPerPage;

    return chartData.countries.slice(start, start + dictionaryChartPerPage).map((item, index) => ({
        ...item,
        rank: start + index + 1,
    }));
});

const dictionaryChartAccessibleLabel = computed(() => {
    if (dictionaryChartPage.value === 1) {
        return __('What’s your favourite country?: Japan 40%, Italy 35%, USA 10%, UK 8%, France 3%');
    }

    return __('What’s your favourite country?: Germany 2%, Spain 1%, Portugal 1%');
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
                <div data-submission-summary class="mt-3 pb-8">
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
                            <PieChart
                                :items="chartData.wakeMeUp"
                                :metric="summaryChartMetric"
                                :accessible-label="__('Wake me up: Before you Go Go 45%, Bring me Back to Life 30%, When September Ends 15%, Never 10%')"
                            />
                        </Widget>
                    </div>
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
                            <PieChart
                                v-if="wakeMeUpChartPage === 1"
                                :items="wakeMeUpResponses"
                                :metric="summaryChartMetric"
                                :accessible-label="pieChart2AccessibleLabel"
                                @select="selectNextChartPage"
                            />
                            <PieChart
                                v-else-if="wakeMeUpChartPage === 2"
                                :items="chartData.wakeMeUpOther"
                                :segments="wakeMeUpResponses"
                                :focused-index="3"
                                :metric="summaryChartMetric"
                                :accessible-label="pieChart2AccessibleLabel"
                            />
                        </Widget>
                    </div>
                    <div class="starting-style-transition w-full min-h-61 min-[1100px]:w-1/2 px-3">
                        <Widget
                            :title="__('Rank your favourite seasons')"
                            title-tag="h2"
                            class="h-full"
                            icon="rank"
                            icon-class="hidden @xs/widget:block size-4 text-gray-500"
                        >
                            <HorizontalLollipopChart
                                :items="chartData.seasons"
                                :metric="summaryChartMetric"
                                :accessible-label="__('Ranked favourite seasons: Summer 55%, Autumn 25%, Spring 15%, Winter 10%')"
                            />
                        </Widget>
                    </div>
                    <div class="starting-style-transition w-full min-h-61 min-[1100px]:w-1/2 px-3">
                        <Widget
                            :title="__('Have you seen us live before?')"
                            title-tag="h2"
                            class="h-full"
                            icon="fieldtype-select"
                            icon-class="hidden @xs/widget:block size-4 text-gray-500"
                        >
                            <HorizontalBarChart
                                :items="chartData.liveBefore"
                                :metric="summaryChartMetric"
                                :accessible-label="__('Have you seen us live before?: Yep 55%, Nope 35%, Maybe 10%')"
                            />
                        </Widget>
                    </div>
                    <div class="starting-style-transition w-full min-h-61 min-[1100px]:w-1/2 px-3">
                        <Widget
                            :title="__('How likely are you to recommend us?')"
                            title-tag="h2"
                            class="h-full"
                            icon="scale-up"
                            icon-class="hidden @xs/widget:block size-4 text-gray-500"
                        >
                            <VerticalBarChart
                                :items="chartData.recommendation"
                                :metric="summaryChartMetric"
                                :accessible-label="__('Average 8.1. Recommendation score distribution: 0: 2%, 1: 1%, 2: 3%, 3: 4%, 4: 6%, 5: 8%, 6: 12%, 7: 20%, 8: 35%, 9: 48%, 10: 61%')"
                            >
                                <template #summary>
                                    <div class="inline-flex items-center gap-2 px-2 py-1.25 rounded-md border border-gray-200 dark:border-gray-700">
                                        <span class="text-md font-semibold st-text-trim-cap tabular-nums text-green-600 dark:text-green-400">8.1</span> <span class="text-[0.75rem] text-gray-500 dark:text-gray-300">Average</span>
                                    </div>
                                </template>
                            </VerticalBarChart>
                        </Widget>
                    </div>
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
                            <HorizontalBarChart
                                v-if="imageChoiceChart1Type === 'bar'"
                                :items="chartData.spiritAnimals"
                                :metric="summaryChartMetric"
                                :accessible-label="__('What is your spirit animal?: Actually 55%, Nope 45%')"
                            >
                                <template #marker="{ item }">
                                    <img class="size-10 shrink-0 object-cover rounded-full" :src="item.image" alt="" />
                                    <span class="flex size-6 shrink-0 items-center justify-center rounded-md border border-gray-300 bg-white text-xs font-bold text-gray-800 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200">{{ item.badge }}</span>
                                </template>
                            </HorizontalBarChart>
                            <ImagePieChart
                                v-else
                                :items="chartData.spiritAnimals"
                                :metric="summaryChartMetric"
                                :accessible-label="__('What is your spirit animal?: Actually 55%, Nope 45%')"
                            />
                        </Widget>
                    </div>
                    <div class="starting-style-transition w-full min-h-61 min-[1100px]:w-1/2 px-3">
                        <Widget
                            :title="__('Who have you seen live?')"
                            title-tag="h2"
                            class="h-full"
                            icon="fieldtype-checkboxes"
                            icon-class="hidden @xs/widget:block size-4 text-gray-500"
                        >
                            <HorizontalBarChart
                                :items="chartData.artists"
                                :metric="summaryChartMetric"
                                :accessible-label="__('Who have you seen live?: Radiohead 55%, Death Cab for Cutie 35%, Olivia Rodrigo 5%, The Cure 5%')"
                            >
                                <template #marker="{ index }">
                                    <Icon name="checkbox-filled" :class="`summary-bar-chart__icon-stroke--${index + 1}`" class="summary-bar-chart__icon-stroke size-3.5 shrink-0" />
                                </template>
                            </HorizontalBarChart>
                        </Widget>
                    </div>
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
                            <HorizontalBarChart
                                v-if="yesNoChart1Type === 'bar'"
                                :items="chartData.pint"
                                :metric="summaryChartMetric"
                                :accessible-label="__('Do you fancy a pint?: I’ll get my coat 55%, Coffee might be better 45%')"
                            >
                                <template #marker="{ item, index }">
                                    <Icon :name="item.icon" :class="`summary-bar-chart__icon-stroke--${index + 1}`" class="summary-bar-chart__icon-stroke size-3.5 shrink-0" />
                                </template>
                            </HorizontalBarChart>
                            <PieChart
                                v-else
                                :items="chartData.pint"
                                :metric="summaryChartMetric"
                                :accessible-label="__('Do you fancy a pint?: I’ll get my coat 55%, Coffee might be better 45%')"
                            >
                                <template #marker="{ item, index }">
                                    <Icon :name="item.icon" :class="`summary-bar-chart__icon-stroke--${index + 1}`" class="summary-bar-chart__icon-stroke size-3.5 shrink-0" />
                                </template>
                            </PieChart>
                        </Widget>
                    </div>
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
                            <p class="sr-only" aria-live="polite">{{ dictionaryChartAccessibleLabel }}</p>
                            <HorizontalLollipopChart
                                :items="dictionaryChartItems"
                                :metric="summaryChartMetric"
                                :accessible-label="dictionaryChartAccessibleLabel"
                                :show-marker="false"
                            >
                                <template #endpoint="{ item }">
                                    <span class="text-lg">{{ item.emoji }}</span>
                                </template>
                            </HorizontalLollipopChart>
                        </Widget>
                    </div>
                    <div class="starting-style-transition w-full min-h-61 min-[1100px]:w-1/2 px-3">
                        <Widget
                            :title="__('How old are you?')"
                            title-tag="h2"
                            class="h-full"
                            icon="number"
                            icon-class="hidden @xs/widget:block size-4 text-gray-500"
                        >
                            <HorizontalBarChart
                                :items="chartData.age"
                                :metric="summaryChartMetric"
                                :accessible-label="__('How old are you?: Min–Max 4–44, Average 26.2. Age distribution: 0–9: 20%, 10–19: 15%, 20–29: 30%, 30–39: 20%, 40–49: 20%')"
                                metric-position="end"
                                :show-marker="false"
                                class="pt-3 ps-4"
                            >
                                <template #summary>
                                    <div class="flex gap-2.5 pb-5 -ms-1">
                                        <div class="inline-flex items-center gap-2 px-2 py-1 rounded-md border border-gray-200 dark:border-gray-700">
                                            <span class="text-xs font-semibold st-text-trim-cap tabular-nums">4–44</span>
                                            <span class="text-[0.75rem] text-gray-500 dark:text-gray-300">{{ __('Min–Max') }}</span>
                                        </div>
                                        <div class="inline-flex items-center gap-2 px-2 py-1 rounded-md border border-gray-200 dark:border-gray-700">
                                            <span class="text-xs font-semibold st-text-trim-cap tabular-nums">26.2</span>
                                            <span class="text-[0.75rem] text-gray-500 dark:text-gray-300">{{ __('Average') }}</span>
                                        </div>
                                    </div>
                                </template>
                                <template #bar="{ width }">
                                    <div :style="{ width }" class="summary-bar-chart__fill h-2.5 rounded-full bg-chart-1" />
                                </template>
                            </HorizontalBarChart>
                        </Widget>
                    </div>
                    <div class="starting-style-transition w-full min-h-61 min-[1100px]:w-1/2 px-3">
                        <Widget
                            :title="__('How would you rate the hotel?')"
                            title-tag="h2"
                            class="h-full"
                            icon="star"
                            icon-class="hidden @xs/widget:block size-4 text-gray-500"
                        >
                            <HorizontalBarChart
                                :items="chartData.hotelRatings"
                                :metric="summaryChartMetric"
                                :accessible-label="__('How would you rate the hotel?: 3/5 average. Rating distribution: 5 stars 20%, 4 stars 15%, 3 stars 30%, 2 stars 20%, 1 star 20%')"
                                metric-position="end"
                                marker-position="after-label"
                                class="pt-3 ps-4"
                            >
                                <template #summary>
                                    <div class="pb-5">
                                        <div class="inline-flex items-center gap-2 px-2 py-1 -ms-1 rounded-md border border-gray-200 dark:border-gray-700">
                                            <Icon v-for="star in 5" :key="star" :name="star <= 3 ? 'star-filled' : 'star'" class="size-3.5 shrink-0 text-gray-950 dark:text-gray-300" />
                                            <span class="text-[0.75rem] text-gray-500 dark:text-gray-300">3/5 Average</span>
                                        </div>
                                    </div>
                                </template>
                                <template #marker="{ item }">
                                    <Icon name="star-filled" :class="item.markerClass" class="size-3.5 shrink-0 -ms-0.75" />
                                </template>
                                <template #bar="{ item, width }">
                                    <div :class="item.barClass" :style="{ width }" class="summary-bar-chart__fill h-2.5 rounded-full" />
                                </template>
                            </HorizontalBarChart>
                        </Widget>
                    </div>
                    <div class="starting-style-transition w-full min-h-61 min-[1100px]:w-1/2 px-3">
                        <Widget
                            :title="__('How would you rate the restaurant?')"
                            title-tag="h2"
                            class="h-full"
                            icon="star"
                            icon-class="hidden @xs/widget:block size-4 text-gray-500"
                        >
                            <VerticalBarChart
                                :items="chartData.restaurantRatings"
                                :metric="summaryChartMetric"
                                :accessible-label="__('How would you rate the restaurant?: 7/10 average. Rating distribution: 10 stars 18%, 9 stars 16%, 8 stars 14%, 7 stars 12%, 6 stars 10%, 5 stars 8%, 4 stars 7%, 3 stars 6%, 2 stars 5%, 1 star 4%')"
                            >
                                <template #summary>
                                    <div class="pb-5">
                                        <div class="inline-flex items-center gap-2 px-2 py-1 -ms-1 rounded-md border border-gray-200 dark:border-gray-700">
                                            <Icon v-for="star in 10" :key="star" :name="star <= 7 ? 'star-filled' : 'star'" class="size-3.5 shrink-0 text-gray-950 dark:text-gray-300" />
                                            <span class="text-[0.75rem] text-gray-500 dark:text-gray-300">7/10 Average</span>
                                        </div>
                                    </div>
                                </template>
                            </VerticalBarChart>
                        </Widget>
                    </div>
                    <div class="starting-style-transition w-full min-h-61 min-[1100px]:w-1/2 px-3">
                        <Widget
                            :title="__('Sign up to our newsletter')"
                            title-tag="h2"
                            class="h-full"
                            icon="fieldtype-toggle"
                            icon-class="hidden @xs/widget:block size-4 text-gray-500"
                        >
                            <HorizontalBarChart
                                :items="chartData.toggle"
                                :metric="summaryChartMetric"
                                :accessible-label="__('Sign up to our newsletter: 20% of users checked the toggle.')"
                                metric-position="end"
                                :show-label="false"
                                class="pt-3 ps-4"
                            >
                                <template #summary>
                                    <div class="pb-5">
                                        <div class="inline-flex items-center gap-2 px-2 -ms-1 py-1.25 rounded-md border border-gray-200 dark:border-gray-700">
                                            <span class="text-md font-semibold st-text-trim-cap tabular-nums text-gray-950 dark:text-gray-50">
                                                {{ summaryChartMetric === 'count' ? 50 : '20%' }}
                                            </span>
                                            <span class="text-[0.75rem] text-gray-500 dark:text-gray-300">{{ __('of users checked the toggle') }}</span>
                                        </div>
                                    </div>
                                </template>
                                <template #marker>
                                    <Switch :model-value="true" tabindex="-1" class="pointer-events-none data-[state=checked]:border-gray-950! data-[state=checked]:bg-gray-950!" />
                                </template>
                                <template #bar="{ width }">
                                    <div class="flex h-2.5 w-full overflow-hidden rounded-full bg-[hsl(from_var(--color-chart-1-legend)_h_s_l/0.15)]">
                                        <div :style="{ width }" class="summary-bar-chart__fill h-full shrink-0 rounded-full bg-chart-1-legend" />
                                    </div>
                                </template>
                            </HorizontalBarChart>
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
