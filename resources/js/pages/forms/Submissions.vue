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
const pieLegendId = useId();
const pieLegendPage2Id = useId();
const rankingChartCaptionId = useId();

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
                <div class="mt-6 widgets @container/widgets flex flex-wrap gap-y-6 -mx-2 sm:-mx-3">
                    <div class="px-3 starting-style-transition w-full @2xl:w-1/2 @4xl:w-1/2 @7xl:w-1/3">
                        <Widget
                            :title="__('Wake me up')"
                            title-tag="h2"
                            class="h-full"
                            icon="fieldtype-radio"
                            icon-class="size-4 text-gray-500 hidden @xs/widget:block"
                        >
                            <figure class="grid gap-4 grid-cols-[0.6fr_1fr] p-6">
                                <div
                                    class="pie-chart"
                                    style="--1: 45; --2: 30; --3: 15; --4: 10;"
                                    role="img"
                                    :aria-labelledby="pieLegendId"
                                >
                                    <div class="pie-chart__disc" aria-hidden="true" />
                                    <!-- aria-hidden because the labels are already in the figure caption -->
                                    <span class="pie-chart__label | pie-chart__label--1" aria-hidden="true">45%</span>
                                    <span class="pie-chart__label | pie-chart__label--2" aria-hidden="true">30%</span>
                                    <span class="pie-chart__label | pie-chart__label--3" aria-hidden="true">15%</span>
                                    <span class="pie-chart__label | pie-chart__label--4" aria-hidden="true">10%</span>
                                </div>
                                <figcaption :id="pieLegendId" class="pt-4">
                                    <ul class="text-xs text-gray-700 dark:text-gray-50 grid gap-2.5">
                                        <li class="flex items-center gap-2.25">
                                            <span class="font-medium text-[0.785rem]">45%</span>
                                            <div class="size-2.5 shrink-0 rounded-full bg-indigo-500" />
                                            <span>Before you Go Go</span>
                                        </li>
                                        <li class="flex items-center gap-2.25">
                                            <span class="font-medium text-[0.785rem]">30%</span>
                                            <div class="size-2.5 shrink-0 rounded-full bg-gray-800" />
                                            <span>Bring me Back to Life</span>
                                        </li>
                                        <li class="flex items-center gap-2.25">
                                            <span class="font-medium text-[0.785rem]">15%</span>
                                            <div class="size-2.5 shrink-0 rounded-full bg-lime-500" />
                                            <span>When September Ends</span>
                                        </li>
                                        <li class="flex items-center gap-2.25">
                                            <span class="font-medium text-[0.785rem]">10%</span>
                                            <div class="size-2.5 shrink-0 rounded-full bg-indigo-300" />
                                            <span>Never</span>
                                        </li>
                                    </ul>
                                </figcaption>
                            </figure>
                        </Widget>
                    </div>
                    <div class="px-3 starting-style-transition w-full @2xl:w-1/2 @4xl:w-1/2 @7xl:w-1/3">
                        <Widget
                            :title="__('I love to wake up...')"
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
                            <figure v-if="wakeMeUpChartPage === 1" class="grid gap-4 grid-cols-[0.6fr_1fr] p-6">
                                <div
                                    class="pie-chart"
                                    style="--1: 68; --2: 18; --3: 9; --4: 5;"
                                    role="img"
                                    :aria-labelledby="pieLegendId"
                                >
                                    <div class="pie-chart__disc" aria-hidden="true" />
                                    <!-- aria-hidden because the labels are already in the figure caption -->
                                    <span class="pie-chart__label | pie-chart__label--1" aria-hidden="true">68%</span>
                                    <span class="pie-chart__label | pie-chart__label--2" aria-hidden="true">18%</span>
                                    <span class="pie-chart__label | pie-chart__label--3" aria-hidden="true">9%</span>
                                    <span class="pie-chart__label | pie-chart__label--4" aria-hidden="true">5%</span>
                                </div>
                                <figcaption :id="pieLegendId" class="pt-4">
                                    <ul class="text-xs text-gray-700 dark:text-gray-50 grid gap-2.5">
                                        <li class="flex items-center gap-2.25">
                                            <span class="font-medium text-[0.785rem]">68%</span>
                                            <div class="size-2.5 shrink-0 rounded-full bg-indigo-500" />
                                            <span>Alarm, no mercy</span>
                                        </li>
                                        <li class="flex items-center gap-2.25">
                                            <span class="font-medium text-[0.785rem]">18%</span>
                                            <div class="size-2.5 shrink-0 rounded-full bg-gray-800" />
                                            <span>Hit snooze</span>
                                        </li>
                                        <li class="flex items-center gap-2.25">
                                            <span class="font-medium text-[0.785rem]">9%</span>
                                            <div class="size-2.5 shrink-0 rounded-full bg-lime-500" />
                                            <span>Woken by someone else</span>
                                        </li>
                                        <li class="flex items-center gap-2.25">
                                            <span class="font-medium text-[0.785rem]">5%</span>
                                            <div class="size-2.5 shrink-0 rounded-full bg-indigo-300" />
                                            <span>I wake up naturally</span>
                                        </li>
                                    </ul>
                                </figcaption>
                            </figure>
                            <figure v-else-if="wakeMeUpChartPage === 2" class="grid gap-4 grid-cols-[0.6fr_1fr] p-6">
                                <div
                                    class="pie-chart"
                                    style="--1: 68; --2: 18; --3: 9; --4: 5;"
                                    role="img"
                                    :aria-labelledby="pieLegendPage2Id"
                                >
                                    <div class="pie-chart__disc" aria-hidden="true" />
                                    <span class="pie-chart__label | pie-chart__label--1" aria-hidden="true">68%</span>
                                    <span class="pie-chart__label | pie-chart__label--2" aria-hidden="true">18%</span>
                                    <span class="pie-chart__label | pie-chart__label--3" aria-hidden="true">9%</span>
                                    <span class="pie-chart__label | pie-chart__label--4" aria-hidden="true">5%</span>
                                </div>
                                <figcaption :id="pieLegendPage2Id" class="pt-4">
                                    <ul class="text-xs text-gray-700 dark:text-gray-50 grid gap-2.5">
                                        <li class="flex items-center gap-2.25">
                                            <span class="font-medium text-[0.785rem]">45%</span>
                                            <div class="size-2.5 shrink-0 rounded-full bg-indigo-500" />
                                            <span>Bohemian Rhapsody</span>
                                        </li>
                                        <li class="flex items-center gap-2.25">
                                            <span class="font-medium text-[0.785rem]">30%</span>
                                            <div class="size-2.5 shrink-0 rounded-full bg-gray-800" />
                                            <span>Stairway to Heaven</span>
                                        </li>
                                        <li class="flex items-center gap-2.25">
                                            <span class="font-medium text-[0.785rem]">15%</span>
                                            <div class="size-2.5 shrink-0 rounded-full bg-lime-500" />
                                            <span>Hey Jude</span>
                                        </li>
                                        <li class="flex items-center gap-2.25">
                                            <span class="font-medium text-[0.785rem]">10%</span>
                                            <div class="size-2.5 shrink-0 rounded-full bg-indigo-300" />
                                            <span>Wonderwall</span>
                                        </li>
                                    </ul>
                                </figcaption>
                            </figure>
                        </Widget>
                    </div>
                    <div class="px-3 starting-style-transition w-full @2xl:w-1/2 @4xl:w-1/2 @7xl:w-1/3">
                        <Widget
                            :title="__('Rank your favourite seasons')"
                            title-tag="h2"
                            class="h-full"
                            icon="rank"
                            icon-class="size-4 text-gray-500 hidden @xs/widget:block"
                        >
                            <figure class="p-6 grid" :aria-labelledby="rankingChartCaptionId">
                                <ol class="m-0 list-none grid gap-2.5 p-0 pt-4">
                                    <li class="flex items-center gap-2.25">
                                        <span class="text-xs font-medium tabular-nums text-gray-700 dark:text-gray-400" aria-hidden="true">1</span>
                                        <span class="size-2.5 shrink-0 rounded-xs bg-indigo-500" />
                                        <span class="w-20 text-xs text-gray-900 dark:text-gray-100">Summer</span>
                                        <div class="relative flex flex-grow-1 items-center gap-1">
                                            <div class="h-px w-[55%] bg-gray-200 dark:bg-gray-600" />
                                            <div class="size-2 rounded-full bg-indigo-500" />
                                            <span class="min-w-8.5 text-end text-[0.785rem] font-medium tabular-nums text-gray-700 dark:text-gray-400">55%</span>
                                        </div>
                                    </li>
                                    <li class="flex items-center gap-2.25">
                                        <span class="text-xs font-medium tabular-nums text-gray-700 dark:text-gray-400" aria-hidden="true">2</span>
                                        <span class="size-2.5 shrink-0 rounded-xs bg-gray-800" />
                                        <span class="w-20 text-xs text-gray-900 dark:text-gray-100">Autumn</span>
                                        <div class="relative flex flex-grow-1 items-center gap-1">
                                            <div class="h-px w-[25%] bg-gray-200 dark:bg-gray-600" />
                                            <div class="size-2 rounded-full bg-gray-800" />
                                            <span class="min-w-8.5 text-end text-[0.785rem] font-medium tabular-nums text-gray-700 dark:text-gray-400">25%</span>
                                        </div>
                                    </li>
                                    <li class="flex items-center gap-2.25">
                                        <span class="text-xs font-medium tabular-nums text-gray-700 dark:text-gray-400" aria-hidden="true">3</span>
                                        <span class="size-2.5 shrink-0 rounded-xs bg-lime-500" />
                                        <span class="w-20 text-xs text-gray-900 dark:text-gray-100">Spring</span>
                                        <div class="relative flex flex-grow-1 items-center gap-1">
                                            <div class="h-px w-[15%] bg-gray-200 dark:bg-gray-600" />
                                            <div class="size-2 rounded-full bg-lime-500" />
                                            <span class="min-w-8.5 text-end text-[0.785rem] font-medium tabular-nums text-gray-700 dark:text-gray-400">15%</span>
                                        </div>
                                    </li>
                                    <li class="flex items-center gap-2.25">
                                        <span class="text-xs font-medium tabular-nums text-gray-700 dark:text-gray-400" aria-hidden="true">4</span>
                                        <span class="size-2.5 shrink-0 rounded-xs bg-indigo-300" />
                                        <span class="w-20 text-xs text-gray-900 dark:text-gray-100">Winter</span>
                                        <div class="relative flex flex-grow-1 items-center gap-1">
                                            <div class="h-px w-[10%] bg-gray-200 dark:bg-gray-600" />
                                            <div class="size-2 rounded-full bg-indigo-300" />
                                            <span class="min-w-8.5 text-end text-[0.785rem] font-medium tabular-nums text-gray-700 dark:text-gray-400">10%</span>
                                        </div>
                                    </li>
                                </ol>
                                <figcaption :id="rankingChartCaptionId" class="sr-only">
                                    {{ __('Ranked favourite seasons: Summer 45%, Autumn 35%, Spring 15%, Winter 10%') }}
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
