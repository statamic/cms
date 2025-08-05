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
    successful ? actionSuccess(response) : actionFailed(response);
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
            class="fixed inset-x-0 bottom-6 z-100 flex w-full justify-center "
            :initial="{ y: 100, opacity: 0 }"
            :animate="{ y: 0, opacity: 1 }"
            :transition="{ duration: 0.2, ease: 'easeInOut' }"
        >
            <div class="[.nav-open_&]:translate-x-23 transition-transform duration-300 relative space-y-3 rounded-xl border border-gray-300/60 p-1 bg-gray-200/50 shadow-[0_1px_16px_-2px_rgba(63,63,71,0.2)] dark:border-none dark:bg-gray-800 dark:shadow-[0_10px_15px_rgba(0,0,0,.5)] dark:inset-shadow-2xs dark:inset-shadow-white/15">
            <ButtonGroup>
                <Button
                    class="text-blue-500!"
                    :text="__n(`Deselect :count item|Deselect all :count items`, selections.length)"
                    @click="clearSelections"
                />
                <Button v-for="action in actions" :key="action.handle" :text="__(action.title)" @click="action.run" />
            </ButtonGroup>
            </div>
        </Motion>
    </BulkActions>
</template>
