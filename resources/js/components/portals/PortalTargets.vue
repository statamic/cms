<template>
    <div class="portal-targets" :class="{ 'stacks-on-stacks': hasStacks }">
        <component
            :is="portal.data?.type === 'stack' ? 'portal-target' : 'div'"
            v-for="(portal, i) in portals"
            :key="portal.id"
            :name="portal.id"
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
            // Note: we're not using the getter because that causes some weird caching to happen.
            return this.$store.state.portals.portals.filter(p => p.isStack())
        },
        hasStacks() {
            return this.stacks.length > 0;
        }
    },
    watch: {
        hasStacks: {
            deep: true,
            handler(hasStacks) {
                console.log('hassstack', hasStacks);
                hasStacks ? this.initStacks() : this.destroyStacks();
            }
        }
    },
    methods: {
        initStacks() {
            this.$events.$on('stacks.hit-area-clicked', (depth) => {
                for (let count = this.stacks.length; count > depth; count--) {
                    if (! this.stacks[count-1].data.runCloseCallback()) {
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
