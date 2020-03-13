<template>

    <div class="">
        <select-input
            class="w-full mb-1"
            name="operator"
            :value="filter.operator"
            placeholder=""
            :options="operatorOptions"
            @input="onOperatorUpdated" />

        <text-input
            class="w-full"
            name="value"
            :value="filter.value"
            @input="onValueUpdated" />
    </div>

</template>

<script>
import HasInputOptions from '../fieldtypes/HasInputOptions.js';

export default {

    mixins: [HasInputOptions],

    props: {
        operators: {
            type: Object,
            required: true
        },
        filter: {
            type: Object,
            required: true
        }
    },

    mounted() {
        if (this.filter && !this.filter.operator && this.operatorOptions[0].value) {
            this.onOperatorUpdated(this.operatorOptions[0].value);
        }
    },

    computed: {
        operatorOptions() {
            return this.normalizeInputOptions(this.operators);
        },
    },

    methods: {
        onOperatorUpdated(operator) {
            this.filter.operator = operator;
            this.update();
        },

        onValueUpdated: _.debounce(function (value) {
            this.filter.value = value;
            this.update();
        }, 300),

        update() {
            this.$emit('updated', this.filter);
        }
    }

}
</script>
