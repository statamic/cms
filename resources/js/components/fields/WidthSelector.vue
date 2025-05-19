<template>
    <div class="relative bg-white border border-gray-300 overflow-hidden rounded-md flex h-8 w-20 cursor-pointer" @mouseenter="isHovering = true" @mouseleave="isHovering = false">
        <div class="flex w-full">
            <div
                v-for="width in widths"
                :key="width"
                @mouseenter.stop="hoveringOver = width"
                @click="$emit('update:model-value', width)"
                :class="[
                    'relative flex-1 border-l border-gray-300',
                    { 'bg-gray-100 border-l-0 border-gray-400': selected >= width, selected: selected == width },
                ]"
            />
        </div>
        <div class="pointer-events-none absolute inset-0 z-10 flex w-full items-center justify-center text-center text-sm text-gray-600">{{ selected }}%</div>
    </div>
</template>

<script>
export default {
    emits: ['update:model-value'],

    props: [
        'modelValue',
        'initialWidths',
        'size',
    ],

    data() {
        return {
            isHovering: false,
            hoveringOver: null,
            widths: this.initialWidths ?? [25, 33, 50, 66, 75, 100],
        };
    },

    computed: {
        selected() {
            if (this.isHovering) {
                return this.hoveringOver;
            }

            return this.modelValue;
        },
    },
};
</script>
