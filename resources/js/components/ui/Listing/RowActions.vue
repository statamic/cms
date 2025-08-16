<script setup>
import { Dropdown, DropdownItem, DropdownMenu, DropdownSeparator } from '@statamic/cms/ui';
import { injectListingContext } from '@statamic/components/ui/Listing/Listing.vue';
import ItemActions from '@statamic/components/actions/ItemActions.vue';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    row: {
        type: Object,
        required: true,
    },
});

const { actionUrl, actionContext, refresh, reorderable, allowActionsWhileReordering } = injectListingContext();
const busy = ref(false);

const shouldShowActions = computed(() => {
    if (reorderable.value && !allowActionsWhileReordering.value) return false;

    return true;
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
    Statamic.$toast.success(response.message || __('Action completed'));
    refresh();
}

function actionFailed(response) {
    Statamic.$toast.error(response.message || __('Action failed'));
}

function dropdownHovered(loadActions) {
    if (actionUrl.value) loadActions();
}
</script>

<template>
    <ItemActions
        v-if="shouldShowActions"
        :url="actionUrl"
        :item="row.id"
        :context="actionContext"
        :actions="row.actions"
        @started="actionStarted"
        @completed="actionCompleted"
        v-slot="{ actions, loadActions }"
    >
        <Dropdown @mouseover="dropdownHovered(loadActions)" placement="left-start" class="me-3">
            <DropdownMenu>
                <slot name="prepended-actions" :row="row" />
                <DropdownSeparator v-if="$slots['prepended-actions'] && actions.length" />
                <DropdownItem
                    v-for="action in actions"
                    :key="action.handle"
                    :text="__(action.title)"
                    :icon="action.icon"
                    :variant="action.dangerous ? 'destructive' : 'default'"
                    @click="action.run"
                />
            </DropdownMenu>
        </Dropdown>
    </ItemActions>
</template>
