<template>
    <div
        class="field-width field-width-selector"
        @mouseenter="isHovering = true"
        @mouseleave="isHovering = false"
    >
        <div class="field-width-label">{{ selected }}%</div>

        <div
            v-for="width in widths"
            @mouseenter.stop="hoveringOver = width"
            @click="value = width"
            :class="[
                'field-width-notch',
                'notch-' + width,
                { 'filled': selected >= width, 'selected': selected == width }
            ]"
        >
        </div>
    </div>
</template>

<script>
export default {

    props: ['value'],

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

    },

    mounted() {
        this.value = this.value || 100;
    },

    methods: {

        classes() {

        }

    }

}
</script>
