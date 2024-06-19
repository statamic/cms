<template>
    <div class="portal-targets" :class="{ 'stacks-on-stacks': hasStacks }">
        <div
            v-for="(portal, i) in portals"
            :key="portal.id"
            :id="portal.id"
        />
    </div>
</template>

<script>
import { disableBodyScroll, enableBodyScroll } from 'body-scroll-lock';

export default {

    computed: {

        portals() {
            return this.$store.state.portals.portals;
        },

        stacks() {
            return this.$store.getters['portals/stacks'];
        },

        hasStacks() {
            return this.stacks.length > 0;
        }

    },

    watch: {

        hasStacks(hasStacks) {
            hasStacks ? this.initStacks() : this.destroyStacks();
        }

    },

    methods: {

        initStacks() {
            this.$events.$on('stacks.hit-area-clicked', (depth) => {
                for (let count = this.stacks.count(); count > depth; count--) {
                    if (! this.stacks[count-1].data.vm.runCloseCallback()) {
                        return;
                    }
                }
            });

            disableBodyScroll(this.$el, {
                allowTouchMove: el => {
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
        }

    }
}
</script>
