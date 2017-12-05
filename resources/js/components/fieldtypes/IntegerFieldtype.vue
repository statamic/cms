<template>
    <input type="number" :class="classes" v-model="data" tabindex="0" :autofocus="autofocus" />
</template>

<script>

module.exports = {

    mixins: [Fieldtype],

    props: ['autofocus'],

    computed: {
        classes: function() {
            return 'form-control';
        }
    },

};
</script>
