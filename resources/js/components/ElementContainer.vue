<script>
import ResizeObserver from 'resize-observer-polyfill';

export default {

    data() {
        return {
            width: null
        }
    },

    render() {
        return this.$scopedSlots.default({});
    },

    mounted() {
        const observer = new ResizeObserver(_.throttle(entries => {
            this.width = entries[0].contentRect.width;
        }, 200));

        observer.observe(this.$el);
    },

    watch: {

        width(width) {
            this.$emit('resized', { width });
        }

    }

}
</script>
