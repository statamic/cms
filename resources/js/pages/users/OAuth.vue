<script setup>
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import Head from '@/pages/layout/Head.vue';
import { Header, Button, Listing } from '@ui';
import { toast } from '@api';

defineProps(['providers']);

const columns = [
    { label: __('Provider'), field: 'label' },
    { label: '', field: 'actions' },
];

function disconnect(provider) {
    axios.delete(provider.unlinkUrl).then(() => {
        toast.success(__('statamic::messages.oauth_unlinked', { provider: provider.label }));
        router.reload();
    });
}
</script>

<template>
    <Head :title="__('OAuth')" />

    <div class="max-w-5xl 3xl:max-w-6xl mx-auto" data-max-width-wrapper>
        <Header :title="__('OAuth')" icon="sign-in" />

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
                        as="a"
                        size="xs"
                        :text="__('Connect')"
                        :href="row.connectUrl"
                    />
                </div>
            </template>
        </Listing>
    </div>
</template>
