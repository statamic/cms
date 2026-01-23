<script setup>
import { ref } from 'vue';
import Head from '@/pages/layout/Head.vue';
import { Header, Dropdown, DropdownMenu, DropdownItem, Button, CommandPaletteItem } from '@ui';
import ResourceDeleter from '@/components/ResourceDeleter.vue';
import FormSubmissionListing from '@/components/forms/SubmissionListing.vue';

const props = defineProps([
    'form',
    'columns',
    'filters',
    'actionUrl',
    'exporters',
    'redirectUrl',
]);

const deleter = ref(null);
</script>

<template>
    <div class="max-w-5xl max-w-wrapper mx-auto">
        <Head :title="[form.title, __('Forms')]" />

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

            <Dropdown v-if="exporters.length">
                <template #trigger>
                    <Button :text="__('Export Submissions')" />
                </template>
                <DropdownMenu>
                    <DropdownItem
                        v-for="exporter in exporters"
                        :key="exporter.downloadUrl"
                        :text="exporter.title"
                        :href="exporter.downloadUrl"
                        target="_blank"
                    />
                </DropdownMenu>
            </Dropdown>

            <CommandPaletteItem
                v-for="exporter in exporters"
                :key="exporter.downloadUrl"
                category="Actions"
                :text="[__('Export Submissions'), exporter.title]"
                icon="save"
                :url="exporter.downloadUrl"
                prioritize
            />
        </Header>

        <FormSubmissionListing
            :form="form.handle"
            :action-url="actionUrl"
            sort-column="datestamp"
            sort-direction="desc"
            :columns="columns"
                :filters="filters"
        />
    </div>
</template>
