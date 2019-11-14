<script>
export default {

    props: {
        value: { required: true }
    },

    data() {
        return {
            truncateAt: 50
        }
    },

    computed: {

        text() {
            if (!this.value) return '';

            if (typeof this.value !== 'string') return JSON.stringify(this.value);

            if (this.value.length > this.truncateAt) {
                return this.value.substring(0, this.truncateAt) + '&hellip;';
            }

            return this.value;
        }

    },

    render(h) {
        return h('div', { domProps: { innerHTML: this.text }});
    }

}
</script>
