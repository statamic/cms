<template>
    <div id="pdfViewer" class="h-full text-white text-center flex flex-col justify-center items-center"></div>
</template>

<script>
import PDFObject from 'pdfobject';

export default {

    props: {
        src: {
            required: true
        }
    },

    mounted() {
        PDFObject.embed(this.src, "#pdfViewer");
    },
}
</script>
