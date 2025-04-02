<script setup>
import { computed } from 'vue';
import {
    DatePickerContent,
    DatePickerField,
    DatePickerInput,
    DatePickerRoot,
    DatePickerTrigger,
    DatePickerCalendar,
    DatePickerCell,
    DatePickerCellTrigger,
    DatePickerGrid,
    DatePickerGridBody,
    DatePickerGridHead,
    DatePickerGridRow,
    DatePickerHeadCell,
    DatePickerHeader,
    DatePickerHeading,
    DatePickerNext,
    DatePickerPrev,
} from 'reka-ui';
import { WithField, Card, Button, Calendar } from '@statamic/ui';

const emit = defineEmits(['update:modelValue']);

const props = defineProps({
    date: { type: String, default: null },
    badge: { type: String, default: null },
    description: { type: String, default: null },
    label: { type: String, default: null },
    required: { type: Boolean, default: false },
    modelValue: { type: [Object, String], default: null },
    min: { type: [String, Object], default: null },
    max: { type: [String, Object], default: null },
    granularity: { type: String, default: null },
    inline: { type: Boolean, default: false },
    clearable: { type: Boolean, default: true },
    disabled: { type: Boolean, default: false },
});

const calendarBindings = computed(() => ({
    modelValue: props.modelValue,
    min: props.min,
    max: props.max,
    components: {
        Root: DatePickerCalendar,
        Header: DatePickerHeader,
        Heading: DatePickerHeading,
        Prev: DatePickerPrev,
        Next: DatePickerNext,
        Grid: DatePickerGrid,
        GridHead: DatePickerGridHead,
        GridBody: DatePickerGridBody,
        GridRow: DatePickerGridRow,
        HeadCell: DatePickerHeadCell,
        Cell: DatePickerCell,
        CellTrigger: DatePickerCellTrigger,
    },
}));

const calendarEvents = computed(() => ({
    'update:model-value': (event) => emit('update:modelValue', event),
}));
</script>

<template>
    <WithField :label :description :required :badge>
        <div class="group/input relative block w-full" data-ui-input>
            <DatePickerRoot
                :modelValue="modelValue"
                :granularity="granularity"
                :locale="$date.locale"
                @update:model-value="emit('update:modelValue', $event)"
                v-bind="$attrs"
                prevent-deselect
                hide-time-zone
            >
                <DatePickerField v-slot="{ segments }" class="w-full">
                    <div
                        :class="[
                            'flex w-full bg-white dark:bg-gray-900',
                            'border border-gray-300 dark:border-x-0 dark:border-t-0 dark:border-white/15 dark:inset-shadow-2xs dark:inset-shadow-black',
                            'leading-[1.375rem] text-gray-600 dark:text-gray-300',
                            'shadow-ui-sm not-prose h-10 rounded-lg py-2 px-10 disabled:shadow-none',
                            'data-invalid:border-red-500',
                        ]"
                    >
                        <DatePickerTrigger
                            v-if="!inline"
                            class="absolute start-1 top-1 bottom-1 flex items-center justify-center rounded-lg px-2 text-gray-400 outline-hidden hover:bg-gray-50 focus:bg-gray-50 dark:hover:bg-gray-900 dark:focus:bg-gray-900"
                        >
                            <ui-icon name="calendar" class="size-4" />
                        </DatePickerTrigger>
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
                    <button
                        v-if="clearable"
                        @click="emit('update:modelValue', null)"
                        type="button"
                        class="absolute end-1 top-1 bottom-1 flex items-center justify-center rounded-lg px-2 text-gray-300 outline-hidden hover:bg-gray-50 focus:bg-gray-50 active:text-gray-400 dark:hover:bg-gray-900 dark:focus:bg-gray-900"
                    >
                        <ui-icon name="x" class="size-3" />
                    </button>
                </DatePickerField>

                <DatePickerContent
                    v-if="!inline"
                    :side-offset="4"
                    class="data-[state=open]:data-[side=top]:animate-slideDownAndFade data-[state=open]:data-[side=right]:animate-slideLeftAndFade data-[state=open]:data-[side=bottom]:animate-slideUpAndFade data-[state=open]:data-[side=left]:animate-slideRightAndFade will-change-[transform,opacity]"
                >
                    <Card class="w-[20rem]">
                        <Calendar v-bind="calendarBindings" v-on="calendarEvents" />
                    </Card>
                </DatePickerContent>

                <Card v-if="inline" class="mt-2">
                    <Calendar v-bind="calendarBindings" v-on="calendarEvents" />
                </Card>
            </DatePickerRoot>
        </div>
    </WithField>
</template>
