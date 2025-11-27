<script setup>
import {
    Dropdown,
    DropdownItem,
    DropdownMenu,
    DropdownSeparator,
} from '@ui';
import { injectListingContext } from '../Listing/Listing.vue';
import ItemActions from '@/components/actions/ItemActions.vue';
import { computed, ref, watch, useSlots, Comment, Fragment } from 'vue';

const props = defineProps({
    row: {
        type: Object,
        required: true,
    },
});

const slots = useSlots();
const { actionUrl, actionContext, refresh, reorderable, allowActionsWhileReordering } = injectListingContext();
const busy = ref(false);

const hasPrependedActionsContent = computed(() => {
    if (!slots['prepended-actions']) return false;

    // Call the slot with the row prop to get the actual vnodes
    const slotContent = slots['prepended-actions']({ row: props.row });

    const hasRealContent = (vnodes) => {
        if (!vnodes || vnodes.length === 0) return false;

        return vnodes.some(vnode => {
            // Skip comments
            if (vnode.type === Comment) return false;

            // Skip empty text nodes
            if (typeof vnode.children === 'string' && !vnode.children.trim()) return false;

            // Handle fragments (like from v-for)
            if (vnode.type === Fragment) {
                return hasRealContent(vnode.children);
            }

            // If it has array children, recursively check them
            if (Array.isArray(vnode.children)) {
                return hasRealContent(vnode.children);
            }

            // Otherwise it's real content
            return true;
        });
    };

    return hasRealContent(slotContent);
});

const shouldShowActions = computed(() => {
    if (reorderable.value && !allowActionsWhileReordering.value) return false;

    return hasPrependedActionsContent.value;
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
