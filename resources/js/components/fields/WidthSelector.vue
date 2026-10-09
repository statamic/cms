<script setup>
import { ref, computed } from 'vue'
import { cva } from 'cva'

const props = defineProps({
    modelValue: Number,
    initialWidths: Array,
    size: { type: String, default: 'base' },
    variant: { type: String, default: 'default' },
    /** When `true`, the value cannot be changed (read-only, same pattern as other CP controls). */
    readOnly: { type: Boolean, default: false },
})

const emit = defineEmits(['update:model-value'])

const isHovering = ref(false)
const hoveringOver = ref(null)
const widths = ref(props.initialWidths ?? [25, 33, 50, 66, 75, 100])

const selected = computed(() => {
    if (props.readOnly) {
        return props.modelValue
    }
    if (isHovering.value) {
        return hoveringOver.value
    }
    return props.modelValue
})

const readOnlyChromeClass =
    'data-readonly:border-dashed! data-readonly:border-gray-300 data-readonly:with-contrast:border-gray-100 data-readonly:dark:border! data-readonly:dark:border-dashed! data-readonly:dark:border-gray-600!'

function handleMouseEnter() {
    if (props.readOnly) return
    isHovering.value = true
}

function handleMouseLeave() {
    isHovering.value = false
    hoveringOver.value = null
}

function handleSegmentClick(width) {
    if (props.readOnly) return
    emit('update:model-value', width)
}

const wrapperClasses = cva({
    base: 'relative text-gray-600 dark:text-gray-400 font-mono antialiased bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 with-contrast:border-gray-500 overflow-hidden flex cursor-pointer data-readonly:cursor-default',
    variants: {
        size: {
            base: 'h-6 w-14 text-xs rounded-md',
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
    <div
        :class="[wrapperClasses, readOnly ? readOnlyChromeClass : '']"
        :data-readonly="readOnly ? true : undefined"
        @mouseenter="handleMouseEnter"
        @mouseleave="handleMouseLeave"
    >
        <div class="flex w-full" :class="{ 'pointer-events-none': readOnly }">
            <div
                v-for="width in widths"
                :key="width"
                @mouseenter.stop="hoveringOver = width"
                @click="handleSegmentClick(width)"
                :class="sizerClasses"
                :data-state="selected >= width ? 'selected' : 'unselected'"
                :data-last="selected === width && width !== 100"
            />
        </div>
        <div
            class="pointer-events-none absolute inset-0 z-10 flex w-full items-center justify-center text-center font-medium text-gray-900 transition-opacity dark:text-gray-300"
            :class="{ 'opacity-60': readOnly }"
        >
            {{ selected }}%
        </div>
    </div>
</template>
