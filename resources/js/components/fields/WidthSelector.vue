<template>
    <div
        class="field-width field-width-selector"
        @mouseenter="isHovering = true"
        @mouseleave="isHovering = false"
    >
        <template v-if="! hidden">
            <div class="field-width-label">{{ selected }}%</div>

            <div
                v-for="width in widths"
                :key="width"
                @mouseenter.stop="hoveringOver = width"
                @click="$emit('input', width)"
                :class="[
                    'field-width-notch',
                    'notch-' + width,
                    { 'filled': selected >= width, 'selected': selected == width }
                ]"
            >
            </div>
        </template>
        <template v-else>
            <div class="field-width-hidden flex items-center justify-center">
                <svg-icon name="hidden" class="h-4 w-4 opacity-50"></svg-icon>
            </div>
        </template>
    </div>
</template>

<script>
export default {

    props: ['value', 'hidden'],

    props: {
        value: {
            default: 100,
        },
        hidden: {
            type: Boolean,
            default: false,
        },
    },

    data() {
        return {
            isHovering: false,
            hoveringOver: null,
            widths: [25, 33, 50, 66, 75, 100]
        }
    },

    computed: {

        selected() {
            if (this.isHovering) {
                return this.hoveringOver;
            }

            return this.value;
        }

    }

}
</script>
