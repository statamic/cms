<template>
    <input :type="mode" :class="classes" v-model="data" tabindex="0" :autofocus="autofocus" />
</template>

<script>

module.exports = {

    mixins: [Fieldtype, AutoSlug],

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

    ready() {
        if (this.config.autoslug) {
            this.autoSlug(this.config.autoslug);
        }
    }

};
</script>
