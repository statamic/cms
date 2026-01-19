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
        base: [
            'relative flex items-start gap-3 rounded-xl border p-4 [&:has(p)]:py-5 [&:has(p)]:pb-6 text-sm',
            '[&_h1]:mb-1 [&_h1]:font-bold',
            '[&_h2]:mb-1 [&_h2]:font-bold',
            '[&_h3]:mb-1 [&_h3]:font-bold',
            '[&_h4]:mb-1 [&_h4]:font-bold',
            '[&_h5]:mb-1 [&_h5]:font-bold',
            '[&_h6]:mb-1 [&_h6]:font-bold',
            '[&_p:not(:last-child)]:mb-3',
        ].join(' '),
        variants: {
            variant: {
                default: [
                    'bg-gray-50 dark:bg-gray-900/50 border-gray-200 dark:border-gray-700/80 text-gray-900 dark:text-gray-100',
                    '[&_code]:bg-gray-200! dark:[&_code]:bg-gray-800!',
                    '[&_code]:border-gray-300 dark:[&_code]:border-gray-700',
                ],
                warning: [
                    'bg-amber-50 dark:bg-amber-300/6 border-amber-200 dark:border-amber-400/25 text-amber-800 dark:text-amber-300',
                    '[&_code]:bg-amber-200/60! [&_code]:text-amber-800! dark:[&_code]:bg-amber-300/25! dark:[&_code]:text-amber-200!',
                    '[&_code]:border-amber-300/50 dark:[&_code]:border-amber-400/40',
                ],
                error: [
                    'bg-red-50 dark:bg-red-300/6 border-red-200 dark:border-red-400/25 text-red-800 dark:text-red-300',
                    '[&_code]:bg-red-200/60! [&_code]:text-red-800! dark:[&_code]:bg-red-300/25! dark:[&_code]:text-red-200!',
                    '[&_code]:border-red-300 dark:[&_code]:border-red-400/30',
                ],
                success: [
                    'bg-emerald-50 dark:bg-emerald-300/6 border-emerald-200 dark:border-emerald-400/25 text-emerald-800 dark:text-emerald-300',
                    '[&_code]:bg-emerald-200/60! [&_code]:text-emerald-800! dark:[&_code]:bg-emerald-300/25! dark:[&_code]:text-emerald-200!',
                    '[&_code]:border-emerald-300 dark:[&_code]:border-emerald-400/30',
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
            class="size-5 shrink-0 opacity-70"
            aria-hidden="true"
        />
        <div class="flex-1 min-w-0">
            <slot v-if="hasDefaultSlot" />
            <span v-else v-html="text" />
        </div>
    </div>
</template>
