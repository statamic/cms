<template>
    <select-input :value="value" @input="update" :options="config.options" />
</template>

<script>

export default {

    mixins: [Fieldtype],

    computed: {
        label: function() {
            // type juggle to make sure integers are treated as thus.
            const parsed = parseInt(this.data);
            const val = isNaN(parsed) ? this.data : parsed;

            var option = _.findWhere(this.selectOptions, {value: val});

            return (option) ? option.text : this.data;
        }
    },

    methods: {
        focus() {
            this.$refs.input.focus();
        },

        getReplicatorPreviewText() {
            return this.label;
        },
    }
};
</script>
