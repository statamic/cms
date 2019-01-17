<template>

    <portal :to="portal" :order="depth">
        <div class="stack-container"
            :class="{ 'stack-is-current': isTopStack }"
            :style="{ zIndex: (depth + 1) * 1000, left: `${offset * depth}px` }"
        >
            <transition name="stack-overlay">
                <div class="stack-overlay" v-if="visible" :style="{ left: `-${offset * depth}px` }" />
            </transition>

            <div class="stack-hit-area" :style="{ left: `-${offset}px` }" @click="clickedHitArea" />

            <transition name="stack-slide">
                <div class="stack-content" v-if="visible">
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
        },
        beforeClose: {
            type: Function,
            default: () => true
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
        },

        isTopStack() {
            return this.$stacks.count() === this.depth;
        }

    },

    created() {
        this.depth = this.$stacks.count() + 1;
        this.portal = `stack-${this.depth-1}`;
        this.$stacks.add(this);
    },

    destroyed() {
        this.$stacks.remove(this);
    },

    render() {
        return this.$scopedSlots.default({ })
    },

    methods: {

        clickedHitArea() {
            this.$events.$emit(`stacks.hit-area-clicked`, this.depth - 1);
        },

        runCloseCallback() {
            const shouldClose = this.beforeClose();

            if (! shouldClose) return false;

            this.close();

            return true;
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
