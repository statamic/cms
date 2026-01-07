<script setup lang="ts">
defineOptions({ name: 'StackContent' });

withDefaults(defineProps<{
    /** When `true`, the internal padding of the stack content is removed. */
    inset?: boolean;
}>(), {
    inset: false,
});
</script>

<template>
    <div
        class="flex-1 overflow-auto"
        :class="{ 'p-6': !inset }"
        data-ui-stack-content
    >
        <slot />
    </div>
</template>
