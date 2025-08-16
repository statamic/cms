<script setup>
import HeaderCell from '@statamic/components/ui/Listing/HeaderCell.vue';
import ToggleAll from '@statamic/components/ui/Listing/ToggleAll.vue';
import { injectListingContext } from '@statamic/components/ui/Listing/Listing.vue';
import { computed } from 'vue';

const { allowsSelections, reorderable, hasActions, visibleColumns, allowsMultipleSelections } = injectListingContext();

const props = defineProps({
    srOnly: {
        type: Boolean,
        default: false,
    },
});

const hasVisibleHeader = computed(() => {
    if (props.srOnly) return false;
    return allowsSelections.value || visibleColumns.value.length > 1;
})
</script>

<template>
    <thead v-if="hasVisibleHeader">
        <tr>
            <th
                v-if="allowsSelections || reorderable"
                :class="{ 'checkbox-column': !reorderable, 'handle-column': reorderable }"
                scope="col"
                :aria-label="reorderable ? __('Reorder') : __('Select')"
            >
                <ToggleAll v-if="allowsSelections && allowsMultipleSelections" />
            </th>
            <HeaderCell v-for="column in visibleColumns" :key="column.field" :column />
            <!-- <th class="type-column" v-if="type">
                <template v-if="type === 'entries'">{{ __('Collection') }}</template>
                <template v-if="type === 'terms'">{{ __('Taxonomy') }}</template>
            </th> -->
            <th scope="col" class="actions-column" v-if="hasActions" :aria-label="__('Actions')" />
        </tr>
    </thead>

    <thead v-else class="sr-only">
        <tr>
            <th v-for="column in visibleColumns" :key="column.field" scope="col" v-text="__(column.label || column.field)" />
        </tr>
    </thead>
</template>
