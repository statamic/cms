<template>
    <div>
        <div v-for="(block, i) in blocks" :key="i" class="p-4">
            <pre
                class="whitespace-pre-wrap font-mono text-xs leading-normal"
                v-if="block.type === 'text'"
                v-text="block.text"
            />

            <div
                class="rounded border border-dashed p-2 text-xs"
                v-if="block.type === 'set'"
                v-text="setDisplay(block.set)"
            />
        </div>
    </div>
</template>

<script>
import pretty from 'pretty';

export default {
    props: ['html'],

    inject: ['bard'],

    computed: {
        blocks() {
            return this.html.split(/(<bard-set>.*?<\/bard-set>)/).map((text) => {
                if (text.startsWith('<bard-set>')) {
                    let json = text.match(/^<bard-set>(.*)<\/bard-set>$/)[1];
                    return { type: 'set', set: JSON.parse(json).values.type };
                }
                return { type: 'text', text: pretty(text) };
            });
        },
    },

    methods: {
        setDisplay(handle) {
            const set = _.findWhere(this.bard.setConfigs, { handle });
            return set.display || handle;
        },
    },
};
</script>
