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
import { dateFormatter } from '@/api';
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

function removeFieldFilter(filterHandle, fieldHandle) {
    const fields = { ...activeFilters.value[filterHandle] };
    delete fields[fieldHandle];
    setFilter(filterHandle, fields);
}

function getFieldFilterBadgeLabel(handle, badge) {
    if (handle === 'date') {
        const df = dateFormatter.options('date');
        const parts = [badge.field, badge.translatedOperator];

        if (badge.operator === 'between') {
            parts.push(df.date(badge.value.start), __('and'), df.date(badge.value.end));
        } else {
            parts.push(df.date(badge.value));
        }

        return parts.filter(Boolean).join(' ');
    }

    return badge;
}

function getClearFilterLabel(label) {
    return __('Clear :filter', { filter: label });
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
                        v-for="filter in fieldFilters"
                        :key="filter.handle"
                    >
                        <PanelHeader class="flex items-center justify-between">
                            <Heading :text="filter.title" />
                            <Button v-if="isActive(filter.handle)" size="sm" :text="__('Clear')" @click="setFilter(filter.handle, null)" />
                        </PanelHeader>
                        <Card>
                            <FieldFilter
                                :config="filter"
                                :values="activeFilters[filter.handle] || {}"
                                @changed="setFilter(filter.handle, $event)"
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
                    <Button variant="primary" :text="__('Done')" @click="handleStackClosed" />
                </div>
            </div>
        </Stack>

        <Button
            as="div"
            variant="filled"
            v-for="({ filter, handle, badge }, index) in fieldFilterBadges"
            :key="`${filter}-${handle}`"
            class="cursor-default ps-4 gap-1 text-gray-900 dark:text-gray-200 last:me-12 hover:bg-gray-950/5 dark:hover:bg-white/4"
            :class="reorderable ? 'pe-4 text-gray-400 dark:text-gray-600' : 'pe-2'"
        >
            <span class="whitespace-nowrap" v-text="getFieldFilterBadgeLabel(handle, badge)" />

            <Button
                v-if="!reorderable"
                variant="ghost"
                size="xs"
                icon="x"
                iconOnly
                inset
                class="opacity-100 [&_svg]:size-4"
                :aria-label="getClearFilterLabel(getFieldFilterBadgeLabel(handle, badge))"
                @click="removeFieldFilter(filter, handle)"
            />
        </Button>
        <Button
            as="div"
            variant="filled"
            v-for="(badge, handle, index) in standardBadges"
            :key="handle"
            class="cursor-default ps-4 gap-1 text-gray-900 dark:text-gray-200 last:me-12 hover:bg-gray-950/5 dark:hover:bg-white/4"
            :class="reorderable ? 'pe-4 text-gray-400 dark:text-gray-600' : 'pe-2'"
        >
            <span class="whitespace-nowrap">{{ badge }}</span>
            <Button
                v-if="!reorderable"
                variant="ghost"
                size="xs"
                icon="x"
                iconOnly
                inset
                class="opacity-100 [&_svg]:size-4"
                :aria-label="getClearFilterLabel(badge)"
                @click="setFilter(handle, null)"
            />
        </Button>
    </div>
</template>
