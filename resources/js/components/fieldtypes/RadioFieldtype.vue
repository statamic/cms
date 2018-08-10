<template>
    <div class="radio-fieldtype-wrapper">
        <ul :class="[
            'list-unstyled',
            config.inline ? 'list-inline mb-0' : ''
        ]">
            <li v-for="option in config.options" :class="{ 'mb-0 mr-2': config.inline }">
                <input type="radio" v-model="data" :value="option.value" :id="name + '-' + $index" />
                <label :for="name + '-' + $index">{{ option.text }}</label>
            </li>
        </ul>
    </div>
</template>

<script>
export default {
    mixins: [Fieldtype],

    methods: {

        focus() {
            document.getElementById(`${this.name}-0`).focus();
        },

        getReplicatorPreviewText() {
            var option = _.findWhere(this.config.options, {value: this.data});
            return (option) ? option.text : this.data;
        },

    }
};
</script>
