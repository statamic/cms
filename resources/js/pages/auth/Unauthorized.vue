<script setup>
import Head from '@/pages/layout/Head.vue';
import Outside from '@/pages/layout/Outside.vue';
import { AuthCard, Button } from '@ui';
import { Link } from '@inertiajs/vue3';

defineOptions({ layout: Outside });

defineProps(['isLoggedIn', 'loginUrl', 'logoutUrl']);
</script>

<template>
    <Head :title="__('Unauthorized')" />

    <AuthCard
        icon="key"
        :title="__('Unauthorized')"
        :description="__('You do not have permission to access this URL')"
    >
        <div class="flex justify-center">
            <Button
                :as="Link"
                variant="primary"
                :href="isLoggedIn ? logoutUrl : loginUrl"
                class="w-full"
                v-text="isLoggedIn ? __('Log out') : __('Log in')"
            />
        </div>
    </AuthCard>
</template>
