<script setup>
import { ref, computed, watch, useId, nextTick } from 'vue';
import axios from 'axios';
import Head from '@/pages/layout/Head.vue';
import { Header, Dropdown, DropdownMenu, DropdownItem, Button, Modal, RadioGroup, Radio, CommandPaletteItem, ToggleGroup, ToggleItem, Widget, Pagination, Icon, Switch } from '@ui';
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
const starRatingChart2Page2CaptionId = useId();
const toggleBarChart1CaptionId = useId();

const starRatingChart2Page = ref(1);
const starRatingChart2PerPage = 5;
const starRatingChart2Total = 10;

const starRatingChart2Meta = computed(() => {
    const from = (starRatingChart2Page.value - 1) * starRatingChart2PerPage + 1;
    const to = Math.min(starRatingChart2Page.value * starRatingChart2PerPage, starRatingChart2Total);

    return {
        current_page: starRatingChart2Page.value,
        last_page: Math.ceil(starRatingChart2Total / starRatingChart2PerPage),
        per_page: starRatingChart2PerPage,
        total: starRatingChart2Total,
        from,
        to,
    };
});

const starRatingChart2AccessibleLabel = computed(() => {
    if (starRatingChart2Page.value === 1) {
        return __('How would you rate the restaurant?: 7/10 average. Rating distribution: 10 stars 18%, 9 stars 16%, 8 stars 14%, 7 stars 12%, 6 stars 10%');
    }

    return __('How would you rate the restaurant?: 7/10 average. Rating distribution: 5 stars 8%, 4 stars 7%, 3 stars 6%, 2 stars 5%, 1 star 4%');
});

const dictionaryChart1Page1Data = [
    { rank: 1, label: 'Japan', flag: '🇯🇵', percent: 40 },
    { rank: 2, label: 'Italy', flag: '🇮🇹', percent: 35 },
    { rank: 3, label: 'USA', flag: '🇺🇸', percent: 10 },
    { rank: 4, label: 'UK', flag: '🇬🇧', percent: 8 },
    { rank: 5, label: 'France', flag: '🇫🇷', percent: 3 },
];

const dictionaryChart1Page2Data = [
    { rank: 6, label: 'Germany', flag: '🇩🇪', percent: 2 },
    { rank: 7, label: 'Spain', flag: '🇪🇸', percent: 1 },
    { rank: 8, label: 'Portugal', flag: '🇵🇹', percent: 1 },
];

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

const dictionaryChartAccessibleLabel = computed(() => {
    if (dictionaryChartPage.value === 1) {
        return __('Japan 40%, Italy 35%, USA 10%, UK 8%, France 3%');
    }

    return __('Germany 2%, Spain 1%, Portugal 1%');
});

const yesNoChart1Data = [
    { percent: 55, label: 'I\'ll get my coat', icon: 'checkmark-circle-filled', chartColor: 1 },
    { percent: 45, label: 'Coffee might be better', icon: 'delete-circle-filled', chartColor: 2 },
];

const yesNoChart1AccessibleLabel = computed(() =>
    yesNoChart1Data
        .map((option) => `${option.label} ${option.percent}%`)
        .join(', '),
);

const yesNoChart1Type = ref('bar');

const imageChoicePieChart1Data = [
    { percent: 55, badge: 'A', label: 'Actually', image: 'https://picsum.photos/id/159/320/320' },
    { percent: 45, badge: 'B', label: 'Nope', image: 'https://picsum.photos/id/485/320/320' },
];

const imageChoicePieChart1AccessibleLabel = computed(() =>
    imageChoicePieChart1Data
        .map((option) => `${option.label} (${option.badge}) ${option.percent}%`)
        .join(', '),
);

const imageChoiceChart1Type = ref('bar');

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

/*
 * Chart reveal animations (see charts.css: pie-chart-reveal, bar-chart-reveal).
 *
 * Toggles the chart-reveal class briefly so the animation can replay when a
 * widget’s chart type changes — pie sweep or bar grow — not on summary enter
 * or pagination.
 */
const CHART_REVEAL_MS = 1100;

function createChartReveal() {
    const isRevealing = ref(false);
    let timeoutId;

    function trigger() {
        clearTimeout(timeoutId);
        isRevealing.value = false;

        nextTick(() => {
            isRevealing.value = true;
            timeoutId = setTimeout(() => {
                isRevealing.value = false;
            }, CHART_REVEAL_MS);
        });
    }

    return { isRevealing, trigger };
}

const yesNoChartReveal = createChartReveal();
const imageChoiceChartReveal = createChartReveal();

watch(yesNoChart1Type, (type, previousType) => {
    if (previousType !== undefined) {
        yesNoChartReveal.trigger();
    }
});

watch(imageChoiceChart1Type, (type, previousType) => {
    if (previousType !== undefined) {
        imageChoiceChartReveal.trigger();
    }
});
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
                <div data-submission-summary class="@container/widgets widgets flex flex-wrap mt-6 pb-8 gap-y-6 -mx-2 sm:-mx-3">
                    <div class="starting-style-transition w-full min-h-61 @2xl:w-1/2 @4xl:w-1/2 @7xl:w-1/3 px-3">
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
                                    <span class="pie-chart__label | pie-chart__label--1" aria-hidden="true">45%</span>
                                    <span class="pie-chart__label | pie-chart__label--2" aria-hidden="true">30%</span>
                                    <span class="pie-chart__label | pie-chart__label--3" aria-hidden="true">15%</span>
                                    <span class="pie-chart__label | pie-chart__label--4" aria-hidden="true">10%</span>
                                </div>
                                <!-- Pie Chart 1 Legend -->
                                <figcaption :id="pieChart1LegendId" class="pie-chart-legend">
                                    <ol class="pie-chart-legend__list">
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
                                    </ol>
                                </figcaption>
                            </figure>
                        </Widget>
                    </div>
                    <!-- Example of a Multiple Choice field type (Pie Chart) with Pagination. The idea here is when there are _more_ than four response options, we can paginate the chart. In such a case, the fourth option shows as "Other", and the second widget shows the "other" segment broken down into the remaining response options.

                    We should dynamically generate the ids to be unique here, so that everything remains accessible.-->
                    <div class="starting-style-transition w-full min-h-61 @2xl:w-1/2 @4xl:w-1/2 @7xl:w-1/3 px-3">
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
                    <div class="starting-style-transition w-full min-h-61 @2xl:w-1/2 @4xl:w-1/2 @7xl:w-1/3 px-3">
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
                                        <span class="text-xs text-end font-medium tabular-nums text-gray-700 dark:text-gray-400">1</span>
                                        <span class="size-2.5 rounded-xs bg-chart-1" />
                                        <span class="max-w-25 truncate me-2 text-xs text-gray-900 dark:text-gray-100">Summer</span>
                                        <div class="flex items-center gap-1">
                                            <div class="h-px w-[55%] bg-gray-200 dark:bg-gray-600" />
                                            <div class="size-2 rounded-full bg-chart-1" />
                                            <span class="min-w-8.5 text-end text-[0.75rem] font-medium tabular-nums text-gray-700 dark:text-gray-400">55%</span>
                                        </div>
                                    </li>
                                    <li class="contents">
                                        <span class="text-xs text-end font-medium tabular-nums text-gray-700 dark:text-gray-400">2</span>
                                        <span class="size-2.5 rounded-xs bg-chart-2" />
                                        <span class="max-w-25 truncate me-2 text-xs text-gray-900 dark:text-gray-100">Autumn</span>
                                        <div class="flex items-center gap-1">
                                            <div class="h-px w-[25%] bg-gray-200 dark:bg-gray-600" />
                                            <div class="size-2 rounded-full bg-chart-2" />
                                            <span class="min-w-8.5 text-end text-[0.75rem] font-medium tabular-nums text-gray-700 dark:text-gray-400">25%</span>
                                        </div>
                                    </li>
                                    <li class="contents">
                                        <span class="text-xs text-end font-medium tabular-nums text-gray-700 dark:text-gray-400">3</span>
                                        <span class="size-2.5 rounded-xs bg-chart-3" />
                                        <span class="max-w-25 truncate me-2 text-xs text-gray-900 dark:text-gray-100">Spring</span>
                                        <div class="flex items-center gap-1">
                                            <div class="h-px w-[15%] bg-gray-200 dark:bg-gray-600" />
                                            <div class="size-2 rounded-full bg-chart-3" />
                                            <span class="min-w-8.5 text-end text-[0.75rem] font-medium tabular-nums text-gray-700 dark:text-gray-400">15%</span>
                                        </div>
                                    </li>
                                    <li class="contents">
                                        <span class="text-xs text-end font-medium tabular-nums text-gray-700 dark:text-gray-400">4</span>
                                        <span class="size-2.5 rounded-xs bg-chart-4-legend" />
                                        <span class="max-w-25 truncate me-2 text-xs text-gray-900 dark:text-gray-100">Winter</span>
                                        <div class="flex items-center gap-1">
                                            <div class="h-px w-[10%] bg-gray-200 dark:bg-gray-600" />
                                            <div class="size-2 rounded-full bg-chart-4-legend" />
                                            <span class="min-w-8.5 text-end text-[0.75rem] font-medium tabular-nums text-gray-700 dark:text-gray-400">10%</span>
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
                    <div class="starting-style-transition w-full min-h-61 @2xl:w-1/2 @4xl:w-1/2 @7xl:w-1/3 px-3">
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
                                        <span class="text-xs text-end font-medium tabular-nums text-gray-700 dark:text-gray-400">55%</span>
                                        <span class="size-2.5 rounded-xs bg-chart-1" />
                                        <span class="max-w-25 truncate me-2 text-xs text-gray-900 dark:text-gray-100">Yep</span>
                                        <div class="h-2.5 w-[55%] rounded-full bg-chart-1" />
                                    </li>
                                    <li class="contents">
                                        <span class="text-xs text-end font-medium tabular-nums text-gray-700 dark:text-gray-400">35%</span>
                                        <span class="size-2.5 rounded-xs bg-chart-2" />
                                        <span class="max-w-25 truncate me-2 text-xs text-gray-900 dark:text-gray-100">Nope</span>
                                        <div class="h-2.5 w-[35%] rounded-full bg-chart-2" />
                                    </li>
                                    <li class="contents">
                                        <span class="text-xs text-end font-medium tabular-nums text-gray-700 dark:text-gray-400">10%</span>
                                        <span class="size-2.5 rounded-xs bg-chart-3" />
                                        <span class="max-w-25 truncate me-2 text-xs text-gray-900 dark:text-gray-100">Maybe</span>
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
                    <div class="starting-style-transition w-full min-h-61 @2xl:w-1/2 @4xl:w-1/2 @7xl:w-1/3 px-3">
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
                                        <span class="text-md font-semibold st-text-trim-cap tabular-nums text-green-600 dark:text-green-400">8.1</span> <span class="text-[0.75rem] text-gray-500 dark:text-gray-400">Average</span>
                                    </div>
                                </div>
                                <ol
                                    class="vertical-bar-chart"
                                    :style="{ '--max-value': verticalBarChart1MaxValue }"
                                    aria-hidden="true"
                                >
                                    <li
                                        v-for="bar in verticalBarChart1Data"
                                        :key="bar.label"
                                        class="vertical-bar-chart__bar"
                                        :style="{ '--value': bar.percent }"
                                    >
                                        <div class="vertical-bar-chart__plot">
                                            <span class="vertical-bar-chart__value">{{ bar.percent }}%</span>
                                            <div class="vertical-bar-chart__fill" />
                                        </div>
                                        <span class="vertical-bar-chart__scale-label">{{ bar.label }}</span>
                                    </li>
                                </ol>
                                <figcaption :id="verticalBarChart1CaptionId" class="sr-only">
                                    {{ __('Average 8.1. Recommendation score distribution: 0: 2%, 1: 1%, 2: 3%, 3: 4%, 4: 6%, 5: 8%, 6: 12%, 7: 20%, 8: 35%, 9: 48%, 10: 61%') }}
                                </figcaption>
                            </figure>
                        </Widget>
                    </div>
                    <!-- Example of an Image Choice field type with a chart type chooser. We should dynamically generate the ids to be unique here, so that everything remains accessible. -->
                    <div class="starting-style-transition w-full min-h-61 @2xl:w-1/2 @4xl:w-1/2 @7xl:w-1/3 px-3">
                        <Widget
                            :title="__('What is your spirit animal?')"
                            title-tag="h2"
                            class="h-full"
                            icon="image-select"
                            icon-class="hidden @xs/widget:block size-4 text-gray-500"
                        >
                            <template #actions>
                                <ToggleGroup v-model="imageChoiceChart1Type" size="sm">
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
                                :class="{ 'chart-reveal': imageChoiceChartReveal.isRevealing }"
                                :aria-labelledby="imageChoiceBarChart1CaptionId"
                            >
                                <ol class="grid grid-cols-[auto_2.5rem_auto_max-content_1fr] items-center list-none m-0 gap-x-2.25 gap-y-2.5 p-0 pt-4" aria-hidden="true">
                                    <li
                                        v-for="(option, index) in imageChoicePieChart1Data"
                                        :key="option.badge"
                                        class="contents"
                                    >
                                        <span class="text-xs text-end font-medium tabular-nums text-gray-700 dark:text-gray-400">{{ option.percent }}%</span>
                                        <img class="size-10 shrink-0 object-cover rounded-full" :src="option.image" alt="" />
                                        <span class="flex size-6 shrink-0 items-center justify-center rounded-md border border-gray-300 bg-white text-xs font-bold text-gray-800 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200">{{ option.badge }}</span>
                                        <span class="max-w-25 truncate me-2 text-xs text-gray-900 dark:text-gray-100">{{ option.label }}</span>
                                        <div
                                            class="summary-bar-chart__fill h-2.5 rounded-full"
                                            :class="index === 1 ? 'bg-chart-2' : 'bg-chart-1'"
                                            :style="{ width: `${option.percent}%` }"
                                        />
                                    </li>
                                </ol>
                                <figcaption :id="imageChoiceBarChart1CaptionId" class="sr-only">
                                    {{ __('What is your spirit animal?: Actually 55%, Nope 45%') }}
                                </figcaption>
                            </figure>
                            <figure v-else class="image-pie-chart-figure" :class="{ 'chart-reveal': imageChoiceChartReveal.isRevealing }">
                                <div
                                    class="image-pie-chart"
                                    :style="{
                                        '--1': imageChoicePieChart1Data[0].percent,
                                        '--2': imageChoicePieChart1Data[1].percent,
                                    }"
                                    role="img"
                                    :aria-labelledby="imageChoicePieChart1LegendId"
                                >
                                    <div class="image-pie-chart__disc" aria-hidden="true">
                                        <div
                                            v-for="(option, index) in imageChoicePieChart1Data"
                                            :key="option.badge"
                                            class="image-pie-chart__slice"
                                            :class="`image-pie-chart__slice--${index + 1}`"
                                            :style="{ '--image': `url('${option.image}')` }"
                                        />
                                    </div>
                                    <!-- aria-hidden because the labels are already in the figure caption -->
                                    <span
                                        v-for="(option, index) in imageChoicePieChart1Data"
                                        :key="`label-${option.badge}`"
                                        class="image-pie-chart__label"
                                        :class="`image-pie-chart__label--${index + 1}`"
                                        aria-hidden="true"
                                    >{{ option.percent }}%</span>
                                </div>
                                <figcaption :id="imageChoicePieChart1LegendId" class="image-pie-chart-legend">
                                    <p class="sr-only">{{ imageChoicePieChart1AccessibleLabel }}</p>
                                    <ol class="grid grid-cols-[auto_2.5rem_auto_1fr] items-center justify-items-start list-none m-0 gap-x-2.25 gap-y-2.5 p-0 pt-3" aria-hidden="true">
                                        <template v-for="option in imageChoicePieChart1Data" :key="option.badge">
                                            <span class="text-xs text-end font-medium tabular-nums text-gray-700 dark:text-gray-400">{{ option.percent }}%</span>
                                            <img class="size-10 shrink-0 object-cover rounded-full" :src="option.image" alt="" />
                                            <span class="flex size-6 shrink-0 items-center justify-center rounded-md border border-gray-300 bg-white text-xs font-bold text-gray-800 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200">{{ option.badge }}</span>
                                            <span class="max-w-25 truncate me-2 text-xs text-gray-900 dark:text-gray-100">{{ option.label }}</span>
                                        </template>
                                    </ol>
                                </figcaption>
                            </figure>
                        </Widget>
                    </div>
                    <!-- Example of a Checkboxes field type (Horizontal Bar Chart with checkbox icons). We should dynamically generate the ids to be unique here, so that everything remains accessible. -->
                    <div class="starting-style-transition w-full min-h-61 @2xl:w-1/2 @4xl:w-1/2 @7xl:w-1/3 px-3">
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
                                        <span class="text-xs text-end font-medium tabular-nums text-gray-700 dark:text-gray-400">55%</span>
                                        <Icon name="checkbox-filled" class="size-3.5 shrink-0 text-chart-1-legend" />
                                        <span class="max-w-25 truncate me-2 text-xs text-gray-900 dark:text-gray-100">Radiohead</span>
                                        <div class="h-2.5 w-[55%] rounded-full bg-chart-1" />
                                    </li>
                                    <li class="contents">
                                        <span class="text-xs text-end font-medium tabular-nums text-gray-700 dark:text-gray-400">35%</span>
                                        <Icon name="checkbox-filled" class="size-3.5 shrink-0 text-chart-2-legend" />
                                        <span class="max-w-25 truncate me-2 text-xs text-gray-900 dark:text-gray-100">Death Cab for Cutie</span>
                                        <div class="h-2.5 w-[35%] rounded-full bg-chart-2" />
                                    </li>
                                    <li class="contents">
                                        <span class="text-xs text-end font-medium tabular-nums text-gray-700 dark:text-gray-400">5%</span>
                                        <Icon name="checkbox-filled" class="size-3.5 shrink-0 text-chart-3-legend" />
                                        <span class="max-w-25 truncate me-2 text-xs text-gray-900 dark:text-gray-100">Olivia Rodrigo</span>
                                        <div class="h-2.5 w-[5%] rounded-full bg-chart-3" />
                                    </li>
                                    <li class="contents">
                                        <span class="text-xs text-end font-medium tabular-nums text-gray-700 dark:text-gray-400">5%</span>
                                        <Icon name="checkbox-filled" class="size-3.5 shrink-0 text-chart-4-legend" />
                                        <span class="max-w-25 truncate me-2 text-xs text-gray-900 dark:text-gray-100">The Cure</span>
                                        <div class="h-2.5 w-[5%] rounded-full bg-chart-4-legend" />
                                    </li>
                                </ol>
                                <figcaption :id="checkboxesBarChart1CaptionId" class="sr-only">
                                    {{ __('Who have you seen live?: Radiohead 55%, Death Cab for Cutie 35%, Olivia Rodrigo 5%, The Cure 5%') }}
                                </figcaption>
                            </figure>
                        </Widget>
                    </div>
                    <!-- Example of a yes/no field type with a chart type chooser. We should dynamically generate the ids to be unique here, so that everything remains accessible. -->
                    <div class="starting-style-transition w-full min-h-61 @2xl:w-1/2 @4xl:w-1/2 @7xl:w-1/3 px-3">
                        <Widget
                            :title="__('Do you fancy a pint?')"
                            title-tag="h2"
                            class="h-full"
                            icon="checkmark-circle"
                            icon-class="hidden @xs/widget:block size-4 text-gray-500"
                        >
                            <template #actions>
                                <ToggleGroup v-model="yesNoChart1Type" size="sm">
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
                                :class="{ 'chart-reveal': yesNoChartReveal.isRevealing }"
                                :aria-labelledby="yesNoBarChart1CaptionId"
                            >
                                <ol class="grid grid-cols-[auto_auto_max-content_1fr] items-center list-none m-0 gap-x-2.25 gap-y-2.75 p-0 pt-4" aria-hidden="true">
                                    <li
                                        v-for="option in yesNoChart1Data"
                                        :key="option.label"
                                        class="contents"
                                    >
                                        <span class="text-xs text-end font-medium tabular-nums text-gray-700 dark:text-gray-400">{{ option.percent }}%</span>
                                        <Icon
                                            :name="option.icon"
                                            class="size-3.5 shrink-0"
                                            :class="option.chartColor === 2 ? 'text-chart-2-legend' : 'text-chart-1-legend'"
                                        />
                                        <span class="max-w-25 truncate me-2 text-xs text-gray-900 dark:text-gray-100">{{ option.label }}</span>
                                        <div
                                            class="summary-bar-chart__fill h-2.5 rounded-full"
                                            :class="option.chartColor === 2 ? 'bg-chart-2' : 'bg-chart-1'"
                                            :style="{ width: `${option.percent}%` }"
                                        />
                                    </li>
                                </ol>
                                <figcaption :id="yesNoBarChart1CaptionId" class="sr-only">
                                    {{ __('Do you fancy a pint?: :summary', { summary: yesNoChart1AccessibleLabel }) }}
                                </figcaption>
                            </figure>
                            <figure v-else class="pie-chart-figure" :class="{ 'chart-reveal': yesNoChartReveal.isRevealing }">
                                <div
                                    class="pie-chart"
                                    :style="{
                                        '--1': yesNoChart1Data[0].percent,
                                        '--2': yesNoChart1Data[1].percent,
                                        '--3': 0,
                                        '--4': 0,
                                    }"
                                    role="img"
                                    :aria-labelledby="yesNoPieChart1LegendId"
                                >
                                    <div class="pie-chart__disc" aria-hidden="true" />
                                    <span class="pie-chart__label | pie-chart__label--1" aria-hidden="true">{{ yesNoChart1Data[0].percent }}%</span>
                                    <span class="pie-chart__label | pie-chart__label--2" aria-hidden="true">{{ yesNoChart1Data[1].percent }}%</span>
                                </div>
                                <figcaption :id="yesNoPieChart1LegendId" class="pie-chart-legend">
                                    <p class="sr-only">{{ __('Do you fancy a pint?: :summary', { summary: yesNoChart1AccessibleLabel }) }}</p>
                                    <ul class="pie-chart-legend__list" aria-hidden="true">
                                        <li
                                            v-for="option in yesNoChart1Data"
                                            :key="option.label"
                                            class="pie-chart-legend__item"
                                        >
                                            <span class="pie-chart-legend__value">{{ option.percent }}%</span>
                                            <Icon
                                                :name="option.icon"
                                                class="size-3.5 shrink-0"
                                                :class="option.chartColor === 2 ? 'text-chart-2-legend' : 'text-chart-1-legend'"
                                            />
                                            <span>{{ option.label }}</span>
                                        </li>
                                    </ul>
                                </figcaption>
                            </figure>
                        </Widget>
                    </div>
                    <!-- Example of a Dictionary field type (Horizontal Lollipop Chart with icons) with Pagination. We should dynamically generate the ids to be unique here, so that everything remains accessible. -->
                    <div class="starting-style-transition w-full min-h-61 @2xl:w-1/2 @4xl:w-1/2 @7xl:w-1/3 px-3">
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
                            <figure v-if="dictionaryChartPage === 1" class="grid p-6" :aria-labelledby="dictionaryChart1CaptionId">
                                <ol class="grid grid-cols-[auto_max-content_1fr] items-center list-none m-0 gap-x-2.25 [&:not(:has(>_:nth-child(5)))]:pt-3" aria-hidden="true">
                                    <li
                                        v-for="item in dictionaryChart1Page1Data"
                                        :key="item.label"
                                        class="contents"
                                    >
                                        <span class="text-xs font-medium tabular-nums text-gray-700 dark:text-gray-400">{{ item.rank }}</span>
                                        <span class="max-w-25 truncate me-2 text-xs text-gray-900 dark:text-gray-100">{{ item.label }}</span>
                                        <div class="flex items-center gap-1">
                                            <div class="h-px bg-gray-200 dark:bg-gray-600" :style="{ width: `${item.percent}%` }" />
                                            <div class="text-lg">{{ item.flag }}</div>
                                            <span class="min-w-8.5 text-end text-[0.75rem] font-medium tabular-nums text-gray-700 dark:text-gray-400">{{ item.percent }}%</span>
                                        </div>
                                    </li>
                                </ol>
                                <figcaption :id="dictionaryChart1CaptionId" class="sr-only">
                                    {{ __('What’s your favourite country?: Japan 40%, Italy 35%, USA 10%, UK 8%, France 3%') }}
                                </figcaption>
                            </figure>
                            <figure v-else-if="dictionaryChartPage === 2" class="grid p-6" :aria-labelledby="dictionaryChart1Page2CaptionId">
                                <ol class="grid grid-cols-[auto_max-content_1fr] items-center list-none m-0 gap-x-2.25 [&:not(:has(>_:nth-child(5)))]:pt-3" aria-hidden="true">
                                    <li
                                        v-for="item in dictionaryChart1Page2Data"
                                        :key="item.label"
                                        class="contents"
                                    >
                                        <span class="text-xs font-medium tabular-nums text-gray-700 dark:text-gray-400">{{ item.rank }}</span>
                                        <span class="max-w-25 truncate me-2 text-xs text-gray-900 dark:text-gray-100">{{ item.label }}</span>
                                        <div class="flex items-center gap-1">
                                            <div class="h-px bg-gray-200 dark:bg-gray-600" :style="{ width: `${item.percent}%` }" />
                                            <div class="text-lg">{{ item.flag }}</div>
                                            <span class="min-w-8.5 text-end text-[0.75rem] font-medium tabular-nums text-gray-700 dark:text-gray-400">{{ item.percent }}%</span>
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
                    <div class="starting-style-transition w-full min-h-61 @2xl:w-1/2 @4xl:w-1/2 @7xl:w-1/3 px-3">
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
                                        <span class="text-[0.75rem] text-gray-500 dark:text-gray-400">{{ __('Min–Max') }}</span>
                                    </div>
                                    <div class="inline-flex items-center gap-2 px-2 py-1 rounded-md border border-gray-200 dark:border-gray-700">
                                        <span class="text-xs font-semibold st-text-trim-cap tabular-nums">26.2</span>
                                        <span class="text-[0.75rem] text-gray-500 dark:text-gray-400">{{ __('Average') }}</span>
                                    </div>
                                </div>
                                <ol class="grid grid-cols-[auto_1fr_max-content] items-center list-none m-0 gap-x-3 gap-y-1 p-0 [&:not(:has(>_:nth-child(5)))]:pt-4 text-end" aria-hidden="true">
                                    <li class="contents">
                                        <span class="text-xs tabular-nums text-gray-700 dark:text-gray-400">0–9</span>
                                        <div class="h-2.5 w-[20%] rounded-full bg-chart-1" />
                                        <span class="text-xs font-medium tabular-nums text-gray-700 dark:text-gray-400">20%</span>
                                    </li>
                                    <li class="contents">
                                        <span class="text-xs tabular-nums text-gray-700 dark:text-gray-400">10–19</span>
                                        <div class="h-2.5 w-[15%] rounded-full bg-chart-1" />
                                        <span class="text-xs font-medium tabular-nums text-gray-700 dark:text-gray-400">15%</span>
                                    </li>
                                    <li class="contents">
                                        <span class="text-xs tabular-nums text-gray-700 dark:text-gray-400">20–29</span>
                                        <div class="h-2.5 w-[30%] rounded-full bg-chart-1" />
                                        <span class="text-xs font-medium tabular-nums text-gray-700 dark:text-gray-400">30%</span>
                                    </li>
                                    <li class="contents">
                                        <span class="text-xs tabular-nums text-gray-700 dark:text-gray-400">30–39</span>
                                        <div class="h-2.5 w-[20%] rounded-full bg-chart-1" />
                                        <span class="text-xs font-medium tabular-nums text-gray-700 dark:text-gray-400">20%</span>
                                    </li>
                                    <li class="contents">
                                        <span class="text-xs tabular-nums text-gray-700 dark:text-gray-400">40–49</span>
                                        <div class="h-2.5 w-[10%] rounded-full bg-chart-1" />
                                        <span class="text-xs font-medium tabular-nums text-gray-700 dark:text-gray-400">20%</span>
                                    </li>
                                </ol>
                                <figcaption :id="numberBarChart1CaptionId" class="sr-only">
                                    {{ __('How old are you?: Min–Max 4–44, Average 26.2. Age distribution: 0–9: 20%, 10–19: 15%, 20–29: 30%, 30–39: 20%, 40–49: 20%') }}
                                </figcaption>
                            </figure>
                        </Widget>
                    </div>
                    <!-- Example of a Star Rating field type (Horizontal Star Rating Bar Chart with info). We should dynamically generate the ids to be unique here, so that everything remains accessible. -->
                    <div class="starting-style-transition w-full min-h-61 @2xl:w-1/2 @4xl:w-1/2 @7xl:w-1/3 px-3">
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
                                        <Icon name="star-filled" class="size-3.5 shrink-0 text-gray-950" />
                                        <Icon name="star-filled" class="size-3.5 shrink-0 text-gray-950" />
                                        <Icon name="star-filled" class="size-3.5 shrink-0 text-gray-950" />
                                        <Icon name="star" class="size-3.5 shrink-0 text-gray-950" />
                                        <Icon name="star" class="size-3.5 shrink-0 text-gray-950" />
                                        <span class="text-[0.75rem] text-gray-500 dark:text-gray-400">3/5 Average</span>
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
                                        <span class="text-xs font-medium tabular-nums text-gray-700 dark:text-gray-400">20%</span>
                                    </li>
                                    <li class="contents">
                                        <span class="text-xs tabular-nums text-gray-700 dark:text-gray-400">4</span>
                                        <Icon name="star-filled" class="size-3.5 shrink-0 -ms-0.75 text-[oklch(from_var(--color-chart-1-legend)_calc(l*0.9)_c_h)]" />
                                        <div class="h-2.5 w-[15%] rounded-full bg-[oklch(from_var(--color-chart-1-legend)_calc(l*0.9)_c_h)]" />
                                        <span class="text-xs font-medium tabular-nums text-gray-700 dark:text-gray-400">15%</span>
                                    </li>
                                    <li class="contents">
                                        <span class="text-xs tabular-nums text-gray-700 dark:text-gray-400">3</span>
                                        <Icon name="star-filled" class="size-3.5 shrink-0 -ms-0.75 text-chart-1-legend" />
                                        <div class="h-2.5 w-[30%] rounded-full bg-chart-1-legend" />
                                        <span class="text-xs font-medium tabular-nums text-gray-700 dark:text-gray-400">30%</span>
                                    </li>
                                    <li class="contents">
                                        <span class="text-xs tabular-nums text-gray-700 dark:text-gray-400">2</span>
                                        <Icon name="star-filled" class="size-3.5 shrink-0 -ms-0.75 text-[oklch(from_var(--color-chart-1-legend)_calc(l*1.2)_c_h)]" />
                                        <div class="h-2.5 w-[20%] rounded-full bg-[oklch(from_var(--color-chart-1-legend)_calc(l*1.2)_c_h)]" />
                                        <span class="text-xs font-medium tabular-nums text-gray-700 dark:text-gray-400">20%</span>
                                    </li>
                                    <li class="contents">
                                        <span class="text-xs tabular-nums text-gray-700 dark:text-gray-400">1</span>
                                        <Icon name="star-filled" class="size-3.5 shrink-0 -ms-0.75 text-[oklch(from_var(--color-chart-1-legend)_calc(l*1.4)_c_h)]" />
                                        <div class="h-2.5 w-[10%] rounded-full bg-[oklch(from_var(--color-chart-1-legend)_calc(l*1.4)_c_h)]" />
                                        <span class="text-xs font-medium tabular-nums text-gray-700 dark:text-gray-400">20%</span>
                                    </li>
                                </ol>
                                <figcaption :id="starRatingChart1CaptionId" class="sr-only">
                                    {{ __('How would you rate the hotel?: 3/5 average. Rating distribution: 5 stars 20%, 4 stars 15%, 3 stars 30%, 2 stars 20%, 1 star 20%') }}
                                </figcaption>
                            </figure>
                        </Widget>
                    </div>
                    <!-- Example of a Star Rating field type with pagination (Horizontal Star Rating Bar Chart with info). We should dynamically generate the ids to be unique here, so that everything remains accessible. -->
                    <div class="starting-style-transition w-full min-h-61 @2xl:w-1/2 @4xl:w-1/2 @7xl:w-1/3 px-3">
                        <Widget
                            :title="__('How would you rate the restaurant?')"
                            title-tag="h2"
                            class="h-full"
                            icon="star"
                            icon-class="hidden @xs/widget:block size-4 text-gray-500"
                        >
                            <template #actions>
                                <Pagination
                                    :resource-meta="starRatingChart2Meta"
                                    :show-totals="false"
                                    :show-page-links="false"
                                    :show-per-page-selector="false"
                                    :scroll-to-top="false"
                                    @page-selected="starRatingChart2Page = $event"
                                />
                            </template>
                            <p class="sr-only" aria-live="polite">{{ starRatingChart2AccessibleLabel }}</p>
                            <figure v-if="starRatingChart2Page === 1" class="grid p-6 pt-3 ps-4" :aria-labelledby="starRatingChart2CaptionId">
                                <div aria-hidden="true" class="pb-5">
                                    <div class="inline-flex items-center gap-2 px-2 py-1 -ms-1 rounded-md border border-gray-200 dark:border-gray-700">
                                        <Icon name="star-filled" class="size-3.5 shrink-0 text-gray-950" />
                                        <Icon name="star-filled" class="size-3.5 shrink-0 text-gray-950" />
                                        <Icon name="star-filled" class="size-3.5 shrink-0 text-gray-950" />
                                        <Icon name="star-filled" class="size-3.5 shrink-0 text-gray-950" />
                                        <Icon name="star-filled" class="size-3.5 shrink-0 text-gray-950" />
                                        <Icon name="star-filled" class="size-3.5 shrink-0 text-gray-950" />
                                        <Icon name="star-filled" class="size-3.5 shrink-0 text-gray-950" />
                                        <Icon name="star" class="size-3.5 shrink-0 text-gray-950" />
                                        <Icon name="star" class="size-3.5 shrink-0 text-gray-950" />
                                        <Icon name="star" class="size-3.5 shrink-0 text-gray-950" />
                                        <span class="text-[0.75rem] text-gray-500 dark:text-gray-400">7/10 Average</span>
                                    </div>
                                </div>
                                <!--
                                    Star row colors use oklch relative lightness from --color-chart-1-legend:
                                    - Midpoint star uses chart-1-legend (3/5 in the hotel demo; 4/10 in the restaurant demo).
                                    - 5 stars or fewer: darker stars use l*0.9, l*0.8 (−0.1 per step); lighter stars use l*1.2, l*1.4 (+0.2 per step).
                                    - 6 stars or more: darker stars use −0.05 per step (e.g. l*0.9, l*0.85, l*0.8, l*0.75, l*0.7); lighter stars use l*1.1, l*1.2, l*1.4 (+0.1, then +0.2).
                                 -->
                                <ol class="grid grid-cols-[auto_auto_1fr_max-content] items-center list-none m-0 gap-x-3 gap-y-1 p-0 text-end" aria-hidden="true">
                                    <li class="contents">
                                        <span class="text-xs tabular-nums text-gray-700 dark:text-gray-400">10</span>
                                        <Icon name="star-filled" class="size-3.5 shrink-0 -ms-0.75 text-[oklch(from_var(--color-chart-1-legend)_calc(l*0.7)_c_h)]" />
                                        <div class="h-2.5 w-[18%] rounded-full bg-[oklch(from_var(--color-chart-1-legend)_calc(l*0.7)_c_h)]" />
                                        <span class="text-xs font-medium tabular-nums text-gray-700 dark:text-gray-400">18%</span>
                                    </li>
                                    <li class="contents">
                                        <span class="text-xs tabular-nums text-gray-700 dark:text-gray-400">9</span>
                                        <Icon name="star-filled" class="size-3.5 shrink-0 -ms-0.75 text-[oklch(from_var(--color-chart-1-legend)_calc(l*0.75)_c_h)]" />
                                        <div class="h-2.5 w-[16%] rounded-full bg-[oklch(from_var(--color-chart-1-legend)_calc(l*0.75)_c_h)]" />
                                        <span class="text-xs font-medium tabular-nums text-gray-700 dark:text-gray-400">16%</span>
                                    </li>
                                    <li class="contents">
                                        <span class="text-xs tabular-nums text-gray-700 dark:text-gray-400">8</span>
                                        <Icon name="star-filled" class="size-3.5 shrink-0 -ms-0.75 text-[oklch(from_var(--color-chart-1-legend)_calc(l*0.8)_c_h)]" />
                                        <div class="h-2.5 w-[14%] rounded-full bg-[oklch(from_var(--color-chart-1-legend)_calc(l*0.8)_c_h)]" />
                                        <span class="text-xs font-medium tabular-nums text-gray-700 dark:text-gray-400">14%</span>
                                    </li>
                                    <li class="contents">
                                        <span class="text-xs tabular-nums text-gray-700 dark:text-gray-400">7</span>
                                        <Icon name="star-filled" class="size-3.5 shrink-0 -ms-0.75 text-[oklch(from_var(--color-chart-1-legend)_calc(l*0.85)_c_h)]" />
                                        <div class="h-2.5 w-[12%] rounded-full bg-[oklch(from_var(--color-chart-1-legend)_calc(l*0.85)_c_h)]" />
                                        <span class="text-xs font-medium tabular-nums text-gray-700 dark:text-gray-400">12%</span>
                                    </li>
                                    <li class="contents">
                                        <span class="text-xs tabular-nums text-gray-700 dark:text-gray-400">6</span>
                                        <Icon name="star-filled" class="size-3.5 shrink-0 -ms-0.75 text-[oklch(from_var(--color-chart-1-legend)_calc(l*0.9)_c_h)]" />
                                        <div class="h-2.5 w-[10%] rounded-full bg-[oklch(from_var(--color-chart-1-legend)_calc(l*0.9)_c_h)]" />
                                        <span class="text-xs font-medium tabular-nums text-gray-700 dark:text-gray-400">10%</span>
                                    </li>
                                </ol>
                                <figcaption :id="starRatingChart2CaptionId" class="sr-only">
                                    {{ __('How would you rate the restaurant?: 7/10 average. Rating distribution: 10 stars 18%, 9 stars 16%, 8 stars 14%, 7 stars 12%, 6 stars 10%') }}
                                </figcaption>
                            </figure>
                            <figure v-else-if="starRatingChart2Page === 2" class="grid p-6 pt-3 ps-4" :aria-labelledby="starRatingChart2Page2CaptionId">
                                <div aria-hidden="true" class="pb-5">
                                    <div class="inline-flex items-center gap-2 px-2 py-1 -ms-1 rounded-md border border-gray-200 dark:border-gray-700">
                                        <Icon name="star-filled" class="size-3.5 shrink-0 text-gray-950" />
                                        <Icon name="star-filled" class="size-3.5 shrink-0 text-gray-950" />
                                        <Icon name="star-filled" class="size-3.5 shrink-0 text-gray-950" />
                                        <Icon name="star-filled" class="size-3.5 shrink-0 text-gray-950" />
                                        <Icon name="star-filled" class="size-3.5 shrink-0 text-gray-950" />
                                        <Icon name="star-filled" class="size-3.5 shrink-0 text-gray-950" />
                                        <Icon name="star-filled" class="size-3.5 shrink-0 text-gray-950" />
                                        <Icon name="star" class="size-3.5 shrink-0 text-gray-950" />
                                        <Icon name="star" class="size-3.5 shrink-0 text-gray-950" />
                                        <Icon name="star" class="size-3.5 shrink-0 text-gray-950" />
                                        <span class="text-[0.75rem] text-gray-500 dark:text-gray-400">7/10 Average</span>
                                    </div>
                                </div>
                                <ol class="grid grid-cols-[auto_auto_1fr_max-content] items-center list-none m-0 gap-x-3 gap-y-1 p-0 text-end" aria-hidden="true">
                                    <li class="contents">
                                        <span class="text-xs tabular-nums text-gray-700 dark:text-gray-400">5</span>
                                        <Icon name="star-filled" class="size-3.5 shrink-0 -ms-0.75 text-[oklch(from_var(--color-chart-1-legend)_calc(l*0.9)_c_h)]" />
                                        <div class="h-2.5 w-[8%] rounded-full bg-[oklch(from_var(--color-chart-1-legend)_calc(l*0.9)_c_h)]" />
                                        <span class="text-xs font-medium tabular-nums text-gray-700 dark:text-gray-400">8%</span>
                                    </li>
                                    <li class="contents">
                                        <span class="text-xs tabular-nums text-gray-700 dark:text-gray-400">4</span>
                                        <Icon name="star-filled" class="size-3.5 shrink-0 -ms-0.75 text-chart-1-legend" />
                                        <div class="h-2.5 w-[7%] rounded-full bg-chart-1-legend" />
                                        <span class="text-xs font-medium tabular-nums text-gray-700 dark:text-gray-400">7%</span>
                                    </li>
                                    <li class="contents">
                                        <span class="text-xs tabular-nums text-gray-700 dark:text-gray-400">3</span>
                                        <Icon name="star-filled" class="size-3.5 shrink-0 -ms-0.75 text-[oklch(from_var(--color-chart-1-legend)_calc(l*1.1)_c_h)]" />
                                        <div class="h-2.5 w-[6%] rounded-full bg-[oklch(from_var(--color-chart-1-legend)_calc(l*1.1)_c_h)]" />
                                        <span class="text-xs font-medium tabular-nums text-gray-700 dark:text-gray-400">6%</span>
                                    </li>
                                    <li class="contents">
                                        <span class="text-xs tabular-nums text-gray-700 dark:text-gray-400">2</span>
                                        <Icon name="star-filled" class="size-3.5 shrink-0 -ms-0.75 text-[oklch(from_var(--color-chart-1-legend)_calc(l*1.2)_c_h)]" />
                                        <div class="h-2.5 w-[5%] rounded-full bg-[oklch(from_var(--color-chart-1-legend)_calc(l*1.2)_c_h)]" />
                                        <span class="text-xs font-medium tabular-nums text-gray-700 dark:text-gray-400">5%</span>
                                    </li>
                                    <li class="contents">
                                        <span class="text-xs tabular-nums text-gray-700 dark:text-gray-400">1</span>
                                        <Icon name="star-filled" class="size-3.5 shrink-0 -ms-0.75 text-[oklch(from_var(--color-chart-1-legend)_calc(l*1.4)_c_h)]" />
                                        <div class="h-2.5 w-[4%] rounded-full bg-[oklch(from_var(--color-chart-1-legend)_calc(l*1.4)_c_h)]" />
                                        <span class="text-xs font-medium tabular-nums text-gray-700 dark:text-gray-400">4%</span>
                                    </li>
                                </ol>
                                <figcaption :id="starRatingChart2Page2CaptionId" class="sr-only">
                                    {{ __('How would you rate the restaurant?: 7/10 average. Rating distribution: 5 stars 8%, 4 stars 7%, 3 stars 6%, 2 stars 5%, 1 star 4%') }}
                                </figcaption>
                            </figure>
                        </Widget>
                    </div>
                    <!-- Example of a Toggle field type (Single fillable bar chart). We should dynamically generate the ids to be unique here, so that everything remains accessible. -->
                    <div class="starting-style-transition w-full min-h-61 @2xl:w-1/2 @4xl:w-1/2 @7xl:w-1/3 px-3">
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
                                        <span class="text-md font-semibold st-text-trim-cap tabular-nums text-gray-950 dark:text-gray-100">20%</span> <span class="text-[0.75rem] text-gray-500 dark:text-gray-400">{{ __('of users checked the toggle') }}</span>
                                    </div>
                                </div>
                                <ol class="grid grid-cols-[auto_1fr_max-content] items-center list-none m-0 gap-x-3 gap-y-1 p-0 pt-2" aria-hidden="true">
                                    <li class="contents">
                                        <Switch :model-value="true" tabindex="-1" class="pointer-events-none data-[state=checked]:border-gray-950! data-[state=checked]:bg-gray-950!" />
                                        <div class="flex h-2.5 w-full overflow-hidden rounded-full bg-[hsl(from_var(--color-chart-1-legend)_h_s_l/0.15)]">
                                            <div class="h-full w-[20%] shrink-0 rounded-full bg-chart-1-legend" />
                                        </div>
                                        <span class="text-xs font-medium tabular-nums text-gray-700 dark:text-gray-400">20%</span>
                                    </li>
                                </ol>
                                <figcaption :id="toggleBarChart1CaptionId" class="sr-only">
                                    {{ __('Sign up to our newsletter: 20% of users checked the toggle.') }}
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
