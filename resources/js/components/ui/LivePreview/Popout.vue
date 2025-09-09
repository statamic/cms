<script setup>
import { updateIframeContents } from './UpdatesIframes.js';
import { onMounted, ref, useTemplateRef } from 'vue';

const channel = ref(null);
const previousUrl = ref(null);
const iframeContentContainer = useTemplateRef('contents');

function setIframeAttributes(iframe) {
    iframe.setAttribute('class', 'min-h-screen');
}

onMounted(() => {
    channel.value = new BroadcastChannel('livepreview');

    channel.value.onmessage = (e) => {
        switch (e.data.event) {
            case 'updated':
                updateIframeContents(e.data.url, e.data.target, e.data.payload, setIframeAttributes, iframeContentContainer, previousUrl);
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
