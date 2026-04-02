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
let pageElements = [];

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
        const { linkService, AnnotationLayerBuilder } = await initViewer(pdf);

        for (let pageNumber = 1; pageNumber <= pdf.numPages; pageNumber++) {
            const page = await pdf.getPage(pageNumber);

            if (renderId !== currentRenderId) return;

            const viewport = page.getViewport({ scale: 2 });
            const pageContainer = document.createElement('div');
            pageContainer.className = 'pdf-page';
            pageContainer.dataset.pageNumber = String(pageNumber);

            const canvas = document.createElement('canvas');
            canvas.className = 'pdf-page-canvas';
            canvas.width = Math.floor(viewport.width);
            canvas.height = Math.floor(viewport.height);
            pageContainer.appendChild(canvas);

            pages.value?.appendChild(pageContainer);
            pageElements.push(pageContainer);

            const canvasContext = canvas.getContext('2d');
            if (!canvasContext) continue;

            await page.render({
                canvasContext,
                viewport,
            }).promise;

            const annotationLayerBuilder = new AnnotationLayerBuilder({
                pdfPage: page,
                linkService,
                renderForms: true,
                onAppend: (div) => pageContainer.appendChild(div),
            });

            await annotationLayerBuilder.render({ viewport });

            if (pageNumber === 1 && renderId === currentRenderId) {
                isLoading.value = false;
            }
        }
    } catch (error) {
        if (renderId === currentRenderId) {
            hasError.value = true;
            console.error(error);
        }
    } finally {
        if (renderId === currentRenderId) {
            isLoading.value = false;
            isRendering.value = false;
        }
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
            const pageElement = pageElements[pageNumber - 1];

            if (pageElement) {
                pageElement.scrollIntoView({
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

    if (loadingTask) {
        loadingTask.destroy();
        loadingTask = null;
    }

    if (pdfDocument) {
        pdfDocument.destroy();
        pdfDocument = null;
    }

    pageElements = [];

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
    inset: 0;
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
