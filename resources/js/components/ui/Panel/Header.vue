<script setup>
import { computed } from 'vue';
import Heading from '../Heading.vue';

const props = defineProps({
    /** Heading text for the panel */
    title: { type: String, default: null },
    /** Show checkerboard toggle for asset thumbnails (default off) */
    showCheckerboardToggle: { type: Boolean, default: false },
    /** 0 = current CP color, 1 = alt CP color, 2 = transparent (default) */
    checkerboardMode: { type: Number, default: 2 },
});

const emit = defineEmits(['checkerboard-toggled']);

const isCpDark = computed(() =>
    typeof document !== 'undefined' && document.documentElement.classList.contains('dark')
);

const checkerboardIcon = computed(() => {
    if (props.checkerboardMode === 0) return isCpDark.value ? 'moon' : 'sun';
    if (props.checkerboardMode === 1) return isCpDark.value ? 'sun' : 'moon';
    return 'eye-slash';
});

function cycleCheckerboard() {
    emit('checkerboard-toggled', (props.checkerboardMode + 1) % 3);
}
</script>

<template>
    <header
        class="px-4.5 py-3 [&:has(button)]:pr-1 [&_button]:-my-2 [&_button]:relative"
        :class="{ 'flex items-center justify-between gap-2': showCheckerboardToggle }"
        data-ui-panel-header
    >
        <div v-if="showCheckerboardToggle" class="flex min-w-0 flex-1 items-center gap-2">
            <Heading v-if="props.title" v-text="props.title" />
            <slot v-else />
        </div>
        <template v-else>
            <Heading v-if="props.title" v-text="props.title" />
            <slot v-else />
        </template>
        <ui-button
            v-if="showCheckerboardToggle"
            inset
            size="sm"
            variant="ghost"
            class="[&_svg]:!opacity-45"
            :icon="checkerboardIcon"
            :text="__('Transparency')"
            @click="cycleCheckerboard"
        />
        <slot v-if="showCheckerboardToggle" name="trailing" />
    </header>
</template>
