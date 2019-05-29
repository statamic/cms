<template>
    <li @click="selectAndClose"><a v-text="text"></a></li>
</template>

<script>
export default {
    props: {
        text: {
            type: String,
            required: true
        },
        redirect: {
            type: String
        }
    },

    methods: {
        selectAndClose() {
            if (this.redirect) {
                location.href = this.redirect;
                return;
            }

            this.$emit('selected');

            this.$parent.close();
        }
    }
}
</script>
