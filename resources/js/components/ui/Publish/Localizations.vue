<script setup>
import { Badge, Card, Panel, Icon } from '@statamic/ui';

defineProps({
    localizations: {
        type: Array,
        required: true,
    },
    localizing: {
        type: [Boolean, String],
        default: false,
    },
});

defineEmits(['selected']);
</script>

<template>
    <Panel v-if="localizations.length > 1" :heading="__('Sites')">
        <Card class="p-3! space-y-1">
            <button
                v-for="option in localizations"
                :key="option.handle"
                class="flex w-full cursor-pointer items-center px-4 py-2 text-sm rounded-lg"
                :class="option.active ? 'dark:bg-gray-700 bg-blue-100' : 'dark:hover:bg-gray-800 hover:bg-gray-100'"
                @click="$emit('selected', option)"
            >
                <div class="flex flex-1 items-center" :class="{ 'line-through opacity-50': !option.exists }">
                    <span
                        class="little-dot me-2"
                        :class="{
                            'bg-green-600': option.published,
                            'bg-gray-500': !option.published,
                            'bg-red-500': !option.exists,
                        }"
                    />
                    {{ __(option.name) }}
                    <Icon name="loading" class="ms-2" v-if="localizing === option.handle" />
                </div>
                <div class="flex items-center gap-1.5">
                    <Badge size="sm" color="orange" v-if="option.origin" :text="__('Origin')" />
                    <Badge size="sm" color="blue" v-if="option.active" :text="__('Active')" />
                    <Badge size="sm" color="purple" v-if="option.root && !option.origin && !option.active" :text="__('Root')" />
                </div>
            </button>
        </Card>
    </Panel>
</template>
