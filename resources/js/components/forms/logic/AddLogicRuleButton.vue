<script setup>
import LogicRulePicker from '@/components/forms/logic/LogicRulePicker.vue';
import { Button } from '@/components/ui';
import { computed } from 'vue';

const emit = defineEmits(['added']);

const props = defineProps({
    items: { type: Array, default: () => [] },
    label: String,
    showConnector: { type: Boolean, default: true },
    searchPlaceholder: String,
});

const buttonLabel = computed(() => props.label ? __(props.label) : __('Add Rule'));
</script>

<template>
    <div class="inline-block">
        <LogicRulePicker
            :items
            :search-placeholder
            @added="emit('added', $event)"
        >
            <template #trigger>
                <div class="inline-flex relative pt-2" :class="{ 'pt-6': showConnector }">
                    <div v-if="showConnector" class="absolute group-hover:opacity-0 transition-opacity delay-25 duration-125 inset-y-0 h-full left-3.5 border-l-1 border-gray-400 dark:border-gray-600 border-dashed z-0 dark:bg-gray-850" />
                    <Button size="sm" :text="buttonLabel" icon="plus" class="relative" />
                </div>
            </template>
        </LogicRulePicker>
    </div>
</template>
