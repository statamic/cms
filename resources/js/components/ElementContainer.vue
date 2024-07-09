<script>
import ResizeObserver from 'resize-observer-polyfill';
import { first_child } from '../node_helpers.js';

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

        observer.observe(first_child(this.$el.parentNode));
    },

    watch: {
        width(width) {
            this.$emit('resized', { width });
        }
    }
}
</script>
