<template>
    <div class="flex">
        <div class="flex flex-1 items-center" v-if="!inline">
            <div class="text-sm text-gray-600" v-if="showTotals && totalItems > 0">
                {{ __(':start-:end of :total', { start: fromItem, end: toItem, total: totalItems }) }}
            </div>
        </div>

        <div v-if="hasMultiplePages" class="flex items-center gap-1" :class="{ 'pagination-inline': inline }">
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

        <div class="flex items-center flex-1 justify-end" v-if="perPage && isPerPageEvenUseful">
            <span class="text-sm text-gray-600 me-3">{{ __('Per Page') }}</span>
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

<script>
import HasInputOptions from '../fieldtypes/HasInputOptions.js';
import { flatten, sortBy, range } from 'lodash-es';
import { Select, Button } from '@statamic/ui';

const onEachSide = 3;

export default {
    mixins: [HasInputOptions],

    components: {
        Select,
        Button,
    },

    props: {
        inline: { type: Boolean, default: false },
        showTotals: { type: Boolean, default: false },
        perPage: { type: Number },
        resourceMeta: { type: Object, required: true },
        scrollToTop: { type: Boolean, default: true },
        showPageLinks: { type: Boolean, default: true },
    },

    data() {
        return {
            onEachSide,
            window: onEachSide * 2,
        };
    },

    computed: {
        totalPages() {
            return this.resourceMeta.last_page;
        },

        pages() {
            const els = this.elements;

            let pages = [
                els.first,
                els.slider ? 'separator' : null,
                els.slider,
                els.last ? 'separator' : null,
                els.last,
            ].filter((i) => i !== null);

            return flatten(pages);
        },

        elements() {
            if (this.lastPage < this.onEachSide * 2 + 6) {
                return this.getSmallSlider();
            }

            if (this.currentPage <= this.window) {
                return this.getSliderTooCloseToBeginning();
            } else if (this.currentPage > this.lastPage - this.window) {
                return this.getSliderTooCloseToEnding();
            }

            return this.getFullSlider();
        },

        currentPage() {
            return this.resourceMeta.current_page;
        },

        lastPage() {
            return this.resourceMeta.last_page;
        },

        hasMultiplePages() {
            return this.totalPages > 1;
        },

        hasPrevious() {
            return this.currentPage > 1;
        },

        hasNext() {
            return this.currentPage < this.totalPages;
        },

        perPageOptions() {
            let defaultPaginationSize = Statamic.$config.get('paginationSize');
            let defaultOptions = Statamic.$config
                .get('paginationSizeOptions')
                .filter((size) => size !== defaultPaginationSize);
            let options = this.normalizeInputOptions(defaultOptions);

            options.push({
                value: defaultPaginationSize,
                label: `${defaultPaginationSize}`,
            });

            return sortBy(options, 'value');
        },

        isPerPageEvenUseful() {
            return this.resourceMeta.total > this.perPageOptions[0].value;
        },

        fromItem() {
            return this.resourceMeta.from || 0;
        },

        toItem() {
            return this.resourceMeta.to || 0;
        },

        totalItems() {
            return this.resourceMeta.total;
        },

        direction() {
            return this.$config.get('direction', 'ltr');
        },
    },

    methods: {
        selectPage(page) {
            if (page === this.currentPage) {
                return;
            }

            this.$emit('page-selected', page);

            if (this.scrollToTop) {
                window.scrollTo(0, 0);
            }
        },

        selectPreviousPage() {
            this.selectPage(this.currentPage - 1);
        },

        selectNextPage() {
            this.selectPage(this.currentPage + 1);
        },

        getSmallSlider() {
            return {
                first: this.getRange(1, this.lastPage),
                slider: null,
                last: null,
            };
        },

        getFullSlider() {
            return {
                first: this.getStart(),
                slider: this.getAdjacentRange(),
                last: this.getFinish(),
            };
        },

        getSliderTooCloseToBeginning() {
            return {
                first: this.getRange(1, this.window + 2),
                slider: null,
                last: this.getFinish(),
            };
        },

        getSliderTooCloseToEnding() {
            const last = this.getRange(this.lastPage - (this.window + 2), this.lastPage);

            return {
                first: this.getStart(),
                slider: null,
                last,
            };
        },

        getStart() {
            return this.getRange(1, 2);
        },

        getFinish() {
            return this.getRange(this.lastPage - 1, this.lastPage);
        },

        getAdjacentRange() {
            return this.getRange(this.currentPage - this.onEachSide, this.currentPage + this.onEachSide);
        },

        getRange(start, end) {
            return range(start, end + 1);
        },
    },
};
</script>
