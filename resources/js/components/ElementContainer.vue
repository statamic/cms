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
        this.throttledCallback = throttle((entries) => {
            this.width = entries[0].contentRect.width;
        }, 200);

        this.observer = new ResizeObserver(this.throttledCallback);
        this.observer.observe(this.$el);
    },

    beforeUnmount() {
        this.observer.disconnect();
        this.throttledCallback.cancel();
    },

    watch: {
        width(width) {
            this.$emit('resized', { width });
        },
    },
};
</script>
