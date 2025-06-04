<script setup>
defineProps({
    title: { type: String, required: true },
    subtitle: { type: String, default: null },
    icon: { type: String, default: null },
    submitText: { type: String, default: null },
    loading: { type: Boolean, default: false },
});

const emit = defineEmits(['submit']);
</script>

<template>
    <div class="mx-auto mt-4 space-y-3 lg:space-y-6 max-w-3xl">
        <header v-if="title || subtitle" class="text-center max-w-xl mx-auto py-6 lg:pt-12 xl:pt-16">
            <ui-heading v-if="title" size="2xl" :level="1" :icon="icon" :text="title" class="justify-center" />
            <ui-subheading v-if="subtitle" class="mt-6" size="lg" :text="subtitle" />
        </header>

        <slot />

        <slot name="footer">
            <footer class="flex justify-center">
                <ui-button
                    variant="primary"
                    size="lg"
                    @click="$emit('submit')"
                    :loading="loading"
                >
                    {{ submitText || title }}
                </ui-button>
            </footer>
        </slot>
    </div>
</template>
