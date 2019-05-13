<template>

    <div class="live-preview-resizer" @mousedown="resizeStart" />

</template>

<script>
export default {

    methods: {

        resizeStart() {
            this.$emit('resize-start');
            window.addEventListener('mousemove', this.resizing);
            window.addEventListener('mouseup', this.resizeEnd);
        },

        resizeEnd() {
            this.$emit('resize-end');
            window.removeEventListener('mousemove', this.resizing, false);
            window.removeEventListener('mouseup', this.resizeEnd, false);
        },

        resizing(e) {
            e.preventDefault();
            let width = e.clientX;

            // If they've resized it all the way down, we'll collapse it.
            if (width < 16) return this.$emit('collapsed');

            // Prevent the width being too narrow.
            width = (width < 350) ? 350 : width;

            this.$emit('resized', width);
        }

    }

}
</script>
