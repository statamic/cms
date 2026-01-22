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
    <div class="max-w-page mx-auto">
        <Head :title="__('CP Nav Preferences')" />

        <Header :title="__('CP Nav Preferences')" icon="preferences" />

        <section class="space-y-6">
            <CardPanel :heading="__('Global Preferences')">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2 sm:gap-3">
                        <Icon name="globals" />
                        <Link :href="defaultPreferencesUrl">{{ __('Default') }}</Link>
                    </div>

                    <Badge v-if="Object.keys(defaultPreferences).length" color="green">{{ __('Modified') }}</Badge>
                </div>
            </CardPanel>

            <CardPanel v-if="roles.length" :heading="__('Preferences by Role')">
                <div v-for="role in roles" :key="role.handle" class="flex items-center justify-between">
                    <div class="flex items-center gap-2 sm:gap-3">
                        <Icon name="permissions" />
                        <Link :href="role.editUrl">{{ role.title }}</Link>
                    </div>
                    <Badge v-if="Object.keys(role.preferences).length" color="green">{{ __('Modified') }}</Badge>
                </div>
            </CardPanel>

            <CardPanel :heading="__('User Preferences')">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2 sm:gap-3">
                        <Icon name="avatar" />
                        <Link :href="userPreferencesUrl">{{ __('My Nav') }}</Link>
                    </div>

                    <Badge v-if="Object.keys(userPreferences).length" color="green">{{ __('Modified') }}</Badge>
                </div>
            </CardPanel>
        </section>

        <DocsCallout :topic="__('Customizing the Control Panel Nav')" url="customizing-the-cp-nav" />
    </div>
</template>
