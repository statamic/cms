<script setup>
import { computed } from 'vue';
import Head from '@/pages/layout/Head.vue';
import { Header, Button, Badge, CommandPaletteItem, EmptyStateMenu, EmptyStateItem, DocsCallout, Icon, Listing, DropdownItem } from '@ui';
import FormStatusIndicator from '@/components/forms/FormStatusIndicator.vue';
import { Link, router } from '@inertiajs/vue3';

const props = defineProps([
    'forms',
    'initialColumns',
    'actionUrl',
    'canCreate',
    'createUrl',
    'configureEmailUrl',
]);

const isEmpty = computed(() => props.forms.length === 0);

const reloadPage = () => router.reload();
</script>

<template>
    <Head :title="__('Forms')" />

    <div class="max-w-page mx-auto">
        <template v-if="isEmpty">
            <header class="py-8 pt-16 text-center">
                <h1 class="text-[25px] font-medium antialiased flex justify-center items-center gap-2 sm:gap-3">
                    <Icon name="collections" class="size-5 text-gray-500" />
                    {{ __('Forms') }}
                </h1>
            </header>

            <EmptyStateMenu :heading="__('statamic::messages.form_configure_intro')">
                <EmptyStateItem
                    v-if="canCreate"
                    :href="createUrl"
                    icon="forms"
                    :heading="__('Create Form')"
                    :description="__('statamic::messages.form_create_description')"
                />
                <EmptyStateItem
                    :href="configureEmailUrl"
                    icon="mail-settings"
                    :heading="__('Configure Email')"
                    :description="__('statamic::messages.form_configure_email_description')"
                />
            </EmptyStateMenu>

            <DocsCallout :topic="__('Forms')" url="forms" />
        </template>

        <template v-else>
            <Header :title="__('Forms')" icon="forms">
                <CommandPaletteItem
                    v-if="canCreate"
                    category="Actions"
                    :text="__('Create Form')"
                    icon="forms"
                    :url="createUrl"
                    v-slot="{ text, url }"
                >
                    <Button :href="url" :text="text" variant="primary" />
                </CommandPaletteItem>
            </Header>

            <Listing :items="forms" :columns="initialColumns" :action-url="actionUrl" @refreshing="reloadPage">
                <template #cell-title="{ row: form }">
                    <div class="flex items-center gap-2">
                        <FormStatusIndicator :status="form.status" />
                        <Link :href="form.show_url">{{ __(form.title) }}</Link>
                    </div>
                </template>
                <template #cell-submissions="{ row: form, value: submissions }">
                    <Badge
                        v-if="form.can_view_submissions"
                        :href="form.submissions_url"
                        :append="String(submissions)"
                        :text="__('Results')"
                        color="white"
                        pill
                    />
                </template>
                <template #prepended-row-actions="{ row: form }">
                    <DropdownItem v-if="form.can_edit" :text="__('Configure')" :href="form.edit_url" icon="cog" />
                </template>
            </Listing>

            <DocsCallout :topic="__('Forms')" url="forms" />
        </template>
    </div>
</template>
