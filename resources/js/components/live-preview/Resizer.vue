<template>

    <div class="live-preview-resizer h-full absolute pin-t" @mousedown="resizeStart" />

</template>

<script>
export default {

    methods: {

        resizeStart() {
            window.addEventListener('mousemove', this.resizing);
            window.addEventListener('mouseup', this.resizeEnd);
        },

        resizeEnd() {
            window.removeEventListener('mousemove', this.resizing, false);
            window.removeEventListener('mouseup', this.resizeEnd, false);
        },

        resizing(e) {
            e.preventDefault();
            let width = e.clientX;

            // Prevent the width being too narrow.
            width = (width < 350) ? 350 : width;

            this.$emit('resized', width);
        }

    }

}
</script>
