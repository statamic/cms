<script setup>
import { Primitive } from 'reka-ui';
import { useContentDirection } from '@/composables/content-direction';

defineProps({
    /** The element or component to render as */
    as: { type: [String, Object], default: 'div' },
    /** When `true`, merges props onto the immediate child instead of rendering a wrapper element */
    asChild: { type: Boolean, default: false },
});

const { direction } = useContentDirection();
</script>

<template>
    <Primitive :as="as" :as-child="asChild" :dir="direction">
        <slot />
    </Primitive>
</template>
