<script setup>
import { ref, computed } from 'vue'
import { cva } from 'cva'
import { GRID_COLUMNS, widthToColumnSpan, widthToPercentage } from '@/util/width.js'

const props = defineProps({
    modelValue: Number,
    initialWidths: Array,
    size: { type: String, default: 'base' },
    variant: { type: String, default: 'default' },
})

const emit = defineEmits(['update:model-value'])

const isHovering = ref(false)
const hoveringOver = ref(null)
// Compared on resolved spans rather than raw values, so a width the stops don't
// offer (a hand written span of 6, say) still fills the selector to the right place.
const widths = computed(() =>
    (props.initialWidths ?? [25, 33, 50, 66, 75, 100]).map((value) => ({
        value,
        span: widthToColumnSpan(value),
    })),
)

const selected = computed(() => {
    if (isHovering.value) {
        return hoveringOver.value
    }
    return props.modelValue
})

const selectedSpan = computed(() => widthToColumnSpan(selected.value))

const wrapperClasses = cva({
    base: 'relative text-gray-600 dark:text-gray-400 font-mono antialiased bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 with-contrast:border-gray-500 overflow-hidden flex cursor-pointer shadow-ui-sm',
    variants: {
        size: {
            base: 'h-6 w-14 text-xs rounded-md',
            md: 'h-8 w-16 text-xs rounded-lg',
            lg: 'h-10 w-24 text-sm rounded-lg',
        },
    },
})({
    size: props.size,
})

const sizerClasses = cva({
    base: 'border border-l-0 last:border-r-0 border-y-0 data-[state="selected"]:data-[last="false"]:border-gray-100 dark:border-gray-700 dark:data-[state="selected"]:data-[last="false"]:border-gray-900 flex-1',
    variants: {
        variant: {
            default: [
                'data-[state="selected"]:bg-gray-100 data-[state="selected"]:border-gray-300 data-[last="true"]:border-r data-[last="true"]:border-gray-300',
                'dark:data-[state="selected"]:bg-gray-900 dark:data-[state="selected"]:border-gray-700',
            ],
            filled: [
                'data-[state="selected"]:bg-gray-100 data-[last="true"]:border-r data-[last="true"]:border-gray-300',
                'dark:data-[state="selected"]:bg-gray-900 dark:data-[state="selected"]:border-gray-800',
            ],
        },
    },
})({
    variant: props.variant,
})
</script>

<template>
    <div :class="wrapperClasses" @mouseenter="isHovering = true" @mouseleave="isHovering = false">
        <div class="flex w-full">
            <div
                v-for="width in widths"
                :key="width.value"
                @mouseenter.stop="hoveringOver = width.value"
                @click="$emit('update:model-value', width.value)"
                :class="sizerClasses"
                :data-state="selectedSpan >= width.span ? 'selected' : 'unselected'"
                :data-last="selectedSpan === width.span && width.span !== GRID_COLUMNS"
            />
        </div>
        <div class="pointer-events-none absolute inset-0 z-10 flex w-full items-center justify-center text-center font-medium text-gray-900 dark:text-gray-300">{{ widthToPercentage(selected) }}%</div>
    </div>
</template>
