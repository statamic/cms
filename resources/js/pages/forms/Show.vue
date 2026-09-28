<script setup>
import { ref } from 'vue';
import Head from '@/pages/layout/Head.vue';
import { Header, Dropdown, DropdownMenu, DropdownItem, Button, CommandPaletteItem } from '@ui';
import ResourceDeleter from '@/components/ResourceDeleter.vue';
import FormSubmissionListing from '@/components/forms/SubmissionListing.vue';
import ExportSubmissionsModal from '@/components/forms/ExportSubmissionsModal.vue';

const props = defineProps([
    'form',
    'columns',
    'filters',
    'actionUrl',
    'exporters',
    'exportColumns',
    'redirectUrl',
]);

const deleter = ref(null);
const submissionListing = ref(null);
const exportModalOpen = ref(false);
const listingParameters = ref({});

function openExportModal() {
    listingParameters.value = submissionListing.value?.parameters ?? {};
    exportModalOpen.value = true;
}
</script>

<template>
    <div class="max-w-5xl 3xl:max-w-6xl mx-auto" data-max-width-wrapper>
        <Head :title="[__(form.title), __('Forms')]" />

        <Header :title="__(form.title)" icon="forms">
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

        <ExportSubmissionsModal
            v-if="exportModalOpen"
            :exporters
            :columns="exportColumns"
            :listing-parameters
            @close="exportModalOpen = false"
        />
    </div>
</template>
