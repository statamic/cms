<template>
    <teleport :to="portal" :order="depth" v-if="mounted">
        <div class="vue-portal-target stack">
            <div
                class="stack-container"
                :class="{ 'stack-is-current': isTopStack }"
                :style="direction === 'ltr' ? { left: `${leftOffset}px` } : { right: `${leftOffset}px` }"
            >
                <transition name="stack-overlay-fade">
                    <div
                        v-if="visible"
                        class="stack-overlay fixed inset-0 bg-gray-800/20 dark:bg-gray-800/50 backdrop-blur-[2px]"
                        :style="direction === 'ltr' ? { left: `-${leftOffset}px` } : { right: `-${leftOffset}px` }"
                    />
                </transition>

                <div
                    class="stack-hit-area"
                    :style="direction === 'ltr' ? { left: `-${offset}px` } : { right: `-${offset}px` }"
                    @click="clickedHitArea"
                    @mouseenter="mouseEnterHitArea"
                    @mouseout="mouseOutHitArea"
                />

                <transition name="stack-slide">
                    <div
                        v-if="visible"
                        class="stack-content fixed flex flex-col sm:end-1.5 overflow-auto bg-white dark:bg-gray-850 rounded-xl shadow-[0_8px_5px_-6px_rgba(0,0,0,0.1),_0_3px_8px_0_rgba(0,0,0,0.02),_0_30px_22px_-22px_rgba(39,39,42,0.15)] dark:shadow-[0_5px_20px_rgba(0,0,0,.5)] transition-transform duration-150 ease-out"
                        :class="[
                            full ? 'inset-2 w-[calc(100svw-1rem)]' : 'inset-y-2',
                            { '-translate-x-4 rtl:translate-x-4': isHovering }
                        ]"
                    >
                        <slot name="default" :depth="depth" :close="close" />
                    </div>
                </transition>
            </div>
        </div>
    </teleport>
</template>

<script>
export default {
    emits: ['closed', 'opened'],

    props: {
        name: {
            type: String,
            required: true,
        },
        beforeClose: {
            type: Function,
            default: () => true,
        },
        narrow: {
            type: Boolean,
        },
        half: {
            type: Boolean,
        },
        full: {
            type: Boolean,
        },
    },

    data() {
        return {
            stack: null,
            mounted: false,
            visible: false,
            isHovering: false,
            escBinding: null,
            windowInnerWidth: window.innerWidth,
        };
    },

    computed: {
        portal() {
            return this.stack ? `#portal-target-${this.stack.id}` : null;
        },

        depth() {
            return this.stack.data.depth;
        },

        id() {
            return `${this.name}-${this.$.uid}`;
        },

        offset() {
            if (this.isTopStack && this.narrow) {
                return this.windowInnerWidth - 450;
            } else if (this.isTopStack && this.half) {
                return this.windowInnerWidth / 2;
            }

            // max of 200px, min of 80px
            return Math.max(450 / (this.$stacks.count() + 1), 80);
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
        },

        direction() {
            return this.$config.get('direction', 'ltr');
        },
    },

    created() {
        this.stack = this.$stacks.add(this);

        this.$events.$on(`stacks.${this.depth}.hit-area-mouseenter`, () => (this.isHovering = true));
        this.$events.$on(`stacks.${this.depth}.hit-area-mouseout`, () => (this.isHovering = false));
        this.escBinding = this.$keys.bindGlobal('esc', this.close);

        window.addEventListener('resize', this.handleResize);
    },

    unmounted() {
        this.stack.destroy();
        this.$events.$off(`stacks.${this.depth}.hit-area-mouseenter`);
        this.$events.$off(`stacks.${this.depth}.hit-area-mouseout`);
        this.escBinding.destroy();

        window.removeEventListener('resize', this.handleResize);
    },

    methods: {
        clickedHitArea() {
            if (!this.visible) {
                return;
            }
            this.$events.$emit(`stacks.hit-area-clicked`, this.depth - 1);
            this.$events.$emit(`stacks.${this.depth - 1}.hit-area-mouseout`);
        },

        mouseEnterHitArea() {
            if (!this.visible) {
                return;
            }
            this.$events.$emit(`stacks.${this.depth - 1}.hit-area-mouseenter`);
        },

        mouseOutHitArea() {
            if (!this.visible) {
                return;
            }
            this.$events.$emit(`stacks.${this.depth - 1}.hit-area-mouseout`);
        },

        runCloseCallback() {
            const shouldClose = this.beforeClose();

            if (!shouldClose) return false;

            this.close();

            return true;
        },

        close() {
            this.visible = false;
            this.$wait(300).then(() => {
                this.mounted = false;
                this.$emit('closed');
            });
        },

        handleResize() {
            this.windowInnerWidth = window.innerWidth;
        },
    },

    mounted() {
        this.mounted = true;
        this.$nextTick(() => {
            this.visible = true;
            this.$emit('opened');
        });
    },
};
</script>
