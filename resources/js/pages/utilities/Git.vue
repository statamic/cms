<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import Head from '@/pages/layout/Head.vue';
import { Header, Button, CardPanel, Badge, Heading, DocsCallout } from '@ui';
import GitStatus from '@/components/GitStatus.vue';

const props = defineProps([
    'statuses',
    'commitUrl',
]);

const submitting = ref(false);

function commit() {
    submitting.value = true;
    router.post(props.commitUrl, {}, {
        onFinish: () => {
            submitting.value = false;
        },
    });
}
</script>

<template>
    <Head :title="[__('Git'), __('Utilities')]" />

    <form @submit.prevent="commit">
        <Header :title="__('Git')" icon="git">
            <Button
                type="submit"
                variant="primary"
                :text="__('Commit Changes')"
                :disabled="!statuses || submitting"
            />
        </Header>
    </form>

    <template v-if="statuses">
        <CardPanel
            v-for="status in statuses"
            :key="status.path"
            :heading="__('Repository')"
            :subheading="status.path"
        >
            <div class="space-y-4">
                <div class="flex flex-wrap gap-2">
                    <Badge :prepend="__('Affected files')" :text="status.totalCount" />
                    <Badge v-if="status.addedCount" :prepend="__('Added')" color="green" :text="status.addedCount" />
                    <Badge v-if="status.modifiedCount" :prepend="__('Modified')" color="yellow" :text="status.modifiedCount" />
                    <Badge v-if="status.deletedCount" :prepend="__('Deleted')" color="red" :text="status.deletedCount" />
                </div>

                <GitStatus :status="status.status" />
            </div>
        </CardPanel>
    </template>

    <CardPanel v-else :heading="__('Repository')">
        <Heading>{{ __('statamic::messages.git_nothing_to_commit') }}</Heading>
    </CardPanel>

    <DocsCallout :topic="__('the Git Integration')" url="git-integration" />
</template>
