<script setup>
import { ref, computed, watch } from 'vue';
import axios from 'axios';
import Keys from '@statamic/components/keys/Keys';
import debounce from '@statamic/util/debounce';
import { DialogContent, DialogOverlay, DialogPortal, DialogRoot, DialogTitle, DialogTrigger, DialogDescription, VisuallyHidden } from 'reka-ui';
import { ComboboxContent, ComboboxEmpty, ComboboxGroup, ComboboxLabel, ComboboxInput, ComboboxItem, ComboboxRoot, ComboboxViewport } from 'reka-ui';
import fuzzysort from 'fuzzysort';
import { each, groupBy, sortBy } from 'lodash-es';
import { motion } from "motion-v"
import { cva } from 'cva';

let open = ref(false);
let query = ref('');
let items = ref(getItems());
let searchResults = ref([]);
let selected = ref(null);

let keys = new Keys;

keys.bindGlobal(['mod+k'], (e) => {
    e.preventDefault();
    open.value = true;
});

each({
    'esc': () => open.value = false,
    'ctrl+n': () => document.activeElement.dispatchEvent(new KeyboardEvent('keydown', {'key': 'ArrowDown'})),
    'ctrl+p': () => document.activeElement.dispatchEvent(new KeyboardEvent('keydown', {'key': 'ArrowUp'})),
}, (callback, binding) => {
    keys.bindGlobal([binding], (e) => {
        if (open.value) {
            e.preventDefault();
            callback();
        }
    });
});

const results = computed(() => {
    let data = searchResults.value.length
        ? items.value.concat(searchResults.value)
        : items.value;

    data = sortBy(data, ['category']);

    let filtered = fuzzysort
        .go(query.value, data, {
            all: true,
            key: 'text',
        })
        .map(result => {
            return {
                score: result._score,
                html: result.highlight('<span class="text-blue-600 dark:text-blue-400">', '</span>'),
                ...result.obj,
            };
        });

    let groups = groupBy(filtered, 'category');

    // TODO: Get category order from Categories enum in server payload?
    const categories = [
        'Actions',
        'Navigation',
        'Content Search',
    ];

    return categories
        .map((category) => {
            return {
                text: __(category),
                items: groups[category],
            };
        })
        .filter((category) => category.items);
});

watch(selected, (item) => {
    if (!item) return;
    console.log('selected:', item);
    reset();
});

watch(query, debounce(() => {
    searchContent();
}, 300));

watch(open, (isOpen) => {
    if (isOpen) return;
    reset();
})

function getItems() {
    axios.get('/cp/command-palette').then((response) => {
        items.value = response.data;
    });
}

function searchContent() {
    axios.get('/cp/command-palette/search', { params: { q: query.value } }).then((response) => {
        searchResults.value = response.data.map((result) => {
            return {
                type: 'link',
                category: 'Content Search',
                icon: 'magnifying-glass',
                text: result.title,
                url: result.url,
                badge: result.badge,
            };
        });
    });
}

function reset() {
    open.value = false;
    query.value = '';
    selected.value = null;
    searchResults.value = [];
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
            <button type="button" aria-expanded="false" class="cursor-text data-[focus-visible]:outline-focus flex items-center gap-x-2 text-xs text-gray-400 outline-none md:w-32 rounded-md md:py-[calc(5/16*1rem)] md:ps-2 md:pe-1.5 md:shadow-[0_1px_5px_-4px_rgba(19,19,22,0.4),0_2px_5px_rgba(32,42,54,0.06)] ring-1 ring-gray-900/10 hover bg-gray-900 shadow-[0_-1px_rgba(255,255,255,0.06),0_4px_8px_rgba(0,0,0,0.05),0_1px_6px_-4px_#000] hover:ring-white/10" >
                <ui-icon name="magnifying-glass" class="size-5 flex-none text-gray-600" />
                <span class="sr-only md:not-sr-only leading-none">Search</span>
                <kbd class="ml-auto hidden self-center rounded px-[0.3125rem] py-[0.0625rem] text-[0.625rem]/4 font-medium ring-1 ring-inset bg-white/5 text-gray-400 ring-white/7.5 md:block [word-spacing:-0.15em]">
                    <kbd class="font-sans">⌘ </kbd><kbd class="font-sans">K</kbd>
                </kbd>
            </button>
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
                    class="relative rounded-xl bg-white border-b border-gray-200/80 dark:border-gray-950 shadow-[0_1px_16px_-2px_rgba(63,63,71,0.2)] dark:bg-gray-800 dark:shadow-[0_10px_15px_rgba(0,0,0,.5)] dark:inset-shadow-2xs dark:inset-shadow-white/15"
                    :initial="{ scale: 1.0 }"
                    :whilePress="{ scale: 0.985 }"
                    :transition="{ duration: 0.1 }"
                >
                    <ComboboxRoot
                        :open="true"
                        :default-open="true"
                        :ignore-filter="true"
                        v-model="selected"
                    >
                        <header class="group/cmd-input flex items-center gap-2 h-14 px-5.5 border-b border-gray-200/80 dark:border-gray-950">
                            <ui-icon name="magnifying-glass" class="size-5 text-gray-400" />
                            <ComboboxInput
                                :auto-focus="true"
                                :placeholder="__('Search or jump to...')"
                                v-model="query"
                                class="flex w-full bg-transparent py-4 text-lg outline-none placeholder:text-gray-500! antialiased"
                            />
                        </header>
                        <ComboboxContent>
                            <ComboboxViewport class="min-h-[360px] max-h-[360px] divide-y divide-gray-200/80 dark:divide-gray-950 overflow-y-auto">
                                <ComboboxEmpty v-if="!results.length" class="px-3 py-2 opacity-50">
                                    <ui-command-palette-item :text="__('No results found!')" icon="entry" disabled />
                                </ComboboxEmpty>
                                <ComboboxGroup
                                    v-else
                                    v-for="category in results" :key="category"
                                    class="px-3 py-2 space-y-1"
                                >
                                    <ComboboxLabel :as-child="true">
                                        <ui-subheading size="sm" class="py-1 px-3 border-0" v-text="category.text"></ui-subheading>
                                    </ComboboxLabel>
                                    <ComboboxItem
                                        v-for="item in category.items"
                                        :key="item.text"
                                        :value="item.text"
                                        :text-value="item.text"
                                        :as-child="true"
                                    >
                                        <ui-command-palette-item :icon="item.icon" :badge="item.keys || item.badge">
                                            <div v-html="item.html" />
                                        </ui-command-palette-item>
                                    </ComboboxItem>
                                </ComboboxGroup>
                            </ComboboxViewport>
                            <footer class="bg-gray-50 dark:bg-gray-900 rounded-b-xl px-6 py-3 flex items-center gap-4 border-t border-gray-200/80 dark:border-gray-950">
                                <div class="flex items-center gap-1.5">
                                    <ui-icon name="up-square" class="size-4 text-gray-500" />
                                    <ui-icon name="down-square" class="size-4 text-gray-500" />
                                    <span class="text-sm text-gray-600">Navigate</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <ui-icon name="return-square" class="size-4 text-gray-500" />
                                    <span class="text-sm text-gray-600">Select</span>
                                </div>
                            </footer>
                        </ComboboxContent>
                    </ComboboxRoot>
                </motion.div>
            </DialogContent>
        </DialogPortal>
    </DialogRoot>
</template>
