<script setup>
import { Motion } from 'motion-v';
import { injectListingContext } from '@statamic/components/ui/Listing/Listing.vue';
import { computed, ref, watch } from 'vue';
import { Button, ButtonGroup } from '@statamic/ui';
import BulkActions from '@statamic/components/actions/BulkActions.vue';

const { actionUrl, actionContext, selections, refresh, clearSelections } = injectListingContext();
const busy = ref(false);
const hasSelections = computed(() => selections.value.length > 0);

watch(busy, (busy) => Statamic.$progress.loading('action', busy));

function actionStarted() {
    busy.value = true;
}

function actionCompleted(successful = null, response = {}) {
    busy.value = false;

    if (successful) {
        actionSuccess(response);
    } else {
        actionFailed(response);
    }
}

function actionSuccess(response) {
    Statamic.$toast.success(response.message || __('Action completed'));
    refresh();
    clearSelections();
}

function actionFailed(response) {
    Statamic.$toast.error(response.message || __('Action failed'));
}
</script>

<template>
    <BulkActions
        :url="actionUrl"
        :selections="selections"
        :context="actionContext"
        @started="actionStarted"
        @completed="actionCompleted"
        v-slot="{ actions }"
    >
        <Motion
            v-if="hasSelections"
            layout
            class="fixed inset-x-0 bottom-1 z-100 flex w-full justify-center"
            :initial="{ y: 100, opacity: 0 }"
            :animate="{ y: 0, opacity: 1 }"
            :transition="{ duration: 0.2, ease: 'easeInOut' }"
        >
            <ButtonGroup>
                <Button
                    variant="primary"
                    class="text-gray-400!"
                    :text="__n(`:count item selected|:count items selected`, selections.length)"
                />
                <Button
                    v-for="action in actions"
                    :key="action.handle"
                    variant="primary"
                    :text="__(action.title)"
                    @click="action.run"
                />
            </ButtonGroup>
        </Motion>
    </BulkActions>
</template>
