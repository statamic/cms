<template>

    <div class="live-preview-contents min-h-screen" ref="contents" />

</template>

<script>
import UpdatesIframe from './UpdatesIframe';

export default {

    mixins: [
        UpdatesIframe
    ],

    data() {
        return {
            channel: null
        }
    },

    created() {
        this.channel = new BroadcastChannel('livepreview');

        this.channel.onmessage = e => {
            switch (e.data.event) {
                case 'updated':
                    this.updateIframeContents(e.data.url);
                    break;
                case 'ping':
                    this.channel.postMessage({ event: 'popout.pong' });
                    break;
                default:
                    break;
            }
        };

        this.channel.postMessage({ event: 'popout.opened' });
    },

    methods: {
        setIframeAttributes(iframe) {
            iframe.setAttribute('class', 'min-h-screen');
        }
    }
}
</script>
