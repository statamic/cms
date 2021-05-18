<template>
    <a :href="href" :target="target" @click="selectAndClose">
        <!-- Pass prop text OR child component into slot -->
        <template v-if="text">{{ text }}</template>
        <slot></slot>
    </a>
</template>

<script>
export default {

    props: ['text', 'redirect', 'externalLink'],

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

            this.$parent.close();
        },

    }

}
</script>
