<script setup lang="ts">
defineOptions({ name: 'StackFooter' });
</script>

<template>
    <div
        data-ui-stack-footer
        class="bg-gray-50 p-4 border-t border-gray-200"
    >
        <slot />
    </div>
</template>
