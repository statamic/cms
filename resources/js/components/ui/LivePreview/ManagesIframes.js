import { ref } from 'vue';
import { createFieldSync, fieldHighlightDuration } from './FieldSync.js';

export function useIframeManager(iframeContentContainer, onSelectField = () => {}) {
    const previousUrl = ref(null);
    let fieldSync;
    let connectedIframe;
    let selecting = false;
    let selectedPath = null;
    let highlightExpiresAt = 0;
    let generation = 0;

    function connectFields() {
        fieldSync?.dispose();
        fieldSync = null;

        try {
            const doc = connectedIframe?.contentDocument;
            if (!doc?.body || !doc.getElementById('statamic-preview-fields')) return;

            fieldSync = createFieldSync(doc, onSelectField);
            fieldSync.select(selecting);
            const remaining = highlightExpiresAt - Date.now();
            if (selectedPath && remaining > 0) fieldSync.highlight(selectedPath, remaining);
        } catch { }
    }

    function disconnectFields() {
        connectedIframe?.removeEventListener('load', connectFields);
        connectedIframe = null;
        fieldSync?.dispose();
        fieldSync = null;
    }

    function watchIframe(iframe) {
        disconnectFields();
        connectedIframe = iframe;
        iframe.addEventListener('load', connectFields);
    }

    const hasIframeSourceChanged = (existingSrc, newSrc) => {
        existingSrc = new URL(existingSrc);
        newSrc = new URL(newSrc);
        existingSrc.searchParams.delete('live-preview');
        newSrc.searchParams.delete('live-preview');

        return existingSrc.toString() !== newSrc.toString();
    };

    const postMessageToIframe = (url, payload) => {
        // If the target is a relative url, we'll get the origin from the current window.
        const targetOrigin = /^https?:\/\//.test(url) ? new URL(url)?.origin : window.origin;

        iframeContentContainer.value.firstChild.contentWindow.postMessage(
            {
                name: 'statamic.preview.updated',
                url,
                ...payload,
            },
            targetOrigin,
        );
    };

    const updateIframeContents = async (url, target, payload, setIframeAttributes) => {
        generation++;
        const revision = generation;
        const iframe = document.createElement('iframe');
        iframe.setAttribute('frameborder', '0');
        iframe.setAttribute('src', url);
        iframe.setAttribute('id', 'live-preview-iframe');
        setIframeAttributes(iframe);

        const container = iframeContentContainer.value;
        let iframeUrl = new URL(url, window.location.href);
        let cleanUrl = iframeUrl.host + iframeUrl.pathname;

        // If there's no iframe yet, just append it.
        if (!container.firstChild) {
            watchIframe(iframe);
            container.appendChild(iframe);
            previousUrl.value = cleanUrl;
            return;
        }

        let shouldRefresh = target.refresh;

        if (hasIframeSourceChanged(container.firstChild.src, iframe.src)) {
            shouldRefresh = true;
        }

        if (!shouldRefresh) {
            postMessageToIframe(url, payload);

            if (Statamic.$config.get('livePreview.hot_reload_contents', false)) {
                const iframeWindow = container.firstChild.contentWindow;
                const iframeDocument = container.firstChild.contentDocument;
                if (!iframeDocument) return;

                const updatedHtml = await fetch(url).then((response) => response.text());
                if (revision !== generation) return;

                const updatedDocument = new DOMParser().parseFromString(updatedHtml, 'text/html');

                if (typeof iframeWindow.StatamicLivePreviewMorph !== 'undefined') {
                    await iframeWindow.StatamicLivePreviewMorph(iframeDocument, updatedDocument);
                    if (revision === generation) connectFields();
                    return;
                }

                if (typeof iframeWindow.Alpine !== 'undefined' && typeof iframeWindow.Alpine.morph !== 'undefined') {
                    iframeWindow.Alpine.morph(iframeDocument.body, updatedDocument.body);
                    connectFields();
                    return;
                }

                if (typeof iframeWindow.Livewire !== 'undefined') {
                    iframeWindow.Livewire.components.components().forEach(component => component.call('$refresh'));
                    return;
                }

                iframeDocument.body.innerHTML = updatedDocument.body.innerHTML;
                connectFields();
            }

            return;
        }

        let isSameOrigin = iframeUrl.origin === window.location.origin;
        let preserveScroll = isSameOrigin && cleanUrl === previousUrl.value;

        let scroll = preserveScroll
            ? [container.firstChild.contentWindow.scrollX ?? 0, container.firstChild.contentWindow.scrollY ?? 0]
            : null;

        watchIframe(iframe);
        container.replaceChild(iframe, container.firstChild);

        if (preserveScroll) {
            let iframeContentWindow = iframe.contentWindow;
            const iframeScrollUpdate = (event) => {
                iframeContentWindow.scrollTo(...scroll);
            };

            iframeContentWindow.addEventListener('DOMContentLoaded', iframeScrollUpdate, true);
            iframeContentWindow.addEventListener('load', iframeScrollUpdate, true);
        }

        previousUrl.value = cleanUrl;
    }

    return {
        previousUrl,
        updateIframeContents,
        selectFields(enabled) {
            selecting = enabled;
            fieldSync?.select(enabled);
        },
        highlightField(path) {
            selectedPath = path;
            highlightExpiresAt = Date.now() + fieldHighlightDuration;
            fieldSync?.highlight(path);
        },
        dispose() {
            generation++;
            disconnectFields();
        },
    };
}
