<template>

    <div>

        <div class="flex items-center text-sm">

            <select-input
                class="w-1/4 mr-2"
                name="operator"
                v-model="filter.operator"
                :placeholder="__('Select Operator')"
                :options="[
                    { label: 'Equal to', value: '=' },
                    { label: 'Not equal to', value: '<>' },
                    { label: 'Like', value: 'like' },
                ]" />

            <div class="flex-1">
                <text-input name="value" v-model="filter.value" />
            </div>

        </div>

    </div>

</template>

<script>
export default {

    props: {
        field: Object,
        filter: {
            type: Object,
            required: true
        }
    },

    computed: {
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

    created() {
        this.ensureDefaults();
    },

    methods: {
        ensureDefaults() {
            if (! this.filter.operator) {
                this.filter.operator = '=';
            }
        }
    }

}
</script>
