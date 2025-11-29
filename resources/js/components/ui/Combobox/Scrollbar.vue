<script setup>
import { ref, watch, onUnmounted, nextTick } from 'vue';

const props = defineProps({
	viewport: { type: Object, default: null },
});

const isVisible = ref(false);
const thumbHeight = ref(0);
const thumbTop = ref(0);

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

watch(
	() => props.viewport,
	(viewport) => {
		if (viewport) {
			const element = viewport.$el || viewport;
			if (element) {
				element.addEventListener('scroll', update);
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
});

defineExpose({
	update,
});
</script>

<template>
	<div v-if="isVisible" class="absolute top-0 right-0 w-3 p-0.5 h-full pointer-events-none">
		<div
			class="absolute right-0 w-1.5 rounded-full bg-black/25 dark:bg-white/25 transition-opacity"
			:style="{
                height: `${thumbHeight}%`,
                top: `${thumbTop}%`
            }"
		/>
	</div>
</template>