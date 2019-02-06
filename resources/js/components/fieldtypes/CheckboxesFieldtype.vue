<template>
    <div class="checkboxes-fieldtype-wrapper"  :class="{'inline-mode': config.inline}">
        <div class="option" v-for="(option, $index) in config.options" :key="$index">
            <input type="checkbox"
                   :name="name + '[]'"
                   :id="name + $index"
                   :value="option.value"
                   v-model="values"
            />
            <label :for="name + $index">{{ option.text }}</label>
        </div>
    </div>
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
