<script setup>
import { ref, computed } from 'vue';
import fuzzysort from 'fuzzysort';
import { groupBy, sortBy } from 'lodash-es';

const props = defineProps({
    initialData: { type: Object },
});

let open = ref(true);
let query = ref('');

const results = computed(() => {
    let data = sortBy(props.initialData, ['category', 'text']);

    let results = fuzzysort
        .go(query.value, data, {
            all: true,
            key: 'text',
        })
        .map(result => {
            return {
                score: result._score,
                html: result.highlight('<span class="text-red-500">', '</span>'),
                ...result.obj,
            }
        });

    results = groupBy(results, 'category');

    // TODO: Sort by [category, score]

    return results;
})
</script>

<template>
    <modal v-if="open" name="command-palette" :width="580" @closed="open = false" click-to-close>
        <input
            :placeholder="__('Type a command or search...')"
            v-model="query"
            class="w-full p-4 text-xl border-b focus:outline-none"
        />
        <div>
            <div v-for="(categoryResults, category) in results" :key="category" class="p-4 border-b">
                <p class="text-lg pb-2">{{ category }}</p>
                <p v-for="result in categoryResults" :key="result.text" class="pb-1" v-html="result.html"></p>
            </div>
        </div>
    </modal>
</template>
