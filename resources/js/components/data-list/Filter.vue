<template>

    <div class="mb-3">
        <h6 class="mb-1">{{ filter.title }}</h6>

        <select-input
            name="value"
            v-model="value"
            :options="filter.options" />
        <!-- todo bring back the blank, and whether its required -->
    </div>

</template>

<script>
export default {

    props: {
        filter: Object,
        initialValue: {}
    },

    data() {
        return {
            value: this.initialValue
        }
    },

    watch: {

        value(value) {
            this.$emit('changed', value);
        }

    }

}
</script>
