<template>
    <div class="h-full">
        <div v-if="isLoading || hasError" class="h-full flex items-center justify-center">
            <loading-graphic v-if="isLoading" :text="null" />
            <div v-if="hasError" class="text-gray-500 flex gap-2" v-text="__('Something went wrong')" />
        </div>

        <div ref="pages" class="pdf-pages h-full overflow-auto" />
    </div>
</template>

<script>
import * as pdfjsLib from 'pdfjs-dist/build/pdf.mjs';
import pdfjsWorkerUrl from 'pdfjs-dist/build/pdf.worker.min.mjs?worker&url';
import { AnnotationLayerBuilder, EventBus, PDFLinkService } from 'pdfjs-dist/web/pdf_viewer.mjs';

export default {

    props: {
        src: {
            required: true
        }
    },

    data() {
        return {
            isLoading: true,
            isRendering: false,
            hasError: false,
            currentRenderId: 0,
            loadingTask: null,
            pdfDocument: null,
            pageElements: [],
            parentInlineStyles: null,
        };
    },

    watch: {
        src() {
            this.renderPdf();
        },
        isRendering: {
            handler(value) {
                Statamic.$progress.loading('pdf', value);
            },
            flush: 'sync',
        },
    },

    mounted() {
        this.applyParentSizingFix();
        this.renderPdf();
    },

    beforeDestroy() {
        this.cleanup();
        this.restoreParentSizingFix();
    },

    methods: {
        async renderPdf() {
            const renderId = ++this.currentRenderId;

            this.cleanup({ invalidateRender: false });
            this.isLoading = true;
            this.isRendering = true;
            this.hasError = false;

            if (!this.src) {
                this.isLoading = false;
                this.isRendering = false;
                return;
            }

            try {
                const pdf = await this.loadDocumentWithFallback();

                if (renderId !== this.currentRenderId) return;

                this.pdfDocument = pdf;
                const linkService = this.createLinkService(pdf);
                const pages = this.$refs.pages;

                for (let pageNumber = 1; pageNumber <= pdf.numPages; pageNumber++) {
                    const page = await pdf.getPage(pageNumber);

                    if (renderId !== this.currentRenderId) return;

                    const viewport = page.getViewport({ scale: 2 });
                    const pageContainer = document.createElement('div');
                    pageContainer.className = 'pdf-page';
                    pageContainer.dataset.pageNumber = String(pageNumber);

                    const canvas = document.createElement('canvas');
                    canvas.className = 'pdf-page-canvas';
                    canvas.width = Math.floor(viewport.width);
                    canvas.height = Math.floor(viewport.height);
                    pageContainer.appendChild(canvas);

                    pages.appendChild(pageContainer);
                    this.pageElements.push(pageContainer);

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

                    if (pageNumber === 1 && renderId === this.currentRenderId) {
                        this.isLoading = false;
                    }
                }
            } catch (error) {
                if (renderId === this.currentRenderId) {
                    this.hasError = true;
                    console.error(error);
                }
            } finally {
                if (renderId === this.currentRenderId) {
                    this.isLoading = false;
                    this.isRendering = false;
                }
            }
        },

        async loadDocumentWithFallback() {
            pdfjsLib.GlobalWorkerOptions.workerSrc = pdfjsWorkerUrl;

            this.loadingTask = pdfjsLib.getDocument({
                url: this.src,
                verbosity: pdfjsLib.VerbosityLevel.ERRORS,
            });

            return await this.loadingTask.promise;
        },

        createLinkService(pdfDocument) {
            const eventBus = new EventBus();
            const linkService = new PDFLinkService({ eventBus });

            linkService.externalLinkEnabled = false;
            linkService.setViewer({
                currentPageNumber: 1,
                pagesRotation: 0,
                isInPresentationMode: false,
                pageLabelToPageNumber: () => null,
                scrollPageIntoView: ({ pageNumber }) => {
                    const pageElement = this.pageElements[pageNumber - 1];

                    if (pageElement) {
                        pageElement.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start',
                        });
                    }
                },
            });
            linkService.setDocument(pdfDocument, null);

            return linkService;
        },

        applyParentSizingFix() {
            const parent = this.$el?.parentElement;
            if (!parent) return;

            this.parentInlineStyles = {
                height: parent.style.height,
                flex: parent.style.flex,
                minHeight: parent.style.minHeight,
            };

            // This component lives under a wrapper with `h-full` inside a flex column.
            // Make that wrapper a flex item so it uses remaining space under the toolbar.
            parent.style.height = 'auto';
            parent.style.flex = '1 1 auto';
            parent.style.minHeight = '0';
        },

        restoreParentSizingFix() {
            const parent = this.$el?.parentElement;
            if (!parent || !this.parentInlineStyles) return;

            parent.style.height = this.parentInlineStyles.height;
            parent.style.flex = this.parentInlineStyles.flex;
            parent.style.minHeight = this.parentInlineStyles.minHeight;
            this.parentInlineStyles = null;
        },

        cleanup({ invalidateRender = true } = {}) {
            if (invalidateRender) {
                this.currentRenderId++;
            }

            this.isRendering = false;

            if (this.loadingTask) {
                this.loadingTask.destroy();
                this.loadingTask = null;
            }

            if (this.pdfDocument) {
                this.pdfDocument.destroy();
                this.pdfDocument = null;
            }

            this.pageElements = [];

            if (this.$refs.pages) {
                this.$refs.pages.replaceChildren();
            }
        },
    },
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
    z-index: 2;
}
</style>
