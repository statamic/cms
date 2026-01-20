<script setup>
import { ref, watch, nextTick } from 'vue';
import { Tooltip as VTooltip } from 'floating-vue';
import { useTooltip } from '@/composables/tooltip.js';

const { isVisible, content, html, targetEl } = useTooltip();

const showTooltip = ref(false);
const wrapperStyle = ref({});
const tooltipKey = ref(0);
const displayContent = ref('');
const displayHtml = ref(false);

function updatePosition() {
    if (!targetEl.value) {
        wrapperStyle.value = { display: 'none' };
        return;
    }

    const rect = targetEl.value.getBoundingClientRect();

    wrapperStyle.value = {
        position: 'fixed',
        top: `${rect.top}px`,
        left: `${rect.left}px`,
        width: `${rect.width}px`,
        height: `${rect.height}px`,
        zIndex: 9999,
        pointerEvents: 'none',
    };
}

watch([isVisible, targetEl, content], async ([visible, target]) => {
    if (visible && target) {
        // Update content and position (handles both initial show and target changes)
        displayContent.value = content.value;
        displayHtml.value = html.value;
        updatePosition();
        tooltipKey.value++;
        await nextTick();
        showTooltip.value = true;
    } else {
        showTooltip.value = false;
        // Don't clear displayContent here - let it persist during animation
    }
}, { immediate: true });
</script>

<template>
    <Teleport to="body">
        <div :style="wrapperStyle">
            <VTooltip
                :key="tooltipKey"
                :shown="showTooltip"
                :triggers="[]"
                placement="top"
                :distance="10"
            >
                <span class="block w-full h-full" />
                <template #popper>
                    <div v-if="displayHtml" v-html="displayContent" />
                    <template v-else>{{ displayContent }}</template>
                </template>
            </VTooltip>
        </div>
    </Teleport>
</template>
