<script>
import ResizeObserver from 'resize-observer-polyfill';
import throttle from '@/util/throttle.js';

export default {
    emits: ['resized'],

    data() {
        return {
            width: null,
        };
    },

    render() {
        return this.$slots.default({})[0];
    },

    mounted() {
        const observer = new ResizeObserver(
            throttle((entries) => {
                this.width = entries[0].contentRect.width;
            }, 200),
        );

        observer.observe(this.$el);
    },

    watch: {
        width(width) {
            this.$emit('resized', { width });
        },
    },
};
</script>
