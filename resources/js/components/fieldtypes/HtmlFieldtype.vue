<template>
    <div v-html="html" />
</template>

<script>
import Dompurify from 'dompurify';

export default {
    mixins: [Fieldtype],
    computed: {
        html() {
            return Dompurify.sanitize(this.config.html);
        }
    }
};
</script>
