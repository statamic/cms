const hasIframeSourceChanged = (existingSrc, newSrc) => {
    existingSrc = new URL(existingSrc);
    newSrc = new URL(newSrc);
    existingSrc.searchParams.delete('live-preview');
    newSrc.searchParams.delete('live-preview');

    return existingSrc.toString() !== newSrc.toString();
}

export default {
    methods: {
        updateIframeContents(url, target) {
            const iframe = document.createElement('iframe');
            iframe.setAttribute('frameborder', '0');
            iframe.setAttribute('src', url);
            iframe.setAttribute('id', 'live-preview-iframe');
            this.setIframeAttributes(iframe);

            const container = this.$refs.contents;

            // If there's no iframe yet, just append it.
            if (! container.firstChild) {
                container.appendChild(iframe);
                return;
            }

            let shouldRefresh = target.refresh;

            if (hasIframeSourceChanged(container.firstChild.src, iframe.src)) {
                shouldRefresh = true;
            }

            if (shouldRefresh) {
                let isSameOrigin = url.startsWith('/') || new URL(url).host === window.location.host;

                let scroll = isSameOrigin ? [
                    container.firstChild.contentWindow.scrollX ?? 0,
                    container.firstChild.contentWindow.scrollY ?? 0
                ] : null;

                container.replaceChild(iframe, container.firstChild);

                if (isSameOrigin) {
                    let iframeContentWindow = iframe.contentWindow;
                    const iframeScrollUpdate = (event) => {
                        iframeContentWindow.scrollTo(...scroll);
                    };

                    iframeContentWindow.addEventListener('DOMContentLoaded', iframeScrollUpdate, true);
                    iframeContentWindow.addEventListener('load', iframeScrollUpdate, true);
                }
            } else {
                const targetOrigin = /^https?:\/\//.test(url) ? (new URL(url))?.origin : window.origin;

                container.firstChild.contentWindow.postMessage('statamic.preview.updated', targetOrigin);
            }
        },

        setIframeAttributes(iframe) {
            //
        }
    }
}
