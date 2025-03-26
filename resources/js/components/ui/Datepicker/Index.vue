<script setup>
import { computed } from 'vue'
import { DatePickerContent, DatePickerField, DatePickerInput, DatePickerRoot, DatePickerTrigger } from 'reka-ui'
import { parseDateTime, parseDate } from '@internationalized/date'

const emit = defineEmits(['update:modelValue'])

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
            emit('update:modelValue', val.toString())
        }
    }
})

const minValue = computed(() => props.min ? (typeof props.min === 'string' ? parseDate(props.min) : props.min) : null)
const maxValue = computed(() => props.max ? (typeof props.max === 'string' ? parseDate(props.max) : props.max) : null)
</script>

<template>
<ui-with-field :label :description :required :badge>
    <div class="w-full relative block group/input" data-ui-input>
        <DatePickerRoot
            :modelValue="dateValue"
            :granularity="granularity"
            @update:modelValue="emit('update:modelValue', $event?.toString())"
            v-bind="$attrs"
        >
            <DatePickerField v-slot="{ segments }" class="w-full">
                <div class="
                    w-full flex bg-white dark:bg-gray-900
                    border border-gray-300 dark:border-x-0 dark:border-t-0 dark:border-white/15 dark:inset-shadow-xs dark:inset-shadow-black
                    text-gray-600 dark:text-gray-300 leading-[1.375rem]
                    shadow-ui-sm disabled:shadow-none rounded-lg ps-3 pe-10 py-2 h-10 not-prose
                    data-[invalid]:border-red-500
                ">
                    <template v-for="item in segments" :key="item.part">
                        <DatePickerInput v-if="item.part === 'literal'" :part="item.part">
                            {{ item.value }}
                        </DatePickerInput>
                        <DatePickerInput
                            v-else
                            :part="item.part"
                            class="py-0.5 px-0.25 rounded focus:outline-none focus:bg-gray-50 dark:focus:bg-gray-800 data-[placeholder]:text-gray-600 dark:data-[placeholder]:text-gray-400"
                            :class="{ 'px-0.5!': item.part === 'month' || item.part === 'year' || item.part === 'day' }"
                        >
                            {{ item.value }}
                        </DatePickerInput>
                    </template>
                </div>
                <DatePickerTrigger class="absolute text-gray-400 hover:bg-gray-50 focus:bg-gray-50 dark:hover:bg-gray-900 dark:focus:bg-gray-900 rounded-lg flex items-center justify-center end-1 px-2 top-1 bottom-1 outline-none">
                    <ui-icon name="calendar" class="w-4 h-4" />
                </DatePickerTrigger>
            </DatePickerField>

            <DatePickerContent
                :side-offset="4"
                class="will-change-[transform,opacity] data-[state=open]:data-[side=top]:animate-slideDownAndFade data-[state=open]:data-[side=right]:animate-slideLeftAndFade data-[state=open]:data-[side=bottom]:animate-slideUpAndFade data-[state=open]:data-[side=left]:animate-slideRightAndFade"
            >
            <ui-card>
                <ui-calendar
                    v-model="dateValue"
                    :minValue="minValue"
                    :maxValue="maxValue"
                />
            </ui-card>
            </DatePickerContent>
        </DatePickerRoot>
    </div>
</ui-with-field>
</template>
