<template>

    <div>

        <div class="flex items-center text-sm">

            <select-input
                class="w-1/3 mr-2"
                name="operator"
                :value="filter.operator"
                placeholder=""
                :options="operatorOptions"
                @input="onOperatorUpdated" />

            <div class="flex-1">
                <text-input
                    name="value"
                    :value="filter.value"
                    @input="onValueUpdated" />
            </div>

        </div>

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

        onValueUpdated(value) {
            this.filter.value = value;
            this.update();
        },

        update() {
            this.$emit('updated', this.filter);
        }
    }

}
</script>
