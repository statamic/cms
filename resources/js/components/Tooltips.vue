<script setup>
import { ref, watch, nextTick, computed } from 'vue';
import { Tooltip as VTooltip } from 'floating-vue';
import DOMPurify from 'dompurify';
import { useTooltip } from '@/composables/tooltip.js';
import useCopy from '@/composables/copy';

const { isVisible, content, html, copyable, targetEl, registerContentEl } = useTooltip();
const { copySupported, copy } = useCopy();

const showTooltip = ref(false);
const wrapperStyle = ref({});
const spanStyle = ref({});
const tooltipKey = ref(0);
const displayContent = ref('');
const displayHtml = ref(false);
const displayCopyable = ref(false);
const isInteractive = computed(() => displayHtml.value || displayCopyable.value);

function isExternalHref(href) {
    if (!href) return false;

    try {
        const url = new URL(href, window.location.href);

        if (!['http:', 'https:'].includes(url.protocol)) return false;

        return url.origin !== window.location.origin;
    } catch {
        return false;
    }
}

function sanitizeHtml(html) {
    const sanitized = DOMPurify.sanitize(html ?? '', { ADD_ATTR: ['target'] });
    const template = document.createElement('template');
    template.innerHTML = sanitized;

    template.content.querySelectorAll('a[href]').forEach((anchor) => {
        if (!isExternalHref(anchor.getAttribute('href'))) return;

        anchor.setAttribute('target', '_blank');
        anchor.setAttribute('rel', 'noopener noreferrer');
    });

    return template.innerHTML;
}

function updatePosition() {
    if (!targetEl.value) {
        wrapperStyle.value = { display: 'none' };
        spanStyle.value = {};
        return;
    }

    const rect = targetEl.value.getBoundingClientRect();

    wrapperStyle.value = {
        position: 'fixed',
        top: `${rect.top}px`,
        left: `${rect.left}px`,
        width: `${rect.width}px`,
        height: `${rect.height}px`,
        pointerEvents: 'none',
    };

    spanStyle.value = {
        display: 'block',
        width: `${rect.width}px`,
        height: `${rect.height}px`,
    };
}

watch([isVisible, targetEl, content], async ([visible, target]) => {
    if (visible && target) {
        // Update content and position (handles both initial show and target changes)
        displayHtml.value = html.value;
        displayCopyable.value = copyable.value;
        displayContent.value = displayHtml.value ? sanitizeHtml(content.value) : content.value;
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
                <span :style="spanStyle" />
                <template #popper>
                    <div
                        :ref="registerContentEl"
                        :class="{ 'tooltip-popper-interactive': isInteractive }"
                    >
                        <div v-if="displayHtml" v-html="displayContent" />
                        <span
                            v-else-if="displayCopyable && copySupported"
                            class="cursor-pointer hover:underline"
                            role="button"
                            tabindex="0"
                            :aria-label="__('Copy :handle to clipboard', { handle: displayContent })"
                            @click.stop="copy(displayContent)"
                            @keydown.enter.prevent="copy(displayContent)"
                            @keydown.space.prevent="copy(displayContent)"
                        >{{ displayContent }}</span>
                        <template v-else>{{ displayContent }}</template>
                    </div>
                </template>
            </VTooltip>
        </div>
    </Teleport>
</template>
