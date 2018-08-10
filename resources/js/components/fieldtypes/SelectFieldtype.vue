<template>
    <div class="select select-full" :class="{ 'select--active': isActive }" :data-content="label">
        <select ref="input" @change="change" tabindex="0" @focus="isActive = true" @blur="isActive = false">
            <option v-for="option in selectOptions" :value="option.value" v-text="option.text"></option>
        </select>
    </div>
</template>

<script>

export default {

    mixins: [Fieldtype],

    props: {
        disabled: { default: false },
        options: { default: []},
        // placeholder: { required: false },
        value: { required: false },
    },

    data: function() {
        return {
            keyed: false,
            selectOptions: [],
            isActive: false,
        }
    },

    mounted() {
        if (this.options) {
            this.selectOptions = this.options;
        } else {
            this.selectOptions = this.config.options;
        }
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
        change(event) {
            this.$emit('input', event.target.value)
        },

        focus() {
            this.$refs.input.focus();
        },

        getReplicatorPreviewText() {
            return this.label;
        },
    }
};
</script>
