<script setup>
import { computed, unref } from 'vue';
import { getLocalTimeZone, isSameMonth, now, startOfMonth, toCalendarDate } from '@internationalized/date';
import { injectCalendarRootContext, injectRangeCalendarRootContext } from 'reka-ui';
import Icon from '../Icon/Icon.vue';

defineOptions({ name: 'CalendarToday' });

const calendarRoot = injectCalendarRootContext(null) ?? injectRangeCalendarRootContext(null);

function currentMonth() {
    return startOfMonth(toCalendarDate(now(getLocalTimeZone())));
}

const disabled = computed(() => {
    if (!calendarRoot) return true;
    if (unref(calendarRoot.disabled) || unref(calendarRoot.readonly)) return true;
    const placeholder = unref(calendarRoot.placeholder);
    if (!placeholder) return false;
    return isSameMonth(toCalendarDate(placeholder), currentMonth());
});

function goToToday() {
    if (!calendarRoot || disabled.value) return;
    calendarRoot.onPlaceholderChange(currentMonth());
}
</script>

<template>
    <button
        type="button"
        class="inline-flex size-7.5 shrink-0 cursor-pointer items-center justify-center rounded-md text-gray-925 hover:bg-gray-50 active:scale-90 disabled:pointer-events-none disabled:opacity-40 dark:text-white dark:hover:bg-gray-925"
        :disabled="disabled"
        :aria-label="__('This month')"
        v-tooltip="__('This month')"
        @click="goToToday"
    >
        <Icon name="calendar" class="size-3.5!" />
    </button>
</template>
