<script setup>
import { CreateForm } from '@/components/ui';

const props = defineProps({
    route: { type: String },
    icon: { type: String },
});
</script>

<template>
    <CreateForm
        :icon
        :route
        without-handle
        :title="__('Create Blueprint')"
        :subtitle="__('messages.blueprints_intro')"
        :title-instructions="__('messages.blueprints_title_instructions')"
    />
</template>
