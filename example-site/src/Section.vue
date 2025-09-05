<script setup>
import Heading from './Heading.vue'
defineProps(['title'])
</script>

<template>
    <div class="border-b border-zinc-300 pb-6">
        <Heading :text="title" />
        <slot />
    </div>
</template>
