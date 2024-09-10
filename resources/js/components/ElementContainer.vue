<script>
import ResizeObserver from 'resize-observer-polyfill';
import { vue_element } from '../node_helpers.js';

export default {
    emits: ['resized'],

    data() {
        return {
            width: null
        }
    },

    render() {
        return this.$slots.default({});
    },

    mounted() {
        const observer = new ResizeObserver(_.throttle(entries => {
            this.width = entries[0].contentRect.width;
        }, 200));

        observer.observe(vue_element(this.$el));
    },

    watch: {
        width(width) {
            this.$emit('resized', { width });
        }
    }
}
</script>
