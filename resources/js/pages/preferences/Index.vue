<script setup>
import { Link } from '@inertiajs/vue3';
import Head from '@/pages/layout/Head.vue';
import { Header, CardPanel, Icon, Badge, DocsCallout } from '@ui';

defineProps([
    'defaultPreferences',
    'defaultPreferencesUrl',
    'roles',
    'userPreferences',
    'userPreferencesUrl',
]);
</script>

<template>
    <div class="max-w-5xl max-w-wrapper mx-auto">
        <Head :title="__('Preferences')" />

        <Header :title="__('Preferences')" icon="preferences" />

        <section class="relative flex flex-col gap-8">
            <div class="absolute top-0 start-8 border-s w-px border-gray-300 dark:border-gray-700 h-[calc(100%-1.25rem)] border-dashed"></div>
            <CardPanel class="mb-0!" :heading="__('Global Preferences')" :subheading="__('Applied to all users by default.')">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2 sm:gap-3">
                        <Icon name="globals" />
                        <Link :href="defaultPreferencesUrl">{{ __('Site Defaults') }}</Link>
                    </div>

                    <Badge v-if="Object.keys(defaultPreferences).length" color="green">{{ __('Modified') }}</Badge>
                </div>
            </CardPanel>

            <CardPanel class="mb-0!" v-if="roles.length" :heading="__('Role Preferences')" :subheading="__('Applied to users in specific roles.')">
                <div v-for="role in roles" :key="role.handle" class="flex items-center justify-between">
                    <div class="flex items-center gap-2 sm:gap-3">
                        <Icon name="permissions" />
                        <Link :href="role.editUrl">{{ role.title }}</Link>
                    </div>
                    <Badge v-if="Object.keys(role.preferences).length" color="green">{{ __('Modified') }}</Badge>
                </div>
            </CardPanel>

            <CardPanel class="mb-0!" :heading="__('User Preferences')" :subheading="__('Applied to your account.')">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2 sm:gap-3">
                        <Icon name="avatar" />
                        <Link :href="userPreferencesUrl">{{ __('My Preferences') }}</Link>
                    </div>

                    <Badge v-if="Object.keys(userPreferences).length" color="green">{{ __('Modified') }}</Badge>
                </div>
            </CardPanel>
        </section>

        <DocsCallout :topic="__('Preferences')" url="preferences" />
    </div>
</template>
