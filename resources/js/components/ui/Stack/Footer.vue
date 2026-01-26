<script setup lang="ts">
import { useSlots } from 'vue';

defineOptions({ name: 'StackFooter' });

const slots = useSlots();
</script>

<template>
    <div
        data-ui-stack-footer
        class="bg-gray-50 p-4 border-t border-gray-200"
    >
        <template v-if="slots['start'] || slots['end']">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <slot name="start" />
                </div>
                <slot />
                <div class="flex items-center gap-2">
                    <slot name="end" />
                </div>
            </div>
        </template>

        <slot v-else />
    </div>
</template>
