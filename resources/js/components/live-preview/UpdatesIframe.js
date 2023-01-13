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

            let iframeUrl = new URL(url);
            let cleanUrl = iframeUrl.host + iframeUrl.pathname;

            let isSameOrigin = url.startsWith('/') || iframeUrl.host === window.location.host;

            let preserveScroll = isSameOrigin && (cleanUrl === this.previousUrl || this.previousUrl === null);

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

            this.previousUrl = cleanUrl;
        },

        setIframeAttributes(iframe) {
            //
        }
    }
}
