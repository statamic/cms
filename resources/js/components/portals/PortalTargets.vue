<template>
    <div class="portal-targets" :class="{ 'stacks-on-stacks': hasStacks, 'solo-narrow-stack': isSoloNarrowStack }">
        <div v-for="(portal, i) in portals" :key="portal.id" :id="`portal-target-${portal.id}`" />
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
            return stacks.length === 1 && stacks[0]?.data?.vm?.size === 'narrow';
        },
    },
};
</script>
