<script setup>
import 'pdfjs-dist/web/pdf_viewer.css';
import * as pdfjsLib from 'pdfjs-dist/build/pdf.mjs';
import pdfjsWorkerUrl from 'pdfjs-dist/build/pdf.worker.min.mjs?worker&url';
import { AnnotationLayerBuilder, EventBus, PDFLinkService } from 'pdfjs-dist/web/pdf_viewer.mjs';
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

let currentRenderId = 0;
let loadingTask = null;
let pdfDocument = null;
let pageElements = [];

onMounted(() => renderPdf());

onBeforeUnmount(() => cleanup());

watch(() => props.src, () => renderPdf());

async function renderPdf() {
    const renderId = ++currentRenderId;

    cleanup({ invalidateRender: false });
    isLoading.value = true;

    if (!props.src) {
        isLoading.value = false;
        return;
    }

    try {
        const pdf = await loadDocument();

        if (renderId !== currentRenderId) return;

        pdfDocument = pdf;
        const linkService = createLinkService(pdf);

        for (let pageNumber = 1; pageNumber <= pdf.numPages; pageNumber++) {
            const page = await pdf.getPage(pageNumber);

            if (renderId !== currentRenderId) return;

            const viewport = page.getViewport({ scale: 1.25 });
            const pageContainer = document.createElement('div');
            pageContainer.className = 'pdf-page';
            pageContainer.dataset.pageNumber = pageNumber;
            pageContainer.style.width = `${viewport.width}px`;
            pageContainer.style.height = `${viewport.height}px`;

            const canvas = document.createElement('canvas');
            canvas.className = 'pdf-page-canvas';
            canvas.width = Math.floor(viewport.width);
            canvas.height = Math.floor(viewport.height);
            canvas.style.width = `${viewport.width}px`;
            canvas.style.height = `${viewport.height}px`;
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
        }
    } catch (error) {
        if (renderId === currentRenderId) {
            console.error(error);
        }
    } finally {
        if (renderId === currentRenderId) {
            isLoading.value = false;
        }
    }
}

async function loadDocument() {
    pdfjsLib.GlobalWorkerOptions.workerSrc = pdfjsWorkerUrl;

    loadingTask = pdfjsLib.getDocument({
        url: props.src,
        verbosity: pdfjsLib.VerbosityLevel.ERRORS,
    });

    return await loadingTask.promise;
}

function createLinkService(pdf) {
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

    return linkService;
}

function cleanup({ invalidateRender = true } = {}) {
    if (invalidateRender) {
        currentRenderId++;
    }

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
    margin: 0 auto 1rem;
}

.pdf-page-canvas {
    display: block;
}

.pdf-page .annotationLayer {
    inset: 0;
}
</style>

<template>
    <div class="relative h-full min-h-0">
        <div v-if="isLoading" class="h-full flex items-center justify-center text-gray-50">
            <Icon name="loading" />
        </div>

        <div ref="pages" class="pdf-pages h-full min-h-0 overflow-auto"></div>
    </div>
</template>
