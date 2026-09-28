<template>
    <div class="portal-targets" :class="{ 'stacks-on-stacks': hasStacks, 'stack-entering': isStackEntering, 'solo-narrow-stack': isSoloNarrowStack }">
        <div v-for="(portal, i) in portals" :key="portal.id" :id="`portal-target-${portal.id}`" />
    </div>
</template>

<script>
import { events } from '@/api';

export default {
    data() {
        return {
            isStackEntering: false,
            stackEnteringTimeout: null,
        };
    },

    computed: {
        portals() {
            return this.$portals.all();
        },

        stackCount() {
            return this.$stacks.count();
        },

        hasStacks() {
            return this.stackCount > 0;
        },

        isSoloNarrowStack() {
            const stacks = this.$stacks.stacks();
            return stacks.length === 1 && stacks[0]?.data?.vm?.size === 'narrow';
        },
    },

    watch: {
        stackCount(newCount, oldCount) {
            if (newCount <= oldCount) {
                return;
            }

            clearTimeout(this.stackEnteringTimeout);
            this.isStackEntering = true;
            events.$emit('stacks.entering', true);

            // Match the stack enter transition so CSS can ignore hover effects while a new stack slides in.
            this.stackEnteringTimeout = setTimeout(() => {
                this.isStackEntering = false;
                this.stackEnteringTimeout = null;
                events.$emit('stacks.entering', false);
            }, 200);
        },
    },

    beforeUnmount() {
        clearTimeout(this.stackEnteringTimeout);
        events.$emit('stacks.entering', false);
    },
};
</script>
