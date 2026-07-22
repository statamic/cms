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
let pageStates = [];
let observer = null;

const scale = Math.min(2, window.devicePixelRatio || 2);

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

        // Create page placeholders with sized canvases for scrollable layout
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

        // Observe pages for viewport proximity and render on demand
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

        if (pageStates.length > 0) {
            await renderPage(pageStates[0], renderId, viewerContext);
        }

        if (renderId !== currentRenderId) return;

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

        const canvasContext = canvas.getContext('2d');
        if (!canvasContext) return;

        const task = page.render({ canvasContext, viewport });
        state.renderTask = task;
        await task.promise;

        if (renderId !== currentRenderId) return;

        await new AnnotationLayerBuilder({
            pdfPage: page,
            linkService,
            renderForms: true,
            onAppend: (div) => container.appendChild(div),
        }).render({ viewport });

        state.rendered = true;
    } catch (error) {
        if (renderId !== currentRenderId) return;
        if (error?.name === 'RenderingCancelledException') return;

        const errorDiv = document.createElement('div');
        errorDiv.className = 'pdf-page-error';
        errorDiv.textContent = __('Something went wrong');
        state.container.appendChild(errorDiv);

        console.warn(`Failed to render PDF page ${state.pageNumber}`, error);
    } finally {
        state.page.cleanup();
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

    for (const state of pageStates) {
        state.renderTask?.cancel();
        state.renderTask = null;
    }

    observer?.disconnect();
    observer = null;

    loadingTask?.destroy();
    loadingTask = null;

    pdfDocument?.destroy();
    pdfDocument = null;

    pageStates = [];

    pages.value?.replaceChildren();
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
    inset: 0;
}

.pdf-page-error {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>

<template>
    <div class="relative h-full min-h-0">
        <div v-if="isLoading || hasError" class="h-full flex items-center justify-center">
            <Icon v-if="isLoading" name="loading" class="text-gray-50" />
            <div v-if="hasError" class="text-gray-500 flex gap-2" v-text="__('Something went wrong')" />
        </div>

        <div ref="pages" class="pdf-pages h-full min-h-0 overflow-auto text-gray-500"></div>
    </div>
</template>
