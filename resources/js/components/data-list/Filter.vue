<template>

    <li>
        <h6>{{ filter.title }}</h6>
        <select class="w-full" v-model="value">
            <option value="" :disabled="filter.required">--</option>
            <option
                v-for="option in filter.options"
                :key="option.value"
                :value="option.value"
                v-text="option.text" />
        </select>
    </li>

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
