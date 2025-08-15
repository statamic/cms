<script setup>
import { ref, computed, watch } from 'vue';
import CommandPaletteItem from './Item.vue';
import CommandPaletteLoadingItem from './LoadingItem.vue';
import axios from 'axios';
import debounce from '@statamic/util/debounce';
import { DialogContent, DialogOverlay, DialogPortal, DialogRoot, DialogTitle, DialogTrigger, DialogDescription, VisuallyHidden } from 'reka-ui';
import { ComboboxContent, ComboboxEmpty, ComboboxGroup, ComboboxLabel, ComboboxInput, ComboboxItem, ComboboxRoot, ComboboxViewport } from 'reka-ui';
import fuzzysort from 'fuzzysort';
import { each, groupBy, orderBy, find, uniq } from 'lodash-es';
import { motion } from 'motion-v';
import { cva } from 'cva';
import { Icon, Subheading } from '@statamic/ui';

let open = ref(false);
let query = ref('');
let serverItemsLoaded = ref(false);
let serverItems = ref(setServerLoadingItems());
let searchResults = ref([]);
let selected = ref(null);
let recentItems = ref(getRecentItems());

Statamic.$keys.bindGlobal(['mod+k'], (e) => {
    e.preventDefault();
    open.value = true;
});

each({
    esc: () => open.value = false,
    'ctrl+n': () => document.activeElement.dispatchEvent(new KeyboardEvent('keydown', { key: 'ArrowDown' })),
    'ctrl+p': () => document.activeElement.dispatchEvent(new KeyboardEvent('keydown', { key: 'ArrowUp' })),
}, (callback, binding) => {
    Statamic.$keys.bindGlobal([binding], (e) => {
        if (open.value) {
            e.preventDefault();
            callback();
        }
    });
});

const actionItems = computed(() => {
    return sortJsInjectedItems(Statamic.$commandPalette.actions().filter(item => item.when()));
});

const miscItems = computed(() => {
    return sortJsInjectedItems(Statamic.$commandPalette.misc().filter(item => item.when()));
});

const aggregatedItems = computed(() => [
    ...(actionItems.value || []),
    ...(recentItems.value || []),
    ...(serverItems.value || []),
    ...(miscItems.value || []),
    ...(searchResults.value || []),
]);

const results = computed(() => {
    let items = aggregatedItems.value.map(item => normalizeItem(item));

    let filtered = fuzzysort
        .go(query.value, items, {
            all: true,
            keys: ['text'],
            scoreFn: fuzzysortScoringAlgorithm,
        })
        .map(result => {
            return {
                score: result._score,
                html: result[0].highlight('<span class="text-blue-600 dark:text-blue-400 underline underline-offset-4 decoration-blue-200 dark:decoration-blue-600/45">', '</span>'),
                ...result.obj,
            };
        });

    let serverCategories = Statamic.$config.get('commandPaletteCategories');

    let categoryOrder = query.value
        ? uniq(filtered.map(item => item.category))
        : serverCategories;

    let grouped = groupBy(filtered, 'category');

    return categoryOrder
        .filter(category => serverCategories.includes(category))
        .map(category => {
            return {
                text: __(category),
                items: grouped[category],
            };
        })
        .filter(category => category.items);
});

function fuzzysortScoringAlgorithm(result) {
    let multiplier = 1;

    switch (result.obj.category) {
        case 'Recent':
            multiplier += 0.6;
            break;
        case 'Navigation':
        case 'Fields':
            multiplier += 0.5;
            break;
    }

    return result.score * multiplier;
}

function normalizeItem(item) {
    if (Array.isArray(item.text)) {
        item.text = item.text.join(' » ');
    }

    return item;
}

watch(selected, (item) => {
    if (!item) return;
    select(item);
    reset();
});

watch(query, debounce(() => {
    searchContent();
}, 300));

watch(open, (isOpen) => {
    if (isOpen) {
        getServerItems();
        return;
    }
    reset();
});

function setServerLoadingItems() {
    return [
        { category: 'Navigation', loading: true },
        { category: 'Fields', loading: true },
    ];
}

function getServerItems() {
    if (serverItemsLoaded.value) return;

    axios.get('/cp/command-palette').then((response) => {
        serverItemsLoaded.value = true;
        serverItems.value = response.data;
    });
}

function searchContent() {
    axios.get('/cp/command-palette/search', { params: { q: query.value } }).then((response) => {
        searchResults.value = response.data;
    });
}

function select(selected) {
    let item = findSelectedItem(selected);

    if (item.trackRecent) {
        addToRecentItems(item);
    }

    if (item.href) {
        return;
    }

    if (item.action) {
        item.action();
    }
}

function findSelectedItem(selected) {
    return find(aggregatedItems.value, (result) => result.text === selected);
}

function sortJsInjectedItems(items) {
    return orderBy(items, ['prioritize', 'text'], ['desc', 'asc']);
}

function getRecentItems() {
    const stored = localStorage.getItem('statamic.command-palette.recent');

    return stored ? JSON.parse(stored) : [];
}

function addToRecentItems(item) {
    item.category = __('Recent');

    const filtered = getRecentItems().filter(recentItem => recentItem.text !== item.text);
    const updated = [item, ...filtered].slice(0, 5);

    localStorage.setItem('statamic.command-palette.recent', JSON.stringify(updated));

    recentItems.value = updated;
}

function isRecentItem(item) {
    return item.category === 'Recent';
}

function removeRecentItem(url) {
    const updated = recentItems.value.filter(recentItem => recentItem.url !== url);

    localStorage.setItem('statamic.command-palette.recent', JSON.stringify(updated));

    recentItems.value = updated;
}

function reset() {
    open.value = false;
    query.value = '';
    selected.value = null;
    searchResults.value = [];
}

function keydownTab(e) {
    document.activeElement.dispatchEvent(new KeyboardEvent('keydown', { key: e.shiftKey ? 'ArrowUp' : 'ArrowDown' }));
}

const modalClasses = cva({
    base: [
        'fixed outline-hidden left-1/2 top-[100px] z-50 w-full max-w-3xl -translate-x-1/2 ',
        'backdrop-blur-[2px] rounded-2xl',
        'shadow-[0_8px_5px_-6px_rgba(0,0,0,0.12),_0_3px_8px_0_rgba(0,0,0,0.02),_0_30px_22px_-22px_rgba(39,39,42,0.35)]',
        'dark:shadow-[0_5px_20px_rgba(0,0,0,.5)]',
        'duration-200 will-change-[transform,opacity]',
        'data-[state=open]:animate-in data-[state=closed]:animate-out',
        'data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0',
        'data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95',
        'slide-in-from-top-2',
    ],
})({});
</script>

<template>
    <DialogRoot v-model:open="open" :modal="true">
        <DialogTrigger>
            <div class="data-[focus-visible]:outline-focus hover flex cursor-text items-center gap-x-2 rounded-md [button:has(>&)]:rounded-md bg-gray-900 text-xs text-gray-400 shadow-[0_-1px_rgba(255,255,255,0.06),0_4px_8px_rgba(0,0,0,0.05),0_1px_6px_-4px_#000] ring-1 ring-gray-900/10 outline-none hover:ring-white/10 md:w-32 md:py-[calc(5/16*1rem)] md:ps-2 md:pe-1.5 md:shadow-[0_1px_5px_-4px_rgba(19,19,22,0.4),0_2px_5px_rgba(32,42,54,0.06)]">
                <Icon name="magnifying-glass" class="size-5 flex-none text-gray-600" />
                <span class="sr-only leading-none md:not-sr-only st-text-trim-cap">Search</span>
                <kbd class="ml-auto hidden self-center rounded bg-white/5 px-[0.3125rem] py-[0.0625rem] text-[0.625rem]/4 font-medium text-gray-400 ring-1 ring-white/7.5 [word-spacing:-0.15em] ring-inset md:block">
                    <kbd class="font-sans">⌘ </kbd><kbd class="font-sans">K</kbd>
                </kbd>
            </div>
        </DialogTrigger>
        <DialogPortal>
            <DialogOverlay class="fixed inset-0 z-30 bg-gray-800/20 backdrop-blur-[2px] dark:bg-gray-800/50" />
            <DialogContent :class="[modalClasses, $attrs.class]" data-ui-modal-content :aria-describedby="undefined">
                <VisuallyHidden asChild>
                    <DialogTitle>{{ __('Command Palette') }}</DialogTitle>
                </VisuallyHidden>
                <VisuallyHidden asChild>
                    <DialogDescription>{{ __('Search for content, navigate, and run actions.') }}</DialogDescription>
                </VisuallyHidden>
                <motion.div
                    class="relative rounded-xl border-b border-gray-200/80 bg-white shadow-[0_1px_16px_-2px_rgba(63,63,71,0.2)] dark:border-gray-950 dark:bg-gray-800 dark:shadow-[0_10px_15px_rgba(0,0,0,.5)] dark:inset-shadow-2xs dark:inset-shadow-white/15"
                    :initial="{ scale: 1.0 }"
                    :whilePress="{ scale: 0.985 }"
                    :transition="{ duration: 0.1 }"
                >
                    <ComboboxRoot
                        :open="true"
                        :default-open="true"
                        :ignore-filter="true"
                        v-model="selected"
                        @keydown.tab.prevent.stop="keydownTab"
                    >
                        <header class="group/cmd-input flex h-14 items-center gap-2 border-b border-gray-200/80 px-5.5 dark:border-gray-950">
                            <Icon name="magnifying-glass" class="size-5 text-gray-400" />
                            <ComboboxInput
                                :auto-focus="true"
                                :placeholder="__('Search or jump to...')"
                                v-model="query"
                                class="flex w-full bg-transparent py-4 text-lg antialiased outline-none placeholder:text-gray-500!"
                            />
                        </header>
                        <ComboboxContent>
                            <ComboboxViewport class="max-h-[360px] min-h-[360px] divide-y divide-gray-200/80 overflow-y-auto dark:divide-gray-950">
                                <ComboboxEmpty v-if="query && !results.length" class="px-3 py-2 opacity-50">
                                    <CommandPaletteItem :text="__('No results found!')" icon="entry" disabled />
                                </ComboboxEmpty>
                                <ComboboxGroup
                                    v-else
                                    v-for="category in results"
                                    :key="category.text"
                                    class="space-y-1 px-3 py-2"
                                >
                                    <ComboboxLabel :as-child="true">
                                        <Subheading
                                            v-if="category.text != 'Actions'"
                                            size="sm"
                                            class="border-0 px-3 py-1"
                                            v-text="category.text"
                                        ></Subheading>
                                    </ComboboxLabel>
                                    <ComboboxItem
                                        v-for="item in category.items"
                                        :key="item.text"
                                        :value="item.text"
                                        :text-value="item.text"
                                        :as-child="true"
                                        :disabled="item.loading"
                                    >
                                        <CommandPaletteLoadingItem class="rounded-lg px-2 py-1.5 w-full opacity-20" v-if="item.loading" />
                                        <CommandPaletteItem
                                            v-else
                                            :icon="item.icon"
                                            :href="item.url"
                                            :open-new-tab="item.openNewTab"
                                            :badge="item.keys || item.badge"
                                            :removable="isRecentItem(item)"
                                            @remove="removeRecentItem"
                                        >
                                            <div v-html="item.html" />
                                        </CommandPaletteItem>
                                    </ComboboxItem>
                                </ComboboxGroup>
                            </ComboboxViewport>
                            <footer class="flex items-center gap-4 rounded-b-xl border-t border-gray-200/80 bg-gray-50 px-6 py-3 dark:border-gray-950 dark:bg-gray-800/20">
                                <div class="flex items-center gap-1.5">
                                    <Icon name="up-square" class="size-4 text-gray-500" />
                                    <Icon name="down-square" class="size-4 text-gray-500" />
                                    <span class="text-sm text-gray-600 dark:text-gray-500">Navigate</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <Icon name="return-square" class="size-4 text-gray-500" />
                                    <span class="text-sm text-gray-600 dark:text-gray-500">Select</span>
                                </div>
                            </footer>
                        </ComboboxContent>
                    </ComboboxRoot>
                </motion.div>
            </DialogContent>
        </DialogPortal>
    </DialogRoot>
</template>
