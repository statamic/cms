<script setup>
import { Icon, Badge } from '@/components/ui';

defineProps({
    localization: {
        type: Object,
        required: true,
    },
    localizing: {
        type: [Boolean, String],
        default: false,
    },
});
</script>

<template>
    <div class="flex items-center justify-between gap-x-2">
        <div class="flex flex-1 items-center" :class="{ 'line-through opacity-50': !localization.exists }">
            <span
                class="little-dot me-2"
                :class="{
                    'bg-green-600': localization.published,
                     'bg-gray-500': !localization.published,
                     'bg-red-500': !localization.exists,
                }"
            />
            {{ __(localization.name) }}
            <Icon name="loading" class="ms-2" v-if="localizing === localization.handle" />
        </div>
        <div class="flex items-center gap-1.5">
            <Badge size="sm" color="orange" v-if="localization.origin" :text="__('Origin')" />
            <Badge size="sm" color="blue" v-if="localization.active" :text="__('Active')" />
            <Badge size="sm" color="purple" v-if="localization.root && !localization.origin && !localization.active" :text="__('Root')" />
        </div>
    </div>
</template>
