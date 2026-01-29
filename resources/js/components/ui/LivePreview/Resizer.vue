<script setup>
const emit = defineEmits(['resize-start', 'resize-end', 'resized', 'collapsed']);

function resizeStart() {
    emit('resize-start');
    window.addEventListener('mousemove', resizing);
    window.addEventListener('mouseup', resizeEnd);
}

function resizeEnd() {
    emit('resize-end');
    window.removeEventListener('mousemove', resizing, false);
    window.removeEventListener('mouseup', resizeEnd, false);
}

function resizing(e) {
    e.preventDefault();
    let width = e.clientX;

    // If they've resized it all the way down, we'll collapse it.
    if (width < 16) return emit('collapsed');

    // Prevent the width being too narrow.
    width = width < 350 ? 350 : width;

    emit('resized', width);
}
</script>

<template>
    <div class="live-preview-resizer" @mousedown="resizeStart" />
</template>
