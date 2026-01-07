<script setup>
import { ref, watch, onUnmounted, nextTick } from 'vue';

const props = defineProps({
	viewport: { type: Object, default: null },
});

const isVisible = ref(false);
const thumbHeight = ref(0);
const thumbTop = ref(0);
const isDragging = ref(false);

let resizeObserver = null;

function update() {
	const element = props.viewport?.$el || props.viewport;
	if (!element) return;

	const scrollHeight = element.scrollHeight;
	const clientHeight = element.clientHeight;
	const scrollTop = element.scrollTop;

	isVisible.value = scrollHeight > clientHeight;

	if (isVisible.value) {
		// Calculate thumb height as a percentage of visible area
		const thumbHeightPercent = (clientHeight / scrollHeight) * 100;
		thumbHeight.value = Math.max(thumbHeightPercent, 10); // Minimum 10%

		// Calculate thumb position
		const maxScroll = scrollHeight - clientHeight;
		const scrollPercent = maxScroll > 0 ? scrollTop / maxScroll : 0;
		const maxThumbTop = 100 - thumbHeight.value;
		thumbTop.value = scrollPercent * maxThumbTop;
	}
}

function handleMouseDown(event) {
	event.preventDefault();
	isDragging.value = true;
	
	document.addEventListener('mousemove', handleMouseMove);
	document.addEventListener('mouseup', handleMouseUp);
}

function handleMouseMove(event) {
	if (!isDragging.value) return;
	
	const element = props.viewport?.$el || props.viewport;
	if (!element) return;
	
	const scrollbarTrack = element.parentElement.querySelector('.absolute.top-0.right-0');
	if (!scrollbarTrack) return;
	
	const trackRect = scrollbarTrack.getBoundingClientRect();
	const mouseY = event.clientY - trackRect.top;
	const trackHeight = trackRect.height;
	
	// Calculate scroll position based on mouse position
	const scrollPercent = Math.max(0, Math.min(1, mouseY / trackHeight));
	const maxScroll = element.scrollHeight - element.clientHeight;
	
	element.scrollTop = scrollPercent * maxScroll;
}

function handleMouseUp() {
	isDragging.value = false;
	document.removeEventListener('mousemove', handleMouseMove);
	document.removeEventListener('mouseup', handleMouseUp);
}

watch(
	() => props.viewport,
	(viewport, oldViewport) => {
		const oldElement = oldViewport?.$el || oldViewport;

		if (oldElement) {
			oldElement.removeEventListener('scroll', update);
			resizeObserver?.disconnect();
		}

		if (viewport) {
			const element = viewport.$el || viewport;
			if (element) {
				element.addEventListener('scroll', update);

				resizeObserver = new ResizeObserver(() => update());
				resizeObserver.observe(element);

				nextTick(() => update());
			}
		}
	},
	{ immediate: true }
);

onUnmounted(() => {
	const element = props.viewport?.$el || props.viewport;

	if (element) {
		element.removeEventListener('scroll', update);
	}
	
	resizeObserver?.disconnect();
	
	document.removeEventListener('mousemove', handleMouseMove);
	document.removeEventListener('mouseup', handleMouseUp);
});

defineExpose({
	update,
});
</script>

<template>
	<div v-if="isVisible" class="absolute top-0 right-0 w-3 p-0.5 h-full pointer-events-none">
		<div
			class="absolute right-0 w-1.5 rounded-full bg-black/25 hover:bg-black/40 dark:bg-white/25 dark:hover:bg-white/40 transition-colors pointer-events-auto cursor-pointer"
			:class="{ 'bg-black/40 dark:bg-white/40': isDragging }"
			:style="{
                height: `${thumbHeight}%`,
                top: `${thumbTop}%`
            }"
			@mousedown="handleMouseDown"
		/>
	</div>
</template>