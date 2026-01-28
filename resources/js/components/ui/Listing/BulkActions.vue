<script setup>
import { Motion } from 'motion-v';
import { injectListingContext } from '../Listing/Listing.vue';
import { computed, ref, watch } from 'vue';
import { Button, ButtonGroup, Icon } from '@ui';
import BulkActions from '@/components/actions/BulkActions.vue';

const { actionUrl, actionContext, selections, refresh, clearSelections } = injectListingContext();
const busy = ref(false);
const hasSelections = computed(() => selections.value.length > 0);
const visible = ref(false);
let visibleTimeout = null;

watch(hasSelections, (value) => {
    clearTimeout(visibleTimeout);
    if (value) {
        visibleTimeout = setTimeout(() => visible.value = true, 300);
    } else {
        visible.value = false;
    }
});

watch(busy, (busy) => Statamic.$progress.loading('action', busy));

function actionStarted() {
    busy.value = true;
}

function actionCompleted(successful = null, response = {}) {
    busy.value = false;
    successful ? actionSuccess(response) : actionFailed(response);
}

function actionSuccess(response) {
    if (response.message !== false) {
        Statamic.$toast.success(response.message || __('Action completed'));
    }
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
        v-slot="{ actions, loading }"
    >
        <Motion
            v-if="visible"
            layout
            data-floating-toolbar
            class="sticky inset-x-0 bottom-1 sm:bottom-6 z-100 flex w-full max-w-[95vw] mx-auto justify-center "
            :initial="{ y: 100, opacity: 0 }"
            :animate="{ y: 0, opacity: 1 }"
            :transition="{ duration: 0.2, ease: 'easeInOut' }"
        >
            <div class="space-y-3 rounded-xl border border-gray-300/60 dark:border-gray-700 p-1 bg-gray-200/55 shadow-[0_1px_16px_-2px_rgba(63,63,71,0.2)] dark:bg-gray-800 dark:shadow-[0_10px_15px_rgba(0,0,0,.5)] dark:inset-shadow-2xs dark:inset-shadow-white/10">
            <ButtonGroup>
                <Button
                    class="text-blue-500!"
                    :disabled="loading"
                    :text="__n(`Deselect :count item|Deselect all :count items`, selections.length)"
                    @click="clearSelections"
                />
                <Button
                    v-for="action in actions"
                    :key="action.handle"
                    :text="__(action.title)"
                    :variant="action.dangerous ? 'danger' : 'default'"
                    @click="action.run"
                />
            </ButtonGroup>
            </div>
        </Motion>
    </BulkActions>
</template>
