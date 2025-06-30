<script setup>
import HeaderCell from '@statamic/components/ui/Listing/HeaderCell.vue';
import ToggleAll from '@statamic/components/ui/Listing/ToggleAll.vue';
import { injectListingContext } from '@statamic/components/ui/Listing/Listing.vue';

const { allowsSelections, reorderable, hasActions, visibleColumns, allowsMultipleSelections } = injectListingContext();
</script>

<template>
    <thead v-if="allowsSelections || visibleColumns.length > 1">
        <tr>
            <th
                v-if="allowsSelections || reorderable"
                :class="{ 'checkbox-column': !reorderable, 'handle-column': reorderable }"
            >
                <ToggleAll v-if="allowsSelections && allowsMultipleSelections" />
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
