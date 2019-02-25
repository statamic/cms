<template>
    <!-- <select-input :name="name" :value="value" @input="update" :options="config.options" /> -->
    <v-select :options="['foo', 'bar']"></v-select>
</template>

<script>

import vSelect from 'vue-select'

export default {

    mixins: [Fieldtype],

    components: {
        'v-select': vSelect
    },

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
