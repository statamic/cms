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
            let value = this.value;

            if (value === 0) return 0;

            if (!value) return '';

            if (typeof value !== 'string') return JSON.stringify(value);

            // Basic html stripping. https://stackoverflow.com/a/5002161
            value = value.replace(/<\/?[^>]+(>|$)/g, '');

            if (value.length > this.truncateAt) {
                value = value.substring(0, this.truncateAt) + '&hellip;';
            }

            return value;
        }

    },

    render(h) {
        return h('div', { domProps: { innerHTML: this.text }});
    }

}
</script>
