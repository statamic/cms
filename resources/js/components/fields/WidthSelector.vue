<template>
    <div
        class="field-width field-width-selector"
        @mouseenter="isHovering = true"
        @mouseleave="isHovering = false"
    >
        <div class="w-full flex">
            <div
                v-for="width in widths"
                :key="width"
                @mouseenter.stop="hoveringOver = width"
                @click="$emit('update:model-value', width)"
                :class="[
                    'field-width-notch',
                    'notch-' + width,
                    { 'filled': selected >= width, 'selected': selected == width }
                ]"
            />
        </div>
        <div class="field-width-label">{{ selected }}%</div>
    </div>
</template>

<script>
export default {
    emits: ['update:model-value'],

    props: [
        'modelValue',
        'initialWidths'
    ],

    data() {
        return {
            isHovering: false,
            hoveringOver: null,
            widths: this.initialWidths ?? [25, 33, 50, 66, 75, 100]
        }
    },

    computed: {

        selected() {
            if (this.isHovering) {
                return this.hoveringOver;
            }

            return this.modelValue;
        }

    }

}
</script>
