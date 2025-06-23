<script setup>
import HeaderCell from '@statamic/components/ui/Listing/HeaderCell.vue';
import ToggleAll from '@statamic/components/ui/Listing/ToggleAll.vue';
import { injectListingContext } from '@statamic/components/ui/Listing/Listing.vue';
import { computed } from 'vue';

const { showBulkActions, reorderable, hasActions, visibleColumns, maxSelections } = injectListingContext();
const singleSelect = computed(() => maxSelections === 1);
</script>

<template>
    <thead v-if="showBulkActions || visibleColumns.length > 1">
        <tr>
            <th
                v-if="showBulkActions || reorderable"
                :class="{ 'checkbox-column': !reorderable, 'handle-column': reorderable }"
            >
                <ToggleAll v-if="showBulkActions && !singleSelect" />
            </th>
            <HeaderCell v-for="column in visibleColumns" :key="column.field" :column />
            <!--                    <th class="type-column" v-if="type">-->
            <!--                        <template v-if="type === 'entries'">{{ __('Collection') }}</template>-->
            <!--                        <template v-if="type === 'terms'">{{ __('Taxonomy') }}</template>-->
            <!--                    </th>-->
            <th class="actions-column" v-if="hasActions" />
        </tr>
    </thead>
</template>
