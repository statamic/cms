<template>
    <button @click="selectAndClose">
        <slot>{{ text }}</slot>
    </button>
</template>

<script>
export default {

    props: ['text', 'redirect', 'externalLink'],

    methods: {
        selectAndClose() {
            if (this.redirect) {
                location.href = this.redirect;
                return;
            }

            if (this.externalLink) {
                window.open(this.externalLink, '_blank');
                return;
            }

            this.$emit('click');

            this.$parent.close();
        }
    }

}
</script>
