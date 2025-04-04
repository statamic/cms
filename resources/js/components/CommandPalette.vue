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

<script>
import fuzzysort from 'fuzzysort';
import { groupBy, sortBy } from 'lodash-es';

export default {

    props: [
        'initialData',
    ],

    data() {
        return {
            data: this.initialData,
            open: true,
            query: '',
        };
    },

    computed: {
        results() {
            let data = sortBy(this.data, 'category');

            let results = fuzzysort
                .go(this.query, data, {
                    all: true,
                    key: 'text',
                })
                .map(result => {
                    return {
                        html: result.highlight('<span class="text-red-500">', '</span>'),
                        ...result.obj,
                    }
                });

            results = groupBy(results, 'category');

            return results;
        },
    },

};
</script>
