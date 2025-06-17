<script setup>
import { Dropdown, DropdownItem, DropdownLabel, DropdownMenu, DropdownSeparator } from '@statamic/ui';
import { injectListingContext } from '@statamic/components/ui/Listing/Listing.vue';
import ItemActions from '@statamic/components/actions/ItemActions.vue';
import { ref, watch } from 'vue';

const props = defineProps({
    row: {
        type: Object,
        required: true,
    },
});

const { actionsUrl, onActionSuccess, onActionFailure, refresh } = injectListingContext();
const busy = ref(false);

watch(busy, (busy) => Statamic.$progress.loading('action', busy));

function actionStarted() {
    busy.value = true;
}

function actionCompleted(successful = null, response = {}) {
    busy.value = false;

    if (successful) {
        const success = () => actionSuccess(response);
        onActionSuccess ? onActionSuccess({ response, success, refresh }) : success();
    } else {
        const failed = () => actionFailed(response);
        onActionFailure ? onActionFailure({ response, failed }) : failed();
    }
}

function actionSuccess(response) {
    Statamic.$toast.success(response.message || __('Action completed'));
    refresh();
}

function actionFailed(response) {
    Statamic.$toast.error(response.message || __('Action failed'));
}
</script>

<template>
    <ItemActions
        :url="actionsUrl"
        :actions="row.actions"
        :item="row.id"
        @started="actionStarted"
        @completed="actionCompleted"
        v-slot="{ actions }"
    >
        <Dropdown placement="left-start" class="me-3">
            <DropdownMenu>
                <DropdownLabel :text="__('Actions')" />
                <slot name="prepended-actions" :row="row" />
                <DropdownSeparator v-if="$slots['prepended-actions']" />
                <DropdownItem
                    v-for="action in actions"
                    :key="action.handle"
                    :text="__(action.title)"
                    :icon="action.icon"
                    :class="{ 'text-red-500': action.dangerous }"
                    @click="action.run"
                />
            </DropdownMenu>
        </Dropdown>
    </ItemActions>
</template>
