<template>
    <a :href="href" :target="target" @click="selectAndClose" v-tooltip="text">
        <svg-icon :name="icon" class="h-3 w-3" />
    </a>
</template>

<script>
export default {

    props: ['text', 'icon', 'redirect', 'externalLink'],

    computed: {

        href() {
            return this.redirect || this.externalLink;
        },

        target() {
            return this.externalLink ? '_blank' : null;
        },

    },

    methods: {

        selectAndClose($event) {
            if (this.href) {
                return;
            }

            this.$emit('click', $event);
        },

    }

}
</script>
