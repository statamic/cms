<template>

    <portal :to="portal" :order="depth">
        <div class="stack-container"
            :style="{ zIndex: depth + 1, left: `${offset * depth}px` }"
        >
            <transition name="stack-overlay">
                <div class="stack-overlay" v-if="visible" :style="{ left: `-${offset * depth}px` }" />
            </transition>

            <div class="stack-hit-area" :style="{ left: `-${offset}px` }" @click="clickedHitArea" />

            <transition name="stack-slide">
                <div class="stack-content" v-if="visible">
                    <button @click="close" class="stack-close btn btn-sm">Close</button>
                    <slot name="default" :depth="depth" />
                </div>
            </transition>
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
            portal: null,
            visible: false
        }
    },

    computed: {

        id() {
            return `${this.name}-${this._uid}`;
        },

        offset() {
            // max of 200px, min of 80px
            return Math.max(400 / (this.$stacks.count() + 1), 80)
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
        },
    },

    mounted() {
        this.visible = true;
    }

}
</script>
