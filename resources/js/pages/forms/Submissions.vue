<script setup>
import { ref, computed, watch, useId } from 'vue';
import axios from 'axios';
import Head from '@/pages/layout/Head.vue';
import { Header, Dropdown, DropdownMenu, DropdownItem, Button, Modal, RadioGroup, Radio, CommandPaletteItem, ToggleGroup, ToggleItem, Widget } from '@ui';
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
const rankingChartCaptionId = useId();

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
                            icon="fieldtype-radio"
                            icon-class="size-4 text-gray-500 hidden @xs/widget:block"
                        >
                            <figure class="grid gap-4 grid-cols-[0.6fr_1fr] p-6">
                                <div
                                    data-pie-chart
                                    class="w-36 aspect-square rounded-full"
                                    style="
                                        /* These slices would be dynamic based on the data */
                                        --1: 45%;
                                        --2: 30%;
                                        --3: 15%;
                                        --4: 10%;

                                        --end1: var(--1);
                                        --end2: calc(var(--1) + var(--2));
                                        --end3: calc(var(--1) + var(--2) + var(--3));
                                        --end4: 100%;

                                        background-image: conic-gradient(
                                            var(--color-indigo-500) 0% var(--end1),
                                            var(--color-gray-800) var(--end1) var(--end2),
                                            var(--color-lime-500) var(--end2) var(--end3),
                                            var(--color-indigo-200) var(--end3) var(--end4)
                                        );
                                    "
                                    role="img"
                                    :aria-labelledby="pieLegendId"
                                />
                                <figcaption :id="pieLegendId" class="pt-4">
                                    <ul class="text-xs text-gray-700 dark:text-gray-50 grid gap-2.5">
                                        <li class="flex items-center gap-2">
                                            <span class="font-medium">45%</span>
                                            <div class="size-2.5 shrink-0 rounded-full bg-indigo-500" />
                                            <span>Before you Go Go</span>
                                        </li>
                                        <li class="flex items-center gap-2">
                                            <span class="font-medium">30%</span>
                                            <div class="size-2.5 shrink-0 rounded-full bg-gray-800" />
                                            <span>Bring me Back to Life</span>
                                        </li>
                                        <li class="flex items-center gap-2">
                                            <span class="font-medium">15%</span>
                                            <div class="size-2.5 shrink-0 rounded-full bg-lime-500" />
                                            <span>When September Ends</span>
                                        </li>
                                        <li class="flex items-center gap-2">
                                            <span class="font-medium">10%</span>
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
                            :title="__('Rank your favourite seasons')"
                            title-tag="h2"
                            icon="rank"
                            icon-class="size-4 text-gray-500 hidden @xs/widget:block"
                        >
                            <figure class="p-6" :aria-labelledby="rankingChartCaptionId">
                                <ol class="m-0 flex list-none flex-col gap-5 p-0">
                                    <li class="grid grid-cols-[1.25rem_1rem_minmax(3.5rem,auto)_1fr_auto] items-center gap-x-3 gap-y-2">
                                        <span class="text-sm font-medium tabular-nums text-gray-500 dark:text-gray-400" aria-hidden="true">1</span>
                                        <span class="size-3.5 shrink-0 rounded-md bg-indigo-500" />
                                        <span class="text-sm text-gray-900 dark:text-gray-100">Summer</span>
                                        <div class="relative flex min-h-3 min-w-0 items-center">
                                            <div class="h-px w-[45%] bg-gray-300 dark:bg-gray-600" />
                                            <div class="size-3 -ms-1.5 shrink-0 rounded-full bg-indigo-500" />
                                        </div>
                                        <span class="min-w-9 text-end text-sm font-medium tabular-nums text-indigo-500">45%</span>
                                    </li>
                                    <li class="grid grid-cols-[1.25rem_1rem_minmax(3.5rem,auto)_1fr_auto] items-center gap-x-3 gap-y-2">
                                        <span class="text-sm font-medium tabular-nums text-gray-500 dark:text-gray-400" aria-hidden="true">2</span>
                                        <span class="size-3.5 shrink-0 rounded-md bg-gray-800" />
                                        <span class="text-sm text-gray-900 dark:text-gray-100">Autumn</span>
                                        <div class="relative flex min-h-3 min-w-0 items-center">
                                            <div class="h-px w-[35%] bg-gray-300 dark:bg-gray-600" />
                                            <div class="size-3 -ms-1.5 shrink-0 rounded-full bg-gray-800" />
                                        </div>
                                        <span class="min-w-9 text-end text-sm font-medium tabular-nums text-gray-800">35%</span>
                                    </li>
                                    <li class="grid grid-cols-[1.25rem_1rem_minmax(3.5rem,auto)_1fr_auto] items-center gap-x-3 gap-y-2">
                                        <span class="text-sm font-medium tabular-nums text-gray-500 dark:text-gray-400" aria-hidden="true">3</span>
                                        <span class="size-3.5 shrink-0 rounded-md bg-lime-500" />
                                        <span class="text-sm text-gray-900 dark:text-gray-100">Spring</span>
                                        <div class="relative flex min-h-3 min-w-0 items-center">
                                            <div class="h-px w-[15%] bg-gray-300 dark:bg-gray-600" />
                                            <div class="size-3 -ms-1.5 shrink-0 rounded-full bg-lime-500" />
                                        </div>
                                        <span class="min-w-9 text-end text-sm font-medium tabular-nums text-lime-500">15%</span>
                                    </li>
                                    <li class="grid grid-cols-[1.25rem_1rem_minmax(3.5rem,auto)_1fr_auto] items-center gap-x-3 gap-y-2">
                                        <span class="text-sm font-medium tabular-nums text-gray-500 dark:text-gray-400" aria-hidden="true">4</span>
                                        <span class="size-3.5 shrink-0 rounded-md bg-indigo-400" />
                                        <span class="text-sm text-gray-900 dark:text-gray-100">Winter</span>
                                        <div class="relative flex min-h-3 min-w-0 items-center">
                                            <div class="h-px w-[10%] bg-gray-300 dark:bg-gray-600" />
                                            <div class="size-3 -ms-1.5 shrink-0 rounded-full bg-indigo-400" />
                                        </div>
                                        <span class="min-w-9 text-end text-sm font-medium tabular-nums text-indigo-400">10%</span>
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
