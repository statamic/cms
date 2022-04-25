export default {
    methods: {
        updateIframeContents(url) {
            const iframe = document.createElement('iframe');
            iframe.setAttribute('frameborder', '0');
            iframe.setAttribute('src', url);
            iframe.setAttribute('id', 'live-preview-iframe');
            this.setIframeAttributes(iframe);

            const container = this.$refs.contents;

            if (this.$config.get('livePreview.post_message_data')) {
                const targetOrigin = /^https?:\/\//.test(url) ? (new URL(url))?.origin : window.origin
                container.firstChild
                    ? container.firstChild.contentWindow.postMessage(
                        this.$config.get('livePreview.post_message_data'),
                        targetOrigin
                    )
                    : container.appendChild(iframe);
            } else {
                container.firstChild
                    ? container.replaceChild(iframe, container.firstChild)
                    : container.appendChild(iframe);
            }

            // todo: maintain scroll position
        },

        setIframeAttributes(iframe) {
            //
        }
    }
}
