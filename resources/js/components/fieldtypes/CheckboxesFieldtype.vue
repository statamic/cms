<template>
    <ul class="list-unstyled">
        <li v-for="(option, $index) in config.options" :key="$index">
            <input type="checkbox"
                   :name="name + '[]'"
                   :id="name + '-' + $index"
                   :value="option.value"
                   v-model="values"
            />
            <label :for="name + '-' + $index">{{ option.text }}</label>
        </li>
    </ul>
</template>

<script>
export default {

    mixins: [Fieldtype],

    data() {
        return {
            values: this.value || []
        }
    },

    watch: {

        values(values) {
            this.update(values);
        }

    },

    methods: {

        focus() {
            document.getElementById(`${this.name}-0`).focus();
        },

        getReplicatorPreviewText() {
            return this.values.map(item => {
                var option = _.findWhere(this.config.options, {value: item});
                return (option) ? option.text : item;
            }).join(', ');
        },

    }
};
</script>
