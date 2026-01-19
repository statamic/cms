<script setup>
import { computed, useSlots } from 'vue';
import { cva } from 'cva';
import Icon from './Icon/Icon.vue';

const props = defineProps({
    /** The alert message to display */
    text: { type: [String, Number, Boolean, null], default: null },
    /** Controls the appearance of the alert. <br><br> Options: `default`, `warning`, `error`, `success` */
    variant: { type: String, default: 'default' },
    /** Icon name to display. [Browse available icons](/?path=/story/components-icon--all-icons) */
    icon: { type: String, default: null },
});

const slots = useSlots();
const hasDefaultSlot = !!slots.default;

const alertRole = computed(() => {
    // Use 'alert' for urgent messages that need immediate attention
    // Use 'status' for informational messages
    return props.variant === 'error' || props.variant === 'warning' ? 'alert' : 'status';
});

const ariaLive = computed(() => {
    // 'assertive' for urgent, 'polite' for informational
    return props.variant === 'error' || props.variant === 'warning' ? 'assertive' : 'polite';
});

const alertClasses = computed(() => {
    return cva({
        base: 'relative flex items-start gap-3 rounded-xl border p-4 text-sm',
        variants: {
            variant: {
                default: [
                    'bg-gray-50 dark:bg-gray-900/50 border-gray-200 dark:border-gray-700/80 text-gray-900 dark:text-gray-100',
                ],
                warning: [
                    'bg-amber-50 dark:bg-amber-300/6 border-amber-200 dark:border-amber-400/25 text-amber-800 dark:text-amber-300',
                ],
                error: [
                    'bg-red-50 dark:bg-red-300/6 border-red-200 dark:border-red-400/25 text-red-800 dark:text-red-300',
                ],
                success: [
                    'bg-emerald-50 dark:bg-emerald-300/6 border-emerald-200 dark:border-emerald-400/25 text-emerald-800 dark:text-emerald-300',
                ],
            },
        },
    })({ variant: props.variant });
});

const defaultIcon = computed(() => {
    if (props.icon) return props.icon;
    
    switch (props.variant) {
        case 'warning':
            return 'warning-diamond';
        case 'error':
            return 'alert-alarm-bell';
        case 'success':
            return 'checkmark';
        default:
            return 'info';
    }
});
</script>

<template>
    <div
        :class="alertClasses"
        :role="alertRole"
        :aria-live="ariaLive"
        data-ui-alert
        :data-variant="variant"
    >
        <Icon
            v-if="defaultIcon"
            :name="defaultIcon"
            class="size-5 shrink-0 mt-0.5 opacity-70"
            aria-hidden="true"
        />
        <div class="flex-1 min-w-0">
            <slot v-if="hasDefaultSlot" />
            <span v-else v-html="text" />
        </div>
    </div>
</template>
