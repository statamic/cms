export default {
    methods: {
        updateIframeContents(url, target) {
            const iframe = document.createElement('iframe');
            iframe.setAttribute('frameborder', '0');
            iframe.setAttribute('src', url);
            iframe.setAttribute('id', 'live-preview-iframe');
            this.setIframeAttributes(iframe);

            const container = this.$refs.contents;

            if (container.firstChild) {
                const existingIFrameSource = new URL(container.firstChild.src);
                const newIFrameSource = new URL(iframe.src);

                existingIFrameSource.searchParams.delete('live-preview');
                newIFrameSource.searchParams.delete('live-preview');

                const iFrameSourceIsEqual = existingIFrameSource.toString() === newIFrameSource.toString();

                if (target?.use_post_message && iFrameSourceIsEqual) {
                    let postMessageData = target.post_message_data;
                    try {
                        postMessageData = JSON.parse(target.post_message_data);
                    } catch(e) {}

                    const targetOrigin = /^https?:\/\//.test(url) ? (new URL(url))?.origin : window.origin;

                    container.firstChild.contentWindow.postMessage(
                        postMessageData,
                        targetOrigin
                    );
                } else {
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
                }
            } else {
                container.appendChild(iframe);
            }
        },

        setIframeAttributes(iframe) {
            //
        }
    }
}
