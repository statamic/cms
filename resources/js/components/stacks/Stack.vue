<template>

    <portal :to="portal" :order="depth">
        <div class="stack-container"
            :class="{ 'stack-is-current': isTopStack, 'hovering': isHovering, 'p-1 shadow-lg': full }"
            :style="{ zIndex: (depth + 1) * 1000, left: `${leftOffset}px` }"
        >
            <transition name="stack-overlay-fade">
                <div class="stack-overlay" v-if="visible" :style="{ left: `-${leftOffset}px` }" />
            </transition>

            <div class="stack-hit-area" :style="{ left: `-${offset}px` }" @click="clickedHitArea" @mouseenter="mouseEnterHitArea" @mouseout="mouseOutHitArea" />

            <transition name="stack-slide">
                <div class="stack-content" v-if="visible">
                    <slot name="default" :depth="depth" :close="close" />
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
        },
        narrow: {
            type: Boolean
        },
        half: {
            type: Boolean
        },
        full: {
            type: Boolean
        },
    },

    data() {
        return {
            stack: null,
            visible: false,
            isHovering: false,
            escBinding: null,
        }
    },

    computed: {

        portal() {
            return this.stack ? this.stack.key : null;
        },

        depth() {
            return this.stack.depth;
        },

        id() {
            return `${this.name}-${this._uid}`;
        },

        offset() {
            if (this.isTopStack && this.narrow) {
                return window.innerWidth - 400;
            } else if (this.isTopStack && this.half) {
                return window.innerWidth/ 2 ;
            }

            // max of 200px, min of 80px
            return Math.max(400 / (this.$stacks.count() + 1), 80)
        },

        leftOffset() {
            if (this.full) {
                return 0;
            }

            if (this.isTopStack && (this.narrow || this.half)) {
                return this.offset;
            }

            return this.offset * this.depth;
        },

        hasChild() {
            return this.$stacks.count() > this.depth;
        },

        isTopStack() {
            return this.$stacks.count() === this.depth;
        }

    },

    created() {
        this.stack = this.$stacks.add(this);

        this.$events.$on(`stacks.${this.depth}.hit-area-mouseenter`, () => this.isHovering = true);
        this.$events.$on(`stacks.${this.depth}.hit-area-mouseout`, () => this.isHovering = false);
        this.escBinding = this.$keys.bindGlobal('esc', this.close);
    },

    destroyed() {
        this.$stacks.remove(this);
        this.$events.$off(`stacks.${this.depth}.hit-area-mouseenter`);
        this.$events.$off(`stacks.${this.depth}.hit-area-mouseout`);
        this.escBinding.destroy();
    },

    methods: {

        clickedHitArea() {
            this.$events.$emit(`stacks.hit-area-clicked`, this.depth - 1);
        },

        mouseEnterHitArea() {
            this.$events.$emit(`stacks.${this.depth - 1}.hit-area-mouseenter`);
        },

        mouseOutHitArea() {
            this.$events.$emit(`stacks.${this.depth - 1}.hit-area-mouseout`);

        },

        runCloseCallback() {
            const shouldClose = this.beforeClose();

            if (! shouldClose) return false;

            this.close();

            return true;
        },

        close() {
            this.visible = false;
            this.$wait(300).then(() => { this.$emit('closed') });
        },
    },

    mounted() {
        this.visible = true;
    },



}
</script>
