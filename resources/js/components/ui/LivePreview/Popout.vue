<script setup>
import { useIframeManager } from './ManagesIframes.js';
import { onMounted, ref, useTemplateRef } from 'vue';

const channel = ref(null);
const iframeContentContainer = useTemplateRef('contents');

const { updateIframeContents } = useIframeManager(iframeContentContainer);

function setIframeAttributes(iframe) {
    iframe.setAttribute('class', 'min-h-screen');
}

onMounted(() => {
    channel.value = new BroadcastChannel('livepreview');

    channel.value.onmessage = (e) => {
        switch (e.data.event) {
            case 'updated':
                updateIframeContents(e.data.url, e.data.target, e.data.payload, setIframeAttributes);
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
</script>

<template>
    <div class="live-preview-contents min-h-screen" ref="contents" />
</template>
