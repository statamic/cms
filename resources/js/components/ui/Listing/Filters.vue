<script setup>
import {
    Badge,
    Button,
    Drawer,
    Panel,
    PanelHeader,
    Card,
    Heading,
} from '@ui';
import { injectListingContext } from '../Listing/Listing.vue';
import { computed, ref, watch, nextTick } from 'vue';
import FieldFilter from './FieldFilter.vue';
import DataListFilter from './Filter.vue';

const { filters, activeFilters, activeFilterBadges, activeFilterBadgeCount, setFilter, reorderable } = injectListingContext();

const open = ref(false);
const filtersButtonWrapperRef = ref(null);

const fieldFilter = computed(() => filters.value.find((filter) => filter.is_fields));
const fieldFilterHandle = computed(() => fieldFilter.value?.handle);
const fieldFilterBadges = computed(() => activeFilterBadges.value[fieldFilterHandle.value] || {});
const standardFilters = computed(() => filters.value.filter((filter) => !filter.is_fields));

const standardBadges = computed(() => {
    const { [fieldFilterHandle.value]: fields, ...badges } = activeFilterBadges.value;
    return badges;
});

function removeFieldFilter(handle) {
    const fields = { ...activeFilters.value[fieldFilterHandle.value] };
    delete fields[handle];
    setFilter(fieldFilterHandle.value, fields);
}

function isActive(handle) {
    return activeFilters.value.hasOwnProperty(handle);
}

const stackContentRef = ref(null);
const comboboxObserver = ref(null);

function tryFocusCombobox(root) {
    if (!root) return false;
    const anchor = root.querySelector('[data-ui-combobox-anchor]');
    if (anchor && typeof anchor.focus === 'function') {
        anchor.focus();
        return true;
    }
    const input = root.querySelector('input');
    if (input && typeof input.focus === 'function') {
        input.focus();
        return true;
    }
    return false;
}

function focusComboboxWhenReady() {
    const root = stackContentRef.value;
    if (!root) return;

    // If already in DOM, focus immediately
    if (tryFocusCombobox(root)) return;

    // Otherwise observe for it to appear
    if (comboboxObserver.value) comboboxObserver.value.disconnect();
    comboboxObserver.value = new MutationObserver(() => {
        if (tryFocusCombobox(root)) {
            comboboxObserver.value.disconnect();
            comboboxObserver.value = null;
        }
    });
    comboboxObserver.value.observe(root, { childList: true, subtree: true });
}

</script>

<template>
    <div class="flex flex-1 items-center gap-3 overflow-x-auto py-3">
        <Drawer name="filters" variant="layered" :title="__('Filters')" icon="sliders-horizontal" class="min-w-[calc(100vw-1rem)] lg:min-w-[50vw]" v-model:open="open">
            <template #trigger>
                <Button icon="sliders-horizontal" class="[&_svg]:size-3.5" :disabled="reorderable" @click="open = true">
                    {{ __('Filters') }}
                    <Badge
                        v-if="activeFilterBadgeCount"
                        :text="activeFilterBadgeCount"
                        size="sm"
                        pill
                        class="absolute -top-1.25 -right-2.75"
                    />
                </Button>
            </template>
            <div class="space-y-4">
                <Panel v-if="fieldFilter">
                    <PanelHeader class="flex items-center justify-between">
                        <Heading :text="__('Fields')" />
                        <Button v-if="isActive(fieldFilterHandle)" size="sm" :text="__('Clear')" @click="setFilter(fieldFilterHandle, null)" />
                    </PanelHeader>
                    <Card>
                        <FieldFilter
                            :config="fieldFilter"
                            :values="activeFilters.fields || {}"
                            @changed="setFilter(fieldFilterHandle, $event)"
                        />
                    </Card>
                </Panel>

                <Panel
                    v-for="filter in standardFilters"
                    :key="filter.handle"
                >
                    <PanelHeader class="flex items-center justify-between">
                        <Heading :text="filter.title" />
                        <Button v-if="isActive(filter.handle)" size="sm" :text="__('Clear')" @click="setFilter(filter.handle, null)" />
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
            <template #footer>
                <div class="flex gap-2 items-center justify-end">
                    <ui-button text="Close" variant="primary" @click="open = false" />
                </div>
            </template>
    </Drawer>
    <Button
        v-for="(badge, handle, index) in fieldFilterBadges"
        :key="handle"
        variant="filled"
        :icon-append="reorderable ? null : 'x'"
        :text="badge"
        :disabled="reorderable"
        class="last:me-12"
        @click="removeFieldFilter(handle)"
    />
    <Button
        v-for="(badge, handle, index) in standardBadges"
        :key="handle"
        variant="filled"
        :icon-append="reorderable ? null : 'x'"
        :text="badge"
        :disabled="reorderable"
        class="last:me-12"
        @click="setFilter(handle, null)"
    />
</div>
</template>
