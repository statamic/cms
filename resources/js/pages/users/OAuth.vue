<script setup>
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import Head from '@/pages/layout/Head.vue';
import { Header, Button, Listing } from '@ui';
import { toast } from '@api';
import { requireElevatedSession } from '@/components/elevated-sessions';

defineProps(['providers']);

const columns = [
    { label: __('Provider'), field: 'label' },
    { label: '', field: 'actions' },
];

function connect(provider) {
    requireElevatedSession()
        .then(() => (window.location = provider.connectUrl))
        .catch(() => toast.error(__('statamic::messages.elevated_session_required')));
}

function disconnect(provider) {
    requireElevatedSession()
        .then(() => performDisconnect(provider))
        .catch(() => toast.error(__('statamic::messages.elevated_session_required')));
}

function performDisconnect(provider) {
    axios.delete(provider.disconnectUrl).then(() => {
        toast.success(__('statamic::messages.oauth_disconnected', { provider: provider.label }));
        router.reload();
    });
}
</script>

<template>
    <Head :title="__('Sign-in Providers')" />

    <div class="max-w-5xl 3xl:max-w-6xl mx-auto" data-max-width-wrapper>
        <Header :title="__('Sign-in Providers')" icon="sign-in" />

        <Listing
            :items="providers"
            :columns
            :allow-search="false"
            :allow-customizing-columns="false"
        >
            <template #cell-label="{ row }">
                <div class="flex items-center gap-2">
                    <span v-if="row.icon" class="flex size-4 items-center [&_svg]:size-4" v-html="row.icon" />
                    <span>{{ row.label }}</span>
                </div>
            </template>

            <template #cell-actions="{ row }">
                <div class="text-right">
                    <Button
                        v-if="row.connected"
                        size="xs"
                        :text="__('Disconnect')"
                        @click="disconnect(row)"
                    />
                    <Button
                        v-else
                        size="xs"
                        :text="__('Connect')"
                        @click="connect(row)"
                    />
                </div>
            </template>
        </Listing>
    </div>
</template>
