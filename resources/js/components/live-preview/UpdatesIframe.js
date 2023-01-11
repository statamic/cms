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

            let urlHost = new URL(url).host;

            let isSameOrigin = url.startsWith('/') || new URL(url).host === window.location.host;

            let preserveScroll = isSameOrigin && urlHost === this.previousHost;

            let scroll = preserveScroll ? [
                container.firstChild.contentWindow.scrollX ?? 0,
                container.firstChild.contentWindow.scrollY ?? 0
            ] : null;

            container.replaceChild(iframe, container.firstChild);

            if (preserveScroll) {
                let iframeContentWindow = iframe.contentWindow;
                const iframeScrollUpdate = (event) => {
                    iframeContentWindow.scrollTo(...scroll);
                };

                iframeContentWindow.addEventListener('DOMContentLoaded', iframeScrollUpdate, true);
                iframeContentWindow.addEventListener('load', iframeScrollUpdate, true);
            }

            this.previousHost = new URL(url).host;
        },

        setIframeAttributes(iframe) {
            //
        }
    }
}
