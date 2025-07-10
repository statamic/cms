<script setup>
import { TimeFieldInput, TimeFieldRoot } from 'reka-ui';
import { Button } from '@statamic/ui';

const emit = defineEmits(['update:modelValue']);

const props = defineProps({
    badge: { type: String, default: null },
    required: { type: Boolean, default: false },
    modelValue: { type: String, default: null },
    granularity: { type: String, default: null },
    clearable: { type: Boolean, default: true },
});

const setToNow = () => {
    const date = new Date();
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    const seconds = String(date.getSeconds()).padStart(2, '0');
    emit(
        'update:modelValue',
        props.granularity === 'second' ? `${hours}:${minutes}:${seconds}` : `${hours}:${minutes}`,
    );
};
</script>

<template>
    <TimeFieldRoot
        :model-value="modelValue"
        @update:model-value="emit('update:modelValue', $event)"
        v-slot="{ segments }"
        :granularity="granularity"
        :class="[
            'flex w-full bg-white dark:bg-gray-900',
            'border border-gray-300 dark:border-x-0 dark:border-t-0 dark:border-white/15 dark:inset-shadow-2xs dark:inset-shadow-black',
            'leading-5 text-gray-600 dark:text-gray-300',
            'shadow-ui-sm not-prose h-10 rounded-lg py-2 px-3 disabled:shadow-none',
            'data-invalid:border-red-500',
        ]"
    >
        <div class="flex-1 flex items-center">
            <template v-for="item in segments" :key="item.part">
                <TimeFieldInput v-if="item.part === 'literal'" :part="item.part">{{ item.value }}</TimeFieldInput>
                <TimeFieldInput
                    v-else
                    :part="item.part"
                    class="rounded-sm px-0.25 py-0.5 focus:bg-gray-100 focus:outline-hidden data-placeholder:text-gray-600 dark:focus:bg-gray-800 dark:data-placeholder:text-gray-400"
                >
                    {{ item.value }}
                </TimeFieldInput>
            </template>
        </div>
        <div class="flex items-center gap-1">
            <Button v-if="clearable" @click="setToNow" type="button" class="" size="xs" v-tooltip="__('Set to now')" icon="time-now" />
            <Button v-if="clearable" @click="emit('update:modelValue', null)" type="button" class="" v-tooltip="__('Clear')" icon="x" size="xs" />
        </div>
    </TimeFieldRoot>

</template>
