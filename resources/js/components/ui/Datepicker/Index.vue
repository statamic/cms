<script setup>
import { computed } from 'vue';
import { DatePickerContent, DatePickerField, DatePickerInput, DatePickerRoot, DatePickerTrigger } from 'reka-ui';
import { parseDateTime, parseDate } from '@internationalized/date';

const emit = defineEmits(['update:modelValue']);

const props = defineProps({
    date: { type: String, default: null },
    badge: { type: String, default: null },
    description: { type: String, default: null },
    label: { type: String, default: null },
    required: { type: Boolean, default: false },
    modelValue: { type: String, default: null },
    min: { type: [String, Object], default: null },
    max: { type: [String, Object], default: null },
    granularity: { type: String, default: 'day' },
});

const dateValue = computed({
    get: () => {
        if (!props.modelValue) return null;

        // Check if modelValue contains a timestamp (has a 'T' character)
        if (props.modelValue.includes('T')) {
            return parseDateTime(props.modelValue);
        } else {
            return parseDate(props.modelValue);
        }
    },
    set: (val) => {
        if (val) {
            emit('update:modelValue', val.toString());
        }
    },
});

const minValue = computed(() =>
    props.min ? (typeof props.min === 'string' ? parseDate(props.min) : props.min) : null,
);
const maxValue = computed(() =>
    props.max ? (typeof props.max === 'string' ? parseDate(props.max) : props.max) : null,
);
</script>

<template>
    <ui-with-field :label :description :required :badge>
        <div class="group/input relative block w-full" data-ui-input>
            <DatePickerRoot
                :modelValue="dateValue"
                :granularity="granularity"
                @update:modelValue="emit('update:modelValue', $event?.toString())"
                v-bind="$attrs"
            >
                <DatePickerField v-slot="{ segments }" class="w-full">
                    <div
                        :class="[
                            'flex w-full bg-white dark:bg-gray-900',
                            'border border-gray-300 dark:border-x-0 dark:border-t-0 dark:border-white/15 dark:inset-shadow-2xs dark:inset-shadow-black',
                            'leading-[1.375rem] text-gray-600 dark:text-gray-300',
                            'shadow-ui-sm not-prose h-10 rounded-lg py-2 ps-3 pe-10 disabled:shadow-none',
                            'data-invalid:border-red-500',
                        ]"
                    >
                        <template v-for="item in segments" :key="item.part">
                            <DatePickerInput v-if="item.part === 'literal'" :part="item.part">
                                {{ item.value }}
                            </DatePickerInput>
                            <DatePickerInput
                                v-else
                                :part="item.part"
                                class="rounded-sm px-0.25 py-0.5 focus:bg-gray-50 focus:outline-hidden data-placeholder:text-gray-600 dark:focus:bg-gray-800 dark:data-placeholder:text-gray-400"
                                :class="{
                                    'px-0.5!': item.part === 'month' || item.part === 'year' || item.part === 'day',
                                }"
                            >
                                {{ item.value }}
                            </DatePickerInput>
                        </template>
                    </div>
                    <DatePickerTrigger
                        class="absolute end-1 top-1 bottom-1 flex items-center justify-center rounded-lg px-2 text-gray-400 outline-hidden hover:bg-gray-50 focus:bg-gray-50 dark:hover:bg-gray-900 dark:focus:bg-gray-900"
                    >
                        <ui-icon name="calendar" class="h-4 w-4" />
                    </DatePickerTrigger>
                </DatePickerField>

                <DatePickerContent
                    :side-offset="4"
                    class="data-[state=open]:data-[side=top]:animate-slideDownAndFade data-[state=open]:data-[side=right]:animate-slideLeftAndFade data-[state=open]:data-[side=bottom]:animate-slideUpAndFade data-[state=open]:data-[side=left]:animate-slideRightAndFade will-change-[transform,opacity]"
                >
                    <ui-card>
                        <ui-calendar v-model="dateValue" :minValue="minValue" :maxValue="maxValue" />
                    </ui-card>
                </DatePickerContent>
            </DatePickerRoot>
        </div>
    </ui-with-field>
</template>
