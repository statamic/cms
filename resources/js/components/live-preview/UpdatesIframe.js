export default {
    methods: {
        updateIframeContents(url) {
            const iframe = document.createElement('iframe');
            iframe.setAttribute('frameborder', '0');
            iframe.setAttribute('src', url);
            iframe.setAttribute('id', 'live-preview-iframe');
            this.setIframeAttributes(iframe);

            const container = this.$refs.contents;

            if (! container.firstChild) {
                container.appendChild(iframe);
                return;
            }

            let isSameOrigin = url.startsWith('/') || new URL(url).hostname === window.location.host;

            let scroll = isSameOrigin ? [
                container.firstChild.contentWindow.scrollX ?? 0,
                container.firstChild.contentWindow.scrollY ?? 0
            ] : null;

            container.replaceChild(iframe, container.firstChild);

            if (isSameOrigin) {
                setTimeout(() => {
                    iframe.contentWindow.scrollTo(...scroll);
                }, 200);
            }
        },

        setIframeAttributes(iframe) {
            //
        }
    }
}
