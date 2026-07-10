<script>
import { h } from 'vue';
import striptags from 'striptags';

export default {
    props: {
        value: { required: true },
    },

    data() {
        return {
            truncateAt: 50,
        };
    },

    computed: {
        text() {
            let value = this.value;

            if (value === 0) return 0;

            if (!value) return '';

            if (typeof value !== 'string') return JSON.stringify(value);

            value = striptags(value);

            if (value.length > this.truncateAt) {
                value = value.substring(0, this.truncateAt) + '\u2026';
            }

            return value;
        },
    },

    render() {
        return h('div', { textContent: this.text });
    },
};
</script>
