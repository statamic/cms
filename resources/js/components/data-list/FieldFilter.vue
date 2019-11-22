<template>

    <div>

        <div class="flex items-center text-sm">

            <select-input
                class="w-1/3 mr-2"
                name="operator"
                v-model="filter.operator"
                placeholder=""
                :options="operatorOptions" />

            <div class="flex-1">
                <text-input name="value" :value="filter.value" @input="updateFilterValue" />
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

    computed: {
        operatorOptions() {
            return this.normalizeInputOptions(this.operators);
        },

        value() {
            return this.filter;
        }
    },

    watch: {
        value: {
            deep: true,
            handler(value) {
                this.ensureDefaults();
                this.$emit('updated', value);
            }
        }
    },

    methods: {
        ensureDefaults() {
            if (this.filter.field && ! this.filter.operator) {
                this.filter.operator = this.operatorOptions[0].value;
            }
        },

        updateFilterValue: _.debounce(function (value) {
            this.filter.value = value;
        }, 300)
    }

}
</script>
