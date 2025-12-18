<script setup>
import { computed } from 'vue';
import {
    DateRangePickerCalendar,
    DateRangePickerCell,
    DateRangePickerCellTrigger,
    DateRangePickerGrid,
    DateRangePickerGridBody,
    DateRangePickerGridHead,
    DateRangePickerGridRow,
    DateRangePickerHeadCell,
    DateRangePickerHeader,
    DateRangePickerHeading,
    DateRangePickerNext,
    DateRangePickerPrev,
    DateRangePickerContent,
    DateRangePickerField,
    DateRangePickerInput,
    DateRangePickerRoot,
    DateRangePickerTrigger,
} from 'reka-ui';
import Card from '../Card/Card.vue';
import Button from '../Button/Button.vue';
import Calendar from '../Calendar/Calendar.vue';
import Icon from '../Icon/Icon.vue';
import { parseAbsoluteToLocal } from '@internationalized/date';

const emit = defineEmits(['update:modelValue']);

const props = defineProps({
    date: { type: String, default: null },
    badge: { type: String, default: null },
    required: { type: Boolean, default: false },
    modelValue: { type: [Object, String], default: null },
    min: { type: [String, Object], default: null },
    max: { type: [String, Object], default: null },
    granularity: { type: String, default: null },
    inline: { type: Boolean, default: false },
    clearable: { type: Boolean, default: true },
    disabled: { type: Boolean, default: false },
    readOnly: { type: Boolean, default: false },
});

const calendarBindings = computed(() => ({
    modelValue: props.modelValue,
    min: props.min,
    max: props.max,
    components: {
        Root: DateRangePickerCalendar,
        Header: DateRangePickerHeader,
        Heading: DateRangePickerHeading,
        Prev: DateRangePickerPrev,
        Next: DateRangePickerNext,
        Grid: DateRangePickerGrid,
        GridHead: DateRangePickerGridHead,
        GridBody: DateRangePickerGridBody,
        GridRow: DateRangePickerGridRow,
        HeadCell: DateRangePickerHeadCell,
        Cell: DateRangePickerCell,
        CellTrigger: DateRangePickerCellTrigger,
    },
}));

// The placeholder defines the month to show when there's no value. Additionally,
// by setting it to an absolute value, it ensures that the emitted event value
// will be the appropriate format (e.g. a full date with time with timezone,
// rather than just a day).
const placeholder = parseAbsoluteToLocal(new Date().toISOString());

const calendarEvents = computed(() => ({
    'update:model-value': (event) => {
        if (props.granularity === 'day') {
            event.start.hour = 0;
            event.start.minute = 0;
            event.start.second = 0;
            event.start.millisecond = 0;

            event.end.hour = 0;
            event.end.minute = 0;
            event.end.second = 0;
            event.end.millisecond = 0;
        }

        emit('update:modelValue', event)
    },
}));
</script>

<template>
    <div class="group/input relative block w-full" data-ui-input>
        <DateRangePickerRoot
            :modelValue="modelValue"
            :granularity="granularity"
            :locale="$date.locale"
            :disabled="disabled || readOnly"
            @update:model-value="emit('update:modelValue', $event)"
            v-bind="$attrs"
            prevent-deselect
            hide-time-zone
            :placeholder="placeholder"
            close-on-select
        >
            <DateRangePickerField v-slot="{ segments }" class="w-full">
                <div
                    :class="[
                        'flex items-center w-full bg-white dark:bg-gray-900',
                        'border border-gray-300 dark:border-x-0 dark:border-t-0 dark:border-white/10 dark:inset-shadow-2xs dark:inset-shadow-black',
                        'leading-[1.375rem] text-gray-600 dark:text-gray-300',
                        'shadow-ui-sm not-prose h-10 rounded-lg py-2 px-2.5 disabled:shadow-none',
                        'data-invalid:border-red-500',
                        'disabled:shadow-none disabled:opacity-50',
                        readOnly ? 'border-dashed' : '',
                    ]"
                >
                    <DateRangePickerTrigger v-if="!inline">
                        <Button as="div" variant="ghost" size="sm" icon="calendar" class="-ms-2" />
                    </DateRangePickerTrigger>
                    <template v-for="item in segments.start" :key="item.part">
                        <DateRangePickerInput v-if="item.part === 'literal'" :part="item.part" type="start">
                            {{ item.value }}
                        </DateRangePickerInput>
                        <DateRangePickerInput
                            v-else
                            :part="item.part"
                            class="rounded-sm py-0.5 focus:bg-blue-100 focus:outline-hidden data-placeholder:text-gray-600 dark:focus:bg-blue-900 dark:data-placeholder:text-gray-400"
                            :class="{
                                'px-0.25!': item.part === 'month' || item.part === 'year' || item.part === 'day',
                            }"
                            type="start"
                        >
                            {{ item.value }}
                        </DateRangePickerInput>
                    </template>
                    <span class="mx-0.75 text-gray-400 dark:text-gray-600">&ndash;</span>
                    <template v-for="item in segments.end" :key="item.part">
                        <DateRangePickerInput v-if="item.part === 'literal'" :part="item.part" type="end">
                            {{ item.value }}
                        </DateRangePickerInput>
                        <DateRangePickerInput
                            v-else
                            :part="item.part"
                            class="rounded-sm py-0.5 focus:bg-blue-100 focus:outline-hidden data-placeholder:text-gray-600 dark:focus:bg-gray-800 dark:data-placeholder:text-gray-400"
                            :class="{
                                'px-0.25!': item.part === 'month' || item.part === 'year' || item.part === 'day',
                            }"
                            type="end"
                        >
                            {{ item.value }}
                        </DateRangePickerInput>
                    </template>
                    <div class="flex-1" />
                    <Button v-if="!readOnly" @click="emit('update:modelValue', null)" variant="subtle" size="sm" icon="x" class="-me-2" :disabled="disabled" />
                </div>
            </DateRangePickerField>

            <DateRangePickerContent
                v-if="!inline"
                align="start"
                :align-offset="-12"
                :side-offset="14"
                class="data-[state=open]:data-[side=top]:animate-slideDownAndFade data-[state=open]:data-[side=right]:animate-slideLeftAndFade data-[state=open]:data-[side=bottom]:animate-slideUpAndFade data-[state=open]:data-[side=left]:animate-slideRightAndFade will-change-[transform,opacity]"
            >
                <Card class="w-[20rem]">
                    <Calendar v-bind="calendarBindings" v-on="calendarEvents" />
                </Card>
            </DateRangePickerContent>

            <Card v-if="inline" class="mt-2">
                <Calendar v-bind="calendarBindings" v-on="calendarEvents" />
            </Card>
        </DateRangePickerRoot>
    </div>
</template>