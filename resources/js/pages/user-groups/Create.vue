<script setup>
import { Head } from '@inertiajs/vue3';
import PublishForm from '@/components/user-groups/PublishForm.vue';

const props = defineProps({
    title: {
        type: String,
        required: true,
    },
    actions: {
        type: Object,
        required: true,
    },
    values: {
        type: Object,
        required: true,
    },
    meta: {
        type: Object,
        required: true,
    },
    blueprint: {
        type: Object,
        required: true,
    },
    canEditBlueprint: {
        type: Boolean,
        default: false,
    },
    isCreating: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <div>
        <Head :title="title" />

        <PublishForm
            publish-container="base"
            :initial-title="title"
            :initial-fieldset="blueprint"
            :initial-values="values"
            :initial-meta="meta"
            :actions="actions"
            method="post"
            :can-edit-blueprint="canEditBlueprint"
            :is-creating="isCreating"
        />
    </div>
</template>
