<script setup>
import PanelHeader from '../Panel/Header.vue';
import Heading from '../Heading.vue';
import Subheading from '../Subheading.vue';

const props = defineProps({
    /** Heading text for the panel */
    heading: { type: String, default: null },
    /** Subheading text below the heading */
    subheading: { type: String, default: null },
});
</script>

<template>
    <div
        :class="[
            '@container/panel relative bg-gray-150 [.bg-architectural-lines_&]:backdrop-blur-[10px] dark:bg-gray-950/35 dark:inset-shadow-2xs dark:inset-shadow-black',
            'w-full rounded-2xl mb-10 max-[600px]:p-1.25 p-1.75 [&:has(>[data-ui-panel-header])]:pt-0 focus-none starting-style-transition starting-style-transition--siblings',
        ]"
        data-ui-panel
    >
        <PanelHeader v-if="heading" class="flex items-center justify-between">
	        <div>
		        <Heading v-html="heading" />
		        <Subheading v-if="subheading" v-html="subheading" />
	        </div>
	        <div v-if="$slots['header-actions']" class="flex flex-wrap items-center gap-2 sm:gap-3">
                <slot name="header-actions" />
	        </div>
        </PanelHeader>
        <slot />
    </div>
</template>
