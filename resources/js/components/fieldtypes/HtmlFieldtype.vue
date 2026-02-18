<template>
    <div v-html="html" />
</template>

<script>
import DOMPurify from 'dompurify';

export default {
    mixins: [Fieldtype],
    computed: {
        html() {
            return DOMPurify.sanitize(this.config.html);
        }
    }
};
</script>
