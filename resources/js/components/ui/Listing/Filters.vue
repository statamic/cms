<script setup>
import { Badge, Button, Panel, PanelHeader, Card, Heading } from '@statamic/ui';
import { injectListingContext } from '@statamic/components/ui/Listing/Listing.vue';
import { computed } from 'vue';
import FieldFilter from './FieldFilter.vue';
import DataListFilter from './Filter.vue';
import { ref } from 'vue';

const { filters, activeFilters, activeFilterBadges, setFilter, reorderable } = injectListingContext();

const open = ref(false);

const badgeCount = computed(() => {
    let count = Object.keys(activeFilterBadges.value).length;

    if (activeFilterBadges.value.hasOwnProperty('fields')) {
        count = count + Object.keys(activeFilterBadges.value.fields).length - 1;
    }

    return count;
});

const fieldFilter = computed(() => filters.value.find((filter) => filter.handle === 'fields'));
const standardFilters = computed(() => filters.value.filter((filter) => filter.handle !== 'fields'));
const fieldFilterBadges = computed(() => activeFilterBadges.value.fields || {});

const standardBadges = computed(() => {
    const { fields, ...badges } = activeFilterBadges.value;
    return badges;
});

function removeFieldFilter(handle) {
    const fields = { ...activeFilters.value.fields };
    delete fields[handle];
    setFilter('fields', fields);
}

function isActive(filter) {
    return activeFilters.value.hasOwnProperty(filter.handle);
}

function needsFinalFieldBadgeMargin(index) {
    return standardBadges.value.length === 0
        && index === Object.keys(fieldFilterBadges.value).length - 1;
}

function needsFinalStandardBadgeMargin(index) {
    return index === Object.keys(standardBadges.value).length - 1;
}
</script>

<template>
    <div class="flex flex-1 items-center gap-3 overflow-x-auto py-3 rounded-r-4xl">

        <div class="sticky left-0 ps-[1px] rounded-r-lg bg-white dark:bg-gray-900 mask-bg mask-bg--left mask-bg--left-small">
            <Button icon="sliders-horizontal" class="[&_svg]:size-3.5" :disabled="reorderable" @click="open = true">
                {{ __('Filters') }}
                <Badge
                    v-if="badgeCount"
                    :text="badgeCount"
                    size="sm"
                    pill
                    class="absolute -top-1.5 -right-1.5"
                />
            </Button>
        </div>

        <stack narrow name="filters" v-if="open" @closed="open = false">
            <div class="flex-1 p-3 bg-white h-full overflow-auto">
                <div class="space-y-4">
                    <FieldFilter
                        :config="fieldFilter"
                        :values="activeFilters.fields || {}"
                        @changed="setFilter('fields', $event)"
                    />

                    <Panel
                        v-for="filter in standardFilters"
                        :key="filter.handle"
                    >
                        <PanelHeader class="flex items-center justify-between">
                            <Heading :text="filter.title" />
                            <Button v-if="isActive(filter)" size="sm" text="Clear" @click="setFilter(filter.handle, null)" />
                        </PanelHeader>
                        <Card>
                            <data-list-filter
                                :filter="filter"
                                :values="activeFilters[filter.handle]"
                                @changed="setFilter(filter.handle, $event)"
                            />
                        </Card>
                    </Panel>
                </div>
            </div>
        </stack>

        <Button
            v-for="(badge, handle, index) in fieldFilterBadges"
            :key="handle"
            variant="filled"
            :icon-append="reorderable ? null : 'x'"
            :text="badge"
            :disabled="reorderable"
            :class="{ 'me-12': needsFinalFieldBadgeMargin(index) }"
            @click="removeFieldFilter(handle)"
        />
        <Button
            v-for="(badge, handle, index) in standardBadges"
            :key="handle"
            variant="filled"
            :icon-append="reorderable ? null : 'x'"
            :text="badge"
            :disabled="reorderable"
            :class="{ 'me-12 ': needsFinalStandardBadgeMargin(index) }"
            @click="setFilter(handle, null)"
        />
    </div>
</template>
