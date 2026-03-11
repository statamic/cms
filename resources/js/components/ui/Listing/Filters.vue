<script setup>
import {
    Badge,
    Button,
    Panel,
    PanelHeader,
    Card,
    Heading,
	Stack,
} from '@ui';
import { injectListingContext } from '../Listing/Listing.vue';
import { computed, ref, watch, nextTick } from 'vue';
import FieldFilter from './FieldFilter.vue';
import DataListFilter from './Filter.vue';

const { filters, activeFilters, activeFilterBadges, activeFilterBadgeCount, setFilter, reorderable } = injectListingContext();

const emit = defineEmits(['filters-updated']);

const open = ref(false);
const filtersButtonWrapperRef = ref(null);

const standardFilters = computed(() => filters.value.filter((filter) => !filter.is_fields));
const standardFilterHandles = computed(() => standardFilters.value.map(filter => filter.handle));
const standardBadges = computed(() => Object.fromEntries(
    Object.entries(activeFilterBadges.value).filter(([handle]) => standardFilterHandles.value.includes(handle))
));

const fieldFilters = computed(() => filters.value.filter((filter) => filter.is_fields));
const fieldFilterHandles = computed(() => fieldFilters.value.map(filter => filter.handle));
const fieldFilterBadges = computed(() => Object.entries(activeFilterBadges.value)
    .filter(([filter]) => fieldFilterHandles.value.includes(filter))
    .flatMap(([filter, badges]) => Object.entries(badges).map(([handle, badge]) => ({ filter, handle, badge })))
);

const filterPanels = computed(() => [
    ...fieldFilters.value.map((filter) => ({
        handle: filter.handle,
        title: filter.title,
        component: FieldFilter,
        componentProps: {
            config: filter,
            values: activeFilters.value[filter.handle] || {},
        },
    })),
    ...standardFilters.value.map((filter) => ({
        handle: filter.handle,
        title: filter.title,
        component: DataListFilter,
        componentProps: {
            filter,
            values: activeFilters.value[filter.handle],
        },
    })),
]);

const badgeChipClasses = 'group last:me-12 inline-flex h-10 items-center gap-1 rounded-lg bg-gray-950/5 ps-4 pe-2 text-sm font-medium text-gray-900 dark:bg-white/4 dark:text-gray-200';
const badgeChipClearButtonClasses = 'opacity-100 [&_svg]:size-4';

const badgeChips = computed(() => [
    ...fieldFilterBadges.value.map(({ filter, handle, badge }) => ({
        key: `${filter}-${handle}`,
        filter,
        handle,
        badge,
        isFieldBadge: true,
    })),
    ...Object.entries(standardBadges.value).map(([handle, badge]) => ({
        key: handle,
        handle,
        badge,
        isFieldBadge: false,
    })),
]);

function removeFieldFilter(filterHandle, fieldHandle) {
    const fields = { ...activeFilters.value[filterHandle] };
    delete fields[fieldHandle];
    setFilter(filterHandle, fields);
}

function clearBadgeChip(chip) {
    if (chip.isFieldBadge) {
        removeFieldFilter(chip.filter, chip.handle);

        return;
    }

    setFilter(chip.handle, null);
}

function isDateBadge(chip) {
    return chip.isFieldBadge && chip.handle === 'date';
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

watch(open, async (isOpen) => {
    if (!isOpen) return;
    await nextTick();
    focusComboboxWhenReady();
});

watch(activeFilters, () => {
    emit('filters-updated', activeFilters.value);
}, { deep: true });

function handleStackClosed() {
    // Clean up observer if active
    if (comboboxObserver.value) {
        comboboxObserver.value.disconnect();
        comboboxObserver.value = null;
    }

    open.value = false;

    nextTick(() => {
        requestAnimationFrame(() => {
            const wrapper = filtersButtonWrapperRef.value;
            const buttonEl = wrapper ? wrapper.querySelector('button') : null;
            if (buttonEl && typeof buttonEl.focus === 'function') buttonEl.focus();
        });
    });
}
</script>

<template>
    <div class="flex flex-1 items-center gap-2 sm:gap-3 overflow-x-auto py-3 rounded-r-4xl">

        <div ref="filtersButtonWrapperRef" class="sticky left-0 ps-[1px] rounded-r-lg mask-bg mask-bg--left mask-bg--left-small">
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
        </div>

        <Stack
            size="half"
            :open="open"
            @update:open="handleStackClosed"
            :title="__('Filters')"
            icon="sliders-horizontal"
        >
            <div ref="stackContentRef" class="">
                <div class="space-y-4">
                    <Panel
                        v-for="panel in filterPanels"
                        :key="panel.handle"
                    >
                        <PanelHeader class="flex items-center justify-between">
                            <Heading :text="panel.title" />
                            <Button v-if="isActive(panel.handle)" size="sm" :text="__('Clear')" @click="setFilter(panel.handle, null)" />
                        </PanelHeader>
                        <Card>
                            <component
                                :is="panel.component"
                                v-bind="panel.componentProps"
                                @changed="setFilter(panel.handle, $event)"
                            />
                        </Card>
                    </Panel>
                    <Button variant="primary" :text="__('Done')" @click="handleStackClosed" />
                </div>
            </div>
        </Stack>

        <div
            v-for="chip in badgeChips"
            :key="chip.key"
            :class="badgeChipClasses"
        >
            <div class="flex items-center gap-1.5 whitespace-nowrap">
                <template v-if="isDateBadge(chip)">
                    {{ chip.badge.field }}
                    {{ chip.badge.translatedOperator }}
                    <template v-if="chip.badge.operator === 'between'">
                        <date-time :of="chip.badge.value.start" options="date" />
                        {{ __('and') }}
                        <date-time :of="chip.badge.value.end" options="date" />
                    </template>
                    <date-time v-else :of="chip.badge.value" options="date" />
                </template>

                <template v-else>
                    {{ chip.badge }}
                </template>
            </div>

            <Button
                v-if="!reorderable"
                variant="ghost"
                size="xs"
                icon="x"
                iconOnly
                inset
                :class="badgeChipClearButtonClasses"
                :aria-label="__('Clear')"
                @click="clearBadgeChip(chip)"
            />
        </div>
    </div>
</template>
