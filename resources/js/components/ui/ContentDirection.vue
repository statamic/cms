<script setup>
import { useContentDirection } from '@/composables/content-direction';

const { direction } = useContentDirection();
</script>

<template>
    <div :dir="direction">
        <slot />
    </div>
</template>
