<script setup>
import { computed, onMounted, onUnmounted, ref, unref } from 'vue';
import { fromDate, getLocalTimeZone, isSameMonth, startOfMonth, toCalendarDate } from '@internationalized/date';
import { injectCalendarRootContext, injectRangeCalendarRootContext } from 'reka-ui';
import Icon from '../Icon/Icon.vue';

defineOptions({ name: 'CalendarToday' });

const calendarRoot = injectCalendarRootContext(null) ?? injectRangeCalendarRootContext(null);

const today = ref(new Date());
let timer;

// Re-evaluates "today" at midnight so the disabled state doesn't go stale if the calendar stays open overnight.
function scheduleUpdate() {
    const now = new Date();
    const msUntilMidnight = +new Date(now.getFullYear(), now.getMonth(), now.getDate() + 1) - +now;
    timer = setTimeout(() => { today.value = new Date(); scheduleUpdate(); }, msUntilMidnight);
}

onMounted(scheduleUpdate);
onUnmounted(() => clearTimeout(timer));

function currentMonth() {
    return startOfMonth(toCalendarDate(fromDate(today.value, getLocalTimeZone())));
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
