<template>

    <div>
        <div
            v-for="(block, i) in blocks"
            :key="i"
            class="p-2"
        >
            <pre
                class="whitespace-pre-wrap leading-normal text-xs font-mono"
                v-if="block.type === 'text'"
                v-text="block.text" />

            <div
                class="border border-dashed p-1 rounded text-xs"
                v-if="block.type === 'set'"
                v-text="setDisplay(block.set)" />
        </div>
    </div>

</template>

<script>
const pretty = require('pretty');

export default {

    props: ['html'],

    inject: ['setConfigs'],

    computed: {

        blocks() {
            return this.html
                .split(/(<bard-set>.*?<\/bard-set>)/)
                .map(text => {
                    if (text.startsWith('<bard-set>')) {
                        let json = text.match(/^<bard-set>(.*)<\/bard-set>$/)[1];
                        return { type: 'set', set: JSON.parse(json).values.type };
                    }
                    return { type: 'text', text: pretty(text) };
                });
        }

    },

    methods: {

        setDisplay(handle) {
            const set = _.findWhere(this.setConfigs, { handle });
            return set.display || handle;
        }

    }

}
</script>
