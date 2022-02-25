<template>
    <pdf :src="src">
        <template slot="loading">
            loading content here...
        </template>
    </pdf>
</template>

<script>
import Pdf from 'pdfvuer';

export default {

    components: {
        Pdf
    },

    props: {
        src: {
            type: Array,
            required: true
        }
    },

    computed: {},

    methods: {}

}
</script>
