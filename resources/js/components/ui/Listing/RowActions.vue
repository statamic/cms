<script setup>
import {
    Dropdown,
    DropdownItem,
    DropdownMenu,
    DropdownSeparator,
    Skeleton,
} from '@ui';
import { injectListingContext } from '../Listing/Listing.vue';
import ItemActions from '@/components/actions/ItemActions.vue';
import { hasSlotContent } from '@/composables/has-slot-content';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    row: {
        type: Object,
        required: true,
    },
});

const { actionUrl, actionContext, refresh, reorderable, allowActionsWhileReordering } = injectListingContext();
const busy = ref(false);

const hasPrependedActionsContent = hasSlotContent('prepended-actions', computed(() => ({ row: props.row })));

const shouldShowActions = computed(() => {
    if (reorderable.value && !allowActionsWhileReordering.value) return false;

    return hasPrependedActionsContent.value || props.row.actions?.length > 0;
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
        v-slot="{ actions, loadActions, shouldShowSkeleton }"
    >
        <Dropdown
            @mouseover="dropdownHovered(loadActions)"
            @focus="dropdownHovered(loadActions)"
            @click="dropdownHovered(loadActions)"
            placement="left-start"
            class="me-3"
        >
            <DropdownMenu>
                <slot name="prepended-actions" :row="row" />
                <DropdownSeparator v-if="hasPrependedActionsContent && (shouldShowSkeleton || actions.length)" />
                <template v-if="shouldShowSkeleton">
                    <div v-for="index in 3" :key="index" class="contents">
                        <Skeleton class="m-1 size-5" />
                        <Skeleton
                            class="mx-2 my-1.5 h-5"
                            :class="index === 1 ? 'w-28' : index === 2 ? 'w-36' : 'w-24'"
                        />
                    </div>
                </template>
                <template v-else>
                    <DropdownItem
                        v-for="action in actions"
                        :key="action.handle"
                        :text="__(action.title)"
                        :icon="action.icon"
                        :variant="action.dangerous ? 'destructive' : 'default'"
                        @click="action.run"
                    />
                </template>
            </DropdownMenu>
        </Dropdown>
    </ItemActions>
</template>
