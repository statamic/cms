<script setup>
import HasInputOptions from '@statamic/components/fieldtypes/HasInputOptions.js';
const normalizeInputOptions = HasInputOptions.methods.normalizeInputOptions;
import { flatten, sortBy, range } from 'lodash-es';
import { Select, Button } from '@statamic/ui';
import { computed } from 'vue';

const emit = defineEmits(['page-selected', 'per-page-changed']);

const props = defineProps({
    showTotals: { type: Boolean, default: true },
    perPage: { type: Number },
    resourceMeta: { type: Object, required: true },
    scrollToTop: { type: Boolean, default: true },
    showPageLinks: { type: Boolean, default: true },
    showPerPageSelector: { type: Boolean, default: true },
});

const onEachSide = 3;
const win = onEachSide * 2;
const totalPages = computed(() => props.resourceMeta.last_page);

const pages = computed(() => {
    const els = elements.value;

    let pages = [
        els.first,
        els.slider ? 'separator' : null,
        els.slider,
        els.last ? 'separator' : null,
        els.last,
    ].filter((i) => i !== null);

    return flatten(pages);
});

const elements = computed(() => {
    if (lastPage.value < onEachSide * 2 + 6) {
        return getSmallSlider();
    }

    if (currentPage.value <= win) {
        return getSliderTooCloseToBeginning();
    } else if (currentPage.value > lastPage.value - win) {
        return getSliderTooCloseToEnding();
    }

    return getFullSlider();
});

const currentPage = computed(() => props.resourceMeta.current_page);
const lastPage = computed(() => props.resourceMeta.last_page);
const hasMultiplePages = computed(() => totalPages.value > 1);
const hasPrevious = computed(() => currentPage.value > 1);
const hasNext = computed(() => currentPage.value < totalPages.value);

const perPageOptions = computed(() => {
    let defaultPaginationSize = Statamic.$config.get('paginationSize');
    let defaultOptions = Statamic.$config.get('paginationSizeOptions').filter((size) => size !== defaultPaginationSize);
    let options = normalizeInputOptions(defaultOptions);

    options.push({
        value: defaultPaginationSize,
        label: `${defaultPaginationSize}`,
    });

    return sortBy(options, 'value');
});

const isPerPageEvenUseful = computed(() => props.resourceMeta.total > perPageOptions.value[0].value);
const showPerPageSelector = computed(() => props.showPerPageSelector && isPerPageEvenUseful.value);
const totalItems = computed(() => props.resourceMeta.total);
const fromItem = computed(() => props.resourceMeta.from || 0);
const toItem = computed(() => Math.min(props.resourceMeta.to, totalItems.value));

function selectPage(page) {
    if (page === currentPage.value) {
        return;
    }

    emit('page-selected', page);

    if (props.scrollToTop) {
        window.scrollTo(0, 0);
    }
}

function selectPreviousPage() {
    selectPage(currentPage.value - 1);
}

function selectNextPage() {
    selectPage(currentPage.value + 1);
}

function getSmallSlider() {
    return {
        first: getRange(1, lastPage.value),
        slider: null,
        last: null,
    };
}

function getFullSlider() {
    return {
        first: getStart(),
        slider: getAdjacentRange(),
        last: getFinish(),
    };
}

function getSliderTooCloseToBeginning() {
    return {
        first: getRange(1, win + 2),
        slider: null,
        last: getFinish(),
    };
}

function getSliderTooCloseToEnding() {
    const last = getRange(lastPage.value - (win + 2), lastPage.value);

    return {
        first: getStart(),
        slider: null,
        last,
    };
}

function getStart() {
    return getRange(1, 2);
}

function getFinish() {
    return getRange(lastPage.value - 1, lastPage.value);
}

function getAdjacentRange() {
    return getRange(currentPage.value - onEachSide, currentPage.value + onEachSide);
}

function getRange(start, end) {
    return range(start, end + 1);
}
</script>

<template>
    <div class="flex">
        <div class="flex flex-1 items-center">
            <div class="text-sm text-gray-500" v-if="showTotals && totalItems > 0">
                {{ __(':start-:end of :total', { start: fromItem, end: toItem, total: totalItems }) }}
            </div>
        </div>

        <div v-if="hasMultiplePages" class="flex items-center gap-1">
            <Button
                size="sm"
                :variant="hasPrevious && !showPageLinks ? 'filled' : 'ghost'"
                round
                icon="ui/chevron-left"
                :disabled="!hasPrevious"
                @click="selectPreviousPage"
            />

            <Button
                v-if="showPageLinks"
                v-for="(page, i) in pages"
                size="sm"
                round
                :variant="page == currentPage ? 'filled' : 'ghost'"
                :key="i"
                @click="selectPage(page)"
                :disabled="page === 'separator' || page === currentPage"
                :text="page === 'separator' ? '...' : String(page)"
            />

            <Button
                size="sm"
                :variant="hasNext && !showPageLinks ? 'filled' : 'ghost'"
                round
                icon="ui/chevron-right"
                :disabled="!hasNext"
                @click="selectNextPage"
            />
        </div>

        <div class="flex flex-1 items-center justify-end" v-if="perPage && showPerPageSelector">
            <span class="me-3 text-sm text-gray-500">{{ __('Per Page') }}</span>
            <Select
                class="w-auto!"
                size="sm"
                :options="perPageOptions"
                :model-value="perPage"
                @update:model-value="$emit('per-page-changed', $event)"
            />
        </div>
    </div>
</template>
