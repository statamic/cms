<script setup>
defineProps({
    heading: { type: String, required: true },
    description: { type: String, default: null },
});
</script>

<template>
    <div class="flex items-start gap-2.5">
        <div class="flex flex-col items-start gap-3">
            <div>
                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                    {{ heading }}
                </p>

                <p
                    v-if="description"
                    class="mt-1 text-sm text-gray-500 dark:text-gray-400"
                >
                    {{ description }}
                </p>
            </div>

            <div v-if="$slots.default">
                <slot />
            </div>
        </div>
    </div>
</template>
