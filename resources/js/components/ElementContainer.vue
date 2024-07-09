<script>
    import ResizeObserver from 'resize-observer-polyfill';

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

            console.log(this.$el);

            // observer.observe(this.$el);
        },

        watch: {
            width(width) {
                this.$emit('resized', { width });
            }
        }
    }
</script>
