<template>
    <div class="radio-fieldtype-wrapper">
        <ul :class="[
            'list-unstyled',
            config.inline ? 'list-inline' : ''
        ]">
            <li v-for="option in config.options">
                <input type="radio" v-model="data" :value="option.value" :id="name + '-' + $index" />
                <label :for="name + '-' + $index">{{ option.text }}</label>
            </li>
        </ul>
    </div>
</template>

<script>
module.exports = {
    mixins: [Fieldtype],

    methods: {

        focus() {
            console.log(`${this.name}-0`);
            document.getElementById(`${this.name}-0`).focus();
        }

    }
};
</script>
