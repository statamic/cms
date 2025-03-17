<template>
    <div id="pdfViewer" class="flex h-full flex-col items-center justify-center text-center text-white"></div>
</template>

<script>
import PDFObject from 'pdfobject';

export default {
    props: {
        src: {
            required: true,
        },
    },

    mounted() {
        PDFObject.embed(this.src, '#pdfViewer');
    },
};
</script>
