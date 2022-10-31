<template>
    <div :class="{ 'stacks-on-stacks': hasStacks }">
        <portal-target
            v-for="(portal) in portals"
            :key="portal.key"
            :name="portal.key"
        />
    </div>
</template>

<script>
import { disableBodyScroll, enableBodyScroll } from 'body-scroll-lock';

export default {

    computed: {

        portals() {
            return this.$root.portals;
        },

        hasStacks() {
            return this.$stacks.count() > 0;
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
                for (let count = this.$stacks.count(); count > depth; count--) {
                    if (! this.$stacks.stacks()[count-1].vm.runCloseCallback()) {
                        return;
                    }
                }
            });

            disableBodyScroll(this.$el);
        },

        destroyStacks() {
            this.$events.$off('stacks.hit-area-clicked');
            enableBodyScroll(this.$el);
        }

    }
}
</script>
