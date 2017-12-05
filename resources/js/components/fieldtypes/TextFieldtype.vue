<template>
    <input :type="mode" :class="classes" v-model="data" tabindex="0" :autofocus="autofocus" />
</template>

<script>

module.exports = {

    mixins: [Fieldtype],

    props: ['autofocus'],

    data: function() {
    	return {
    		mode: this.config.mode || 'text'
    	}
    },

    computed: {
        classes: function() {
            return 'form-control type-' + this.mode;
        }
    },

};
</script>
