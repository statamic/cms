<template>
    <div class="select-input-container">
        <select class="select-input" :name="name" @change="change" :value="value">
            <option v-text="placeholder" value="" v-if="placeholder" :selected="! value"></option>
            <option
                v-for="option in options"
                v-text="option.text"
                :value="option.value"
                :selected="option.value == value"
            ></option>
        </select>
        <div class="select-input-toggle">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                <path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/>
            </svg>
         </div>
     </div>
</template>

<script>
export default {
    props: {
        name: {},
        disabled: { default: false },
        options: { default: []},
        placeholder: { required: false },
        value: { required: true },
    },
    methods: {
        change(event) {
            this.$emit('input', event.target.value)
        }
    }

}
</script>
