<template>
    <div class="stacks-on-stacks">
        <portal-target
            v-for="(stack, i) in stacks"
            :key="`stack-${stack}-${i}`"
            :name="`stack-${i}`"
        />
    </div>
</template>

<script>

import { disableBodyScroll, enableBodyScroll, clearAllBodyScrollLocks } from 'body-scroll-lock';

export default {

    computed: {

        stacks() {
            return this.$root.stacks;
        }

    },

    created() {
        this.$events.$on('stacks.hit-area-clicked', (depth) => {
            for (let count = this.$stacks.count(); count > depth; count--) {
                if (! this.$stacks.stacks[count-1].runCloseCallback()) {
                    return;
                }
            }
        });

        disableBodyScroll(this.$el);
    },

    destroyed() {
        this.$events.$off('stacks.hit-area-clicked');
        enableBodyScroll(this.$el);
        clearAllBodyScrollLocks();
    }
}
</script>
