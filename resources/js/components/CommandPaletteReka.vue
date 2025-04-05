<script setup>
import { ref, computed, watch, nextTick } from 'vue';
import fuzzysort from 'fuzzysort';
import { groupBy, sortBy } from 'lodash-es';
import Keys from '@statamic/components/keys/Keys';
import {
    ComboboxContent,
    ComboboxEmpty,
    ComboboxGroup,
    ComboboxInput,
    ComboboxItem,
    ComboboxRoot,
    ComboboxViewport,
    // ComboboxVirtualizer,
    // ComboboxSeparator,
} from 'reka-ui';

const props = defineProps({
    initialData: { type: Object },
});

let open = ref(false);
let query = ref('');
let selected = ref(null);

function reset() {
    open.value = false;
    query.value = '';
    selected.value = null;
}

new Keys().bindGlobal(['mod+k'], (e) => {
    e.preventDefault();
    open.value = true;
});

const results = computed(() => {
    let data = sortBy(props.initialData, ['category', 'text']);

    let filtered = fuzzysort
        .go(query.value, data, {
            all: true,
            key: 'text',
        })
        .map(result => {
            return {
                score: result._score,
                html: result.highlight('<span class="text-red-500">', '</span>'),
                ...result.obj,
            };
        });

    let groups = groupBy(filtered, 'category');

    // TODO: Send order in payload from server via Categories enum?
    const categories = [
        'Actions',
        'Navigation',
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
</script>

<template>
    <modal
        v-if="open"
        name="command-palette"
        :width="700"
        @closed="open = false"
        click-to-close
    >
        <ComboboxRoot
            :open="true"
            :default-open="true"
            :ignore-filter="true"
            v-model="selected"
        >
            <ComboboxInput
                :auto-focus="true"
                placeholder="Search..."
                class="w-full border-b p-4 text-xl focus:outline-none"
                v-model="query"
            />
            <ComboboxContent :collision-padding="20">
                <ComboboxViewport class="min-h-[350px] max-h-[350px]">
                    <ComboboxEmpty class="text-sm p-4">
                        No results!
                    </ComboboxEmpty>
                    <ComboboxGroup v-for="category in results" :key="category" class="border-b last:border-0 p-2 pb-1">
                        <p class="text-sm p-2 pb-5" v-text="category.text" />
                        <ComboboxItem
                            v-for="item in category.items"
                            :as-child="true"
                            :key="item.text"
                            :value="item.text"
                            :text-value="item.text"
                        >
                            <div class="group py-2 -mt-4">
                                <div v-html="item.html" class="group-[&[data-highlighted]]:bg-gray-400 px-2 py-1" />
                            </div>
                        </ComboboxItem>
                    </ComboboxGroup>
                </ComboboxViewport>
                <div class="w-full border-t px-4 py-3">
                    Footer!
                </div>
            </ComboboxContent>
        </ComboboxRoot>
    </modal>
</template>
