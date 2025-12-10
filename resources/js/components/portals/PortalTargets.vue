<template>
    <div class="portal-targets" :class="{ 'stacks-on-stacks': hasStacks, 'solo-narrow-stack': isSoloNarrowStack }">
        <div v-for="(portal, i) in portals" :id="`portal-target-${portal.id}`" />
    </div>
</template>

<script>
export default {
    computed: {
        portals() {
            return this.$portals.all();
        },

        hasStacks() {
            return this.$stacks.count() > 0;
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
        },

        destroyStacks() {
            this.$events.$off('stacks.hit-area-clicked');
        },
    },
};
</script>
