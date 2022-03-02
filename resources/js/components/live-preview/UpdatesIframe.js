export default {
    methods: {
        updateIframeContents(url) {
            const iframe = document.createElement('iframe');
            iframe.setAttribute('frameborder', '0');
            iframe.setAttribute('src', url);
            iframe.setAttribute('id', 'live-preview-iframe');
            this.setIframeAttributes(iframe);

            const container = this.$refs.contents;
            container.firstChild
                ? container.replaceChild(iframe, container.firstChild)
                : container.appendChild(iframe);

            // todo: maintain scroll position
        },

        setIframeAttributes(iframe) {
            //
        }
    }
}
