<template>
    <div class="portal-targets" :class="{ 'stacks-on-stacks': hasStacks, 'solo-narrow-stack': isSoloNarrowStack, 'modals-on-modals': hasModals }">
        <div v-for="(portal, i) in portals" :id="`portal-target-${portal.id}`" />
    </div>
</template>

<script>
import { disableBodyScroll, enableBodyScroll } from 'body-scroll-lock';

export default {
    computed: {
        portals() {
            return this.$portals.all();
        },

        hasStacks() {
            return this.$stacks.count() > 0;
        },

        hasModals() {
            return this.$modals.count() > 0;
        },

        isSoloNarrowStack() {
            const stacks = this.$stacks.stacks();
            return stacks.length === 1 && stacks[0]?.data?.vm?.narrow === true;
        },
    },

    watch: {
        hasStacks(hasStacks) {
            hasStacks ? this.initStacks() : this.destroyStacks();
        },

        hasModals(hasModals) {
            hasModals ? this.initModals() : this.destroyModals();
        },
    },

    methods: {
        initStacks() {
            this.$events.$on('stacks.hit-area-clicked', (depth) => {
                for (let count = this.$stacks.count(); count > depth; count--) {
                    if (!this.$stacks.stacks()[count - 1].data.vm.runCloseCallback()) {
                        return;
                    }
                }
            });

            disableBodyScroll(this.$el, {
                allowTouchMove: (el) => {
                    while (el && el !== document.body) {
                        if (el.classList.contains('overflow-scroll')) {
                            return true;
                        }
                        el = el.parentElement;
                    }
                },
            });
        },

        destroyStacks() {
            this.$events.$off('stacks.hit-area-clicked');
            enableBodyScroll(this.$el);
        },

        initModals() {
            disableBodyScroll(this.$el, {
                allowTouchMove: (el) => {
                    while (el && el !== document.body) {
                        if (el.classList.contains('overflow-scroll')) {
                            return true;
                        }
                        el = el.parentElement;
                    }
                },
            });
        },

        destroyModals() {
            enableBodyScroll(this.$el);
        },
    },
};
</script>
