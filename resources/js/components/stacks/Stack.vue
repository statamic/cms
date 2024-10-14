<template>

    <v-portal :to="portal" :order="depth">
        <div class="vue-portal-target stack">
            <div
                class="stack-container"
                :class="{ 'stack-is-current': isTopStack, 'hovering': isHovering, 'p-2 shadow-lg': full }"
                :style="direction === 'ltr' ? { left: `${leftOffset}px` } : { right: `${leftOffset}px` }"
            >
                <transition name="stack-overlay-fade">
                    <div
                        class="stack-overlay" v-if="visible"
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
                    <div class="stack-content" v-if="visible">
                        <slot name="default" :depth="depth" :close="close" />
                    </div>
                </transition>
            </div>
        </div>
    </v-portal>

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
        };
    },

    computed: {

        stacks() {
            // Note: we're not using the getter because that causes some weird caching to happen.
            return this.$store.state.portals.portals.filter(p => p.isStack())
        },

        portal() {
            return this.stack ? this.stack.id : null;
        },

        depth() {
            if (!this.stack) {
                return 1;
            }

            return this.stack.data.depth;
        },

        id() {
            return `${this.name}-${this.$.uid}`;
        },

        offset() {
            if (this.isTopStack && this.narrow) {
                return window.innerWidth - 400;
            } else if (this.isTopStack && this.half) {
                return window.innerWidth / 2;
            }

            // max of 200px, min of 80px
            return Math.max(400 / (this.stacks.length + 1), 80);
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
            return this.stacks.length > this.depth;
        },

        isTopStack() {
            return this.stacks.length === this.depth;
        },

        direction() {
            return this.$config.get('direction', 'ltr');
        }

    },

    async mounted() {
        this.visible = true;

        this.stack = await this.$store.dispatch('portals/createStack', {
            data: {
                runCloseCallback: this.runCloseCallback
            }
        });

        this.$events.$on(`stacks.${this.depth}.hit-area-mouseenter`, () => this.isHovering = true);
        this.$events.$on(`stacks.${this.depth}.hit-area-mouseout`, () => this.isHovering = false);
        this.escBinding = this.$keys.bindGlobal('esc', this.close);
    },

    unmounted() {
        this.$store.commit('portals/destroy', this.stack.id)

        this.$events.$off(`stacks.${this.depth}.hit-area-mouseenter`);
        this.$events.$off(`stacks.${this.depth}.hit-area-mouseout`);
        this.escBinding.destroy();
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
                this.$emit('closed');
            });
        },
    },

};
</script>
