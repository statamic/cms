<script setup>
import { computed } from 'vue';
import Head from '@/pages/layout/Head.vue';
import { Header, Button, CommandPaletteItem, EmptyStateMenu, EmptyStateItem, DocsCallout, Icon, Listing, DropdownItem } from '@ui';
import useStatamicPageProps from '@/composables/page-props.js';
import { Link, router } from '@inertiajs/vue3';

const props = defineProps([
    'forms',
    'initialColumns',
    'actionUrl',
    'canCreate',
    'createUrl',
    'configureEmailUrl',
]);

const { isPro } = useStatamicPageProps();
const isEmpty = computed(() => props.forms.length === 0);

const reloadPage = () => router.reload();
</script>

<template>
    <Head :title="__('Forms')" />

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
                v-if="isPro && canCreate"
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
                <Link :href="form.show_url">{{ form.title }}</Link>
            </template>
            <template #prepended-row-actions="{ row: form }">
                <DropdownItem v-if="form.can_edit" :text="__('Configure')" :href="form.edit_url" icon="cog" />
                <DropdownItem
                    v-if="form.can_edit_blueprint"
                    icon="blueprint-edit"
                    :text="__('Edit Blueprint')"
                    :href="form.blueprint_url"
                />
            </template>
        </Listing>

        <DocsCallout :topic="__('Forms')" url="forms" />
    </template>
</template>
