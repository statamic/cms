<template>
    <div class="radio-fieldtype-wrapper">
        <ul :class="[
            'list-unstyled',
            config.inline ? 'list-inline mb-0' : ''
        ]">
            <li
                v-for="(option, $index) in config.options"
                :key="$index"
                :class="{ 'mb-0 mr-2': config.inline }"
            >
                <input type="radio" :name="name" @input="update($event.target.value)" :value="option.value" :id="name + '-' + $index" />
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
            var option = _.findWhere(this.config.options, {value: this.value});
            return (option) ? option.text : this.value;
        },

    }
};
</script>
