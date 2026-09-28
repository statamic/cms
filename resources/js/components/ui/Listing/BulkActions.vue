<script setup>
import { Motion } from 'motion-v';
import { injectListingContext } from '../Listing/Listing.vue';
import { computed, ref, watch } from 'vue';
import { Button, ButtonGroup } from '@ui';
import BulkActions from '@/components/actions/BulkActions.vue';
import BulkActionsFloatingToolbar from './BulkActionsFloatingToolbar.vue';

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
        v-slot="{ actions }"
    >
        <BulkActionsFloatingToolbar
            :actions="actions"
            :visible="visible"
            :selections="selections"
            :clear-selections="clearSelections"
        />
    </BulkActions>
</template>
