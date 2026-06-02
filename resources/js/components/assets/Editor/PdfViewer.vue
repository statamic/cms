<script setup>
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { Icon } from '@ui';

const props = defineProps({
    src: {
        type: String,
        required: true,
    },
});

const pages = ref(null);
const isLoading = ref(true);
const isRendering = ref(false);
const hasError = ref(false);

let currentRenderId = 0;
let loadingTask = null;
let pdfDocument = null;
let observer = null;
let pageStates = [];

const supportsOffscreenCanvas = typeof OffscreenCanvas !== 'undefined';

onMounted(() => renderPdf());

onBeforeUnmount(() => cleanup());

watch(() => props.src, () => renderPdf());
watch(isRendering, (value) => Statamic.$progress.loading('pdf', value), { flush: 'sync' });

async function renderPdf() {
    const renderId = ++currentRenderId;

    cleanup({ invalidateRender: false });
    isLoading.value = true;
    isRendering.value = true;
    hasError.value = false;

    if (!props.src) {
        isLoading.value = false;
        isRendering.value = false;
        return;
    }

    try {
        const pdf = await loadDocument();

        if (renderId !== currentRenderId) return;

        pdfDocument = pdf;
        const viewerContext = await initViewer(pdf);

        if (renderId !== currentRenderId) return;

        const scale = window.devicePixelRatio || 2;

        // Phase 1: Create all page placeholders with correctly-sized empty canvases.
        // This gives the user the full scrollable document structure immediately,
        // while the actual rendering happens lazily via IntersectionObserver.
        for (let pageNumber = 1; pageNumber <= pdf.numPages; pageNumber++) {
            const page = await pdf.getPage(pageNumber);

            if (renderId !== currentRenderId) return;

            const viewport = page.getViewport({ scale });

            const pageContainer = document.createElement('div');
            pageContainer.className = 'pdf-page';
            pageContainer.dataset.pageNumber = String(pageNumber);

            const canvas = document.createElement('canvas');
            canvas.className = 'pdf-page-canvas';
            canvas.width = Math.floor(viewport.width);
            canvas.height = Math.floor(viewport.height);
            pageContainer.appendChild(canvas);

            pages.value?.appendChild(pageContainer);

            pageStates.push({
                pageNumber,
                page,
                viewport,
                canvas,
                container: pageContainer,
                rendered: false,
                rendering: false,
                renderTask: null,
            });
        }

        // Phase 2: Observe pages for viewport proximity and render on demand.
        // rootMargin pre-renders pages 200px before they scroll into view
        // to avoid blank flashes during normal scrolling.
        observer = new IntersectionObserver(
            (entries) => {
                for (const entry of entries) {
                    if (!entry.isIntersecting) continue;

                    const pageNumber = parseInt(entry.target.dataset.pageNumber, 10);
                    const state = pageStates[pageNumber - 1];

                    if (state && !state.rendered && !state.rendering) {
                        renderPage(state, renderId, viewerContext);
                    }
                }
            },
            {
                root: pages.value,
                rootMargin: '200px 0px',
            },
        );

        for (const state of pageStates) {
            observer.observe(state.container);
        }

        isLoading.value = false;
    } catch (error) {
        if (renderId === currentRenderId) {
            hasError.value = true;
            console.error(error);
        }
    } finally {
        if (renderId === currentRenderId) {
            isRendering.value = false;
        }
    }
}

async function renderPage(state, renderId, viewerContext) {
    if (renderId !== currentRenderId) return;

    state.rendering = true;

    try {
        const { canvas, page, viewport, container } = state;
        const { linkService, AnnotationLayerBuilder } = viewerContext;

        if (supportsOffscreenCanvas) {
            const offscreen = new OffscreenCanvas(canvas.width, canvas.height);
            const offCtx = offscreen.getContext('2d');

            const task = page.render({ canvasContext: offCtx, viewport });
            state.renderTask = task;
            await task.promise;

            if (renderId !== currentRenderId) return;

            // Transfer rendered pixels to the visible canvas.
            const visibleCtx = canvas.getContext('2d');
            visibleCtx.drawImage(offscreen, 0, 0);
        } else {
            const canvasContext = canvas.getContext('2d');
            if (!canvasContext) return;

            const task = page.render({ canvasContext, viewport });
            state.renderTask = task;
            await task.promise;
        }

        if (renderId !== currentRenderId) return;

        const annotationLayerBuilder = new AnnotationLayerBuilder({
            pdfPage: page,
            linkService,
            renderForms: true,
            onAppend: (div) => container.appendChild(div),
        });

        await annotationLayerBuilder.render({ viewport });

        state.rendered = true;

        // Release parsed operator lists and font data.
        // The canvas retains its rendered pixels.
        page.cleanup();
    } catch (error) {
        if (renderId !== currentRenderId) return;

        // Cancelled renders are expected during cleanup — not an error.
        if (error?.name === 'RenderingCancelledException') return;

        // Per-page error: show an indicator but keep the rest of the viewer alive.
        console.warn(`Failed to render PDF page ${state.pageNumber}:`, error);

        const errorDiv = document.createElement('div');
        errorDiv.className = 'pdf-page-error';
        errorDiv.textContent = `Page ${state.pageNumber} failed to render`;
        state.container.appendChild(errorDiv);
    } finally {
        state.rendering = false;
        state.renderTask = null;
    }
}

async function loadDocument() {
    const [pdfjsLib, { default: pdfjsWorkerUrl }] = await Promise.all([
        import('pdfjs-dist/build/pdf.mjs'),
        import('pdfjs-dist/build/pdf.worker.min.mjs?worker&url'),
    ]);

    pdfjsLib.GlobalWorkerOptions.workerSrc = pdfjsWorkerUrl;

    loadingTask = pdfjsLib.getDocument({
        url: props.src,
        verbosity: pdfjsLib.VerbosityLevel.ERRORS,
    });

    return await loadingTask.promise;
}

async function initViewer(pdf) {
    const { AnnotationLayerBuilder, EventBus, PDFLinkService } = await import('pdfjs-dist/web/pdf_viewer.mjs');
    const eventBus = new EventBus();
    const linkService = new PDFLinkService({ eventBus });

    // Internal links work, external links are blocked.
    linkService.externalLinkEnabled = false;
    linkService.setViewer({
        currentPageNumber: 1,
        pagesRotation: 0,
        isInPresentationMode: false,
        pageLabelToPageNumber: () => null,
        scrollPageIntoView: ({ pageNumber }) => {
            const state = pageStates[pageNumber - 1];

            if (state?.container) {
                state.container.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start',
                });
            }
        },
    });
    linkService.setDocument(pdf, null);

    return { linkService, AnnotationLayerBuilder };
}

function cleanup({ invalidateRender = true } = {}) {
    if (invalidateRender) {
        currentRenderId++;
    }

    isRendering.value = false;

    // Cancel all in-flight page renders.
    for (const state of pageStates) {
        if (state.renderTask) {
            state.renderTask.cancel();
            state.renderTask = null;
        }
    }

    if (observer) {
        observer.disconnect();
        observer = null;
    }

    if (loadingTask) {
        loadingTask.destroy();
        loadingTask = null;
    }

    if (pdfDocument) {
        pdfDocument.destroy();
        pdfDocument = null;
    }

    pageStates = [];

    if (pages.value) {
        pages.value.replaceChildren();
    }
}
</script>

<style>
.pdf-page {
    position: relative;
    max-width: 900px;
    margin: 0 auto 1rem;
}

.pdf-page-canvas {
    display: block;
    width: 100%;
    height: auto;
}

.pdf-page .annotationLayer {
    position: absolute;
    inset: 0;
}

.pdf-page-error {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    @apply text-sm text-gray-400;
}
</style>

<template>
    <div class="relative h-full min-h-0">
        <div v-if="isLoading || hasError" class="h-full flex items-center justify-center">
            <Icon v-if="isLoading" name="loading" class="text-gray-50" />
            <div v-if="hasError" class="text-gray-500 flex gap-2" v-text="__('Something went wrong')" />
        </div>

        <div ref="pages" class="pdf-pages h-full min-h-0 overflow-auto"></div>
    </div>
</template>
