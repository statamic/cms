<template>
    <button @click="selectAndClose">
        <slot>{{ text }}</slot>
    </button>
</template>

<script>
export default {

    props: ['text', 'redirect', 'external_link'],

    methods: {
        selectAndClose() {
            if (this.redirect) {
                location.href = this.redirect;
                return;
            }

            if (this.external_link) {
                window.open(this.external_link, '_blank');
                return;
            }

            this.$emit('click');

            this.$parent.close();
        }
    }

}
</script>
