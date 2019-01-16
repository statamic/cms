<template>

    <portal :to="portal" :order="depth">
        <div class="stack-container"
            :style="{ zIndex: depth + 1, marginLeft: `${offset * depth}px` }"
        >
            <div class="stack-overlay" :style="{ marginLeft: `-${offset * depth}px` }" />
            <div class="stack-hit-area" :style="{ marginLeft: `-${offset}px` }" @click="clickedHitArea" />

            <div class="stack-content">
                <button @click="close" class="stack-close btn btn-sm">Close</button>
                <slot name="default" :depth="depth" />
            </div>
        </div>
    </portal>

</template>

<script>
export default {

    props: {
        name: {
            type: String,
            required: true
        }
    },

    data() {
        return {
            depth: null,
            portal: null
        }
    },

    computed: {

        id() {
            return `${this.name}-${this._uid}`;
        },

        offset() {
            return 200; // should change based on number of stacks
        },

        hasChild() {
            return this.$stacks.count() > this.depth;
        }

    },

    created() {
        this.depth = this.$stacks.count() + 1;
        this.portal = `stack-${this.depth-1}`;
        this.$stacks.add(this.id);

        this.$events.$on(`stacks.${this.depth}.hit-area-clicked`, () => {
            console.log(`hit area for stack ${this.depth} clicked. everything above will close.`);
        });
    },

    destroyed() {
        this.$stacks.remove(this.id);
    },

    render() {
        return this.$scopedSlots.default({ })
    },

    methods: {

        clickedHitArea() {
            this.$events.$emit(`stacks.${this.depth - 1}.hit-area-clicked`);
        },

        close() {
            this.$emit('closed');
        }

    }

}
</script>
