<script setup>
import { computed, ref } from 'vue';
import axios from 'axios';
import Head from '@/pages/layout/Head.vue';
import { Header, Dropdown, DropdownMenu, DropdownItem, Button, CommandPaletteItem, Modal, ModalClose, Radio, RadioGroup } from '@ui';
import ResourceDeleter from '@/components/ResourceDeleter.vue';
import FormSubmissionListing from '@/components/forms/SubmissionListing.vue';
import Layout from '@/pages/layout/Layout.vue';
import FormsLayout from './Layout.vue';

defineOptions({ layout: [Layout, FormsLayout] });

const props = defineProps([
    'form',
    'columns',
    'filters',
    'actionUrl',
    'generateFakeSubmissionUrl',
    'exporters',
    'redirectUrl',
]);

const deleter = ref(null);
const generatingFakeSubmission = ref(false);
const deletingFakeSubmissions = ref(false);
const submissionListing = ref(null);
const exportModalOpen = ref(false);
const exportingSubmissions = ref(false);
const exportFormat = ref('csv');
const exportScope = ref('all');

const exportFormats = [
    { value: 'csv', label: __('CSV') },
    { value: 'json', label: __('JSON') },
];

const exportScopeOptions = [
    {
        value: 'all',
        label: __('All Submissions'),
    },
    {
        value: 'filtered',
        label: __('Filtered Submissions'),
    },
];

const selectedExporter = computed(() => findExporterByFormat(exportFormat.value));
const hasFilteredScope = computed(() => {
    const parameters = getSubmissionListingParameters();

    return Boolean(parameters.search || parameters.filters);
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

function openExportModal() {
    const firstAvailableFormat = exportFormats.find(({ value }) => Boolean(findExporterByFormat(value)));

    exportFormat.value = firstAvailableFormat?.value ?? 'csv';
    exportScope.value = 'all';
    exportModalOpen.value = true;
}

function normalizedValue(value) {
    return String(value ?? '').toLowerCase();
}

function findExporterByFormat(format) {
    const normalizedFormat = normalizedValue(format);

    return props.exporters.find((exporter) => {
        const handle = normalizedValue(exporter.handle);
        return handle === normalizedFormat;
    }) || null;
}

function getSubmissionListingParameters() {
    const parameters = submissionListing.value?.getParameters?.() ?? {};

    return {
        sort: parameters.sort,
        order: parameters.order,
        search: parameters.search,
        filters: parameters.filters,
    };
}

function appendParamsToUrl(url, params) {
    const result = new URL(url, window.location.origin);

    Object.entries(params).forEach(([key, value]) => {
        if (value === null || value === undefined || value === '') {
            return;
        }

        result.searchParams.set(key, value);
    });

    return result.toString();
}

function exportSubmissions() {
    exportingSubmissions.value = true;

    const params = exportScope.value === 'filtered' ? getSubmissionListingParameters() : {};
    const exportUrl = appendParamsToUrl(selectedExporter.value.downloadUrl, params);

    window.open(exportUrl, '_blank', 'noopener,noreferrer');

    exportModalOpen.value = false;
    exportingSubmissions.value = false;
}
</script>

<template>
    <Head :title="[form.title, __('Forms')]" />

    <div class="max-w-5xl 3xl:max-w-6xl mx-auto" data-max-width-wrapper>
        <Header :title="form.title" icon="forms">
            <Dropdown v-if="form.canEdit || form.canDelete" placement="left-start" class="me-2">
                <DropdownMenu>
                    <DropdownItem v-if="form.canEdit" :text="__('Configure Form')" icon="cog" :href="form.editUrl" />
                    <DropdownItem
                        v-if="form.canConfigureFields"
                        :text="__('Edit Blueprint')"
                        icon="blueprint-edit"
                        :href="form.blueprintUrl"
                    />
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
                :text="__('Edit Blueprint')"
                icon="blueprint-edit"
                :url="form.blueprintUrl"
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
            :form="form.handle"
            :action-url="actionUrl"
            sort-column="datestamp"
            sort-direction="desc"
            :columns="columns"
            :filters="filters"
        />

        <Modal v-model:open="exportModalOpen" :title="__('Export Submissions')" blur class="max-w-xl!">
            <div class="space-y-6">
                <div class="space-y-2">
                    <p class="text-sm font-medium">{{ __('Format') }}</p>
                    <RadioGroup v-model="exportFormat" :name="`form-export-format-${form.handle}`">
                        <Radio
                            v-for="option in exportFormats"
                            :key="option.value"
                            :value="option.value"
                            :label="option.label"
                            :description="findExporterByFormat(option.value) ? null : __('No exporter configured for this format.')"
                            :disabled="!findExporterByFormat(option.value)"
                        />
                    </RadioGroup>
                </div>

                <div class="space-y-2">
                    <p class="text-sm font-medium">{{ __('Scope') }}</p>
                    <RadioGroup v-model="exportScope" :name="`form-export-scope-${form.handle}`">
                        <Radio
                            v-for="option in exportScopeOptions"
                            :key="option.value"
                            :value="option.value"
                            :label="option.label"
                            :description="option.value === 'filtered' && !hasFilteredScope
                                ? __('No active filters are set right now. This will export all submissions.')
                                : option.description"
                        />
                    </RadioGroup>
                </div>

            </div>
                <template #footer>
                    <div class="flex items-center justify-end gap-2 pt-3 pb-1">
                        <ModalClose asChild>
                            <Button variant="ghost" :text="__('Cancel')" />
                        </ModalClose>
                        <Button
                            variant="primary"
                            :text="__('Export')"
                            :loading="exportingSubmissions"
                            :disabled="!selectedExporter"
                            @click="exportSubmissions"
                        />
                    </div>
                </template>
        </Modal>
    </div>
</template>
