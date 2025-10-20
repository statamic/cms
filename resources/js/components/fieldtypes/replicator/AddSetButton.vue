<template>
    <set-picker :enabled="enabled" :sets="groups" :align="variant === 'between' ? 'center' : 'start'" @added="addSet">
        <template #trigger>
            <div class="flex relative pt-2" :class="{ 'pt-6': showConnector }" v-if="variant === 'button'">
                <div v-if="showConnector" class="absolute group-hover:opacity-0 transition-opacity delay-25 duration-125 inset-y-0 h-full left-3.5 border-l-1 border-gray-400 dark:border-gray-600 border-dashed z-0 dark:bg-dark-700" />
                <Button v-if="enabled" size="sm" :text="label" icon="plus" class="relative z-2" />
            </div>
            <div
                v-if="variant === 'between'"
                class="flex justify-center py-3 relative group"
            >
                <div v-if="showConnector" class="absolute opacity-100 group-hover:opacity-0 transition-opacity delay-10 duration-250 inset-y-0 h-full left-3.5 border-l-1 border-gray-400 dark:border-gray-600 border-dashed z-0 dark:bg-dark-700" />
                <button class="w-full absolute inset-0 h-full opacity-0 group-hover:opacity-100 transition-opacity delay-10 duration-250 cursor-pointer">
                    <div class="h-full flex flex-col justify-center">
                        <div class="rounded-full bg-gray-200 dark:bg-gray-700 h-2" />
                    </div>
                </button>
                <Button v-if="enabled" round icon="plus" size="sm" class="-my-4 z-3 opacity-0 group-hover:opacity-100 transition-opacity delay-10 duration-250" />
            </div>
        </template>
    </set-picker>
</template>

<script setup>
import SetPicker from './SetPicker.vue';
import { Button } from '@/components/ui';
import { computed } from 'vue';

const emit = defineEmits(['added']);

const props = defineProps({
    sets: Array,
    groups: Array,
    index: Number,
    enabled: { type: Boolean, default: true },
    label: { type: String },
    showConnector: { type: Boolean, default: true },
    variant: { type: String, default: 'button' },
});

const label = computed(() => props.label ? __(props.label) : __('Add Block'));

function addSet(handle) {
    emit('added', handle, props.index);
}
</script>
