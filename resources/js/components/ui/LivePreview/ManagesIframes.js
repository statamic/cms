import { ref } from 'vue';
import { createFieldSync, fieldHighlightDuration, fieldManifestId } from './FieldSync.js';

export function useIframeManager(iframeContentContainer, onSelectField = () => {}, onCancelSelectFields = () => {}) {
    const previousUrl = ref(null);
    let fieldSync;
    let connectedIframe;
    let selecting = false;
    let selectedPath = null;
    let highlightExpiresAt = 0;
    let updateId = 0;

    function connectedDocument() {
        try {
            return connectedIframe?.contentDocument ?? null;
        } catch {
            return null;
        }
    }

    function cancelSelectFields() {
        selecting = false;
        onCancelSelectFields();
    }

    function connectFields() {
        fieldSync?.dispose();
        fieldSync = null;

        const doc = connectedDocument();
        if (!doc?.body || !doc.getElementById(fieldManifestId)) return;

        fieldSync = createFieldSync(doc, onSelectField, cancelSelectFields);
        fieldSync.select(selecting);

        const remaining = highlightExpiresAt - Date.now();
        if (selectedPath && remaining > 0) fieldSync.highlight(selectedPath, remaining);
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

    const createIframe = (url, setIframeAttributes) => {
        const iframe = document.createElement('iframe');
        iframe.setAttribute('frameborder', '0');
        iframe.setAttribute('src', url);
        iframe.setAttribute('id', 'live-preview-iframe');
        setIframeAttributes(iframe);

        return iframe;
    };

    const hotReloadContents = async (currentIframe, url, isCurrentUpdate) => {
        const iframeWindow = currentIframe.contentWindow;
        const iframeDocument = currentIframe.contentDocument;
        if (!iframeDocument) return;

        const updatedHtml = await fetch(url).then((response) => response.text());
        if (!isCurrentUpdate()) return;

        const updatedDocument = new DOMParser().parseFromString(updatedHtml, 'text/html');

        if (typeof iframeWindow.StatamicLivePreviewMorph !== 'undefined') {
            await iframeWindow.StatamicLivePreviewMorph(iframeDocument, updatedDocument);
            if (isCurrentUpdate()) connectFields();
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
    };

    const replaceIframe = (container, iframe, preserveScroll) => {
        const scroll = preserveScroll
            ? [container.firstChild.contentWindow.scrollX ?? 0, container.firstChild.contentWindow.scrollY ?? 0]
            : null;

        watchIframe(iframe);
        container.replaceChild(iframe, container.firstChild);

        if (!scroll) return;

        const iframeContentWindow = iframe.contentWindow;
        const iframeScrollUpdate = () => iframeContentWindow.scrollTo(...scroll);

        iframeContentWindow.addEventListener('DOMContentLoaded', iframeScrollUpdate, true);
        iframeContentWindow.addEventListener('load', iframeScrollUpdate, true);
    };

    const updateIframeContents = async (url, target, payload, setIframeAttributes) => {
        const update = ++updateId;
        const isCurrentUpdate = () => update === updateId;

        const iframe = createIframe(url, setIframeAttributes);
        const container = iframeContentContainer.value;
        const iframeUrl = new URL(url, window.location.href);
        const cleanUrl = iframeUrl.host + iframeUrl.pathname;

        // If there's no iframe yet, just append it.
        if (!container.firstChild) {
            watchIframe(iframe);
            container.appendChild(iframe);
            previousUrl.value = cleanUrl;
            return;
        }

        const shouldRefresh = target.refresh || hasIframeSourceChanged(container.firstChild.src, iframe.src);

        if (!shouldRefresh) {
            postMessageToIframe(url, payload);

            if (Statamic.$config.get('livePreview.hot_reload_contents', false)) {
                await hotReloadContents(container.firstChild, url, isCurrentUpdate);
            }

            return;
        }

        const isSameOrigin = iframeUrl.origin === window.location.origin;

        replaceIframe(container, iframe, isSameOrigin && cleanUrl === previousUrl.value);

        previousUrl.value = cleanUrl;
    }

    return {
        previousUrl,
        updateIframeContents,
        selectFields(enabled) {
            selecting = enabled;
            fieldSync?.select(enabled);
        },
        isSelectingFields() {
            return selecting;
        },
        highlightField(path) {
            selectedPath = path;
            highlightExpiresAt = Date.now() + fieldHighlightDuration;
            fieldSync?.highlight(path);
        },
        dispose() {
            updateId++;
            disconnectFields();
        },
    };
}
