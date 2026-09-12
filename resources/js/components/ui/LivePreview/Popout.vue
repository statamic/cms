<script setup>
import { useIframeManager } from './ManagesIframes.js';
import { onMounted, onUnmounted, ref, useTemplateRef } from 'vue';

const channel = ref(null);
const iframeContentContainer = useTemplateRef('contents');

const { updateIframeContents, selectFields, highlightField, dispose } = useIframeManager(
    iframeContentContainer,
    field => channel.value?.postMessage({ event: 'field.selected', field }),
);

function setIframeAttributes(iframe) {
    iframe.setAttribute('class', 'min-h-screen');
}

onMounted(() => {
    const session = new URL(window.location.href).searchParams.get('preview-session') || 'livepreview';
    channel.value = new BroadcastChannel(session);

    channel.value.onmessage = (e) => {
        switch (e.data.event) {
            case 'updated':
                selectFields(e.data.payload.selectingFields ?? false);
                updateIframeContents(e.data.url, e.data.target, e.data.payload, setIframeAttributes);
                break;
            case 'field.highlight':
                highlightField(e.data.path);
                break;
            case 'field.selecting':
                selectFields(e.data.enabled);
                break;
            case 'ping':
                channel.value.postMessage({ event: 'popout.pong' });
                break;
            default:
                break;
        }
    };

    channel.value.postMessage({ event: 'popout.opened' });
});

onUnmounted(() => {
    dispose();
    channel.value?.close();
});
</script>

<template>
    <div class="live-preview-contents min-h-screen" ref="contents" />
</template>
