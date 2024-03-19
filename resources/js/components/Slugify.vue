<script>
export default {

    model: {
        prop: 'to',
        event: 'slugified'
    },

    props: {
        from: String,
        to: String,
        language: String,
        separator: {
            type: String,
            default: '-'
        },
        enabled: {
            type: Boolean,
            default: true
        }
    },

    data() {
        return {
            shouldSlugify: this.enabled
        }
    },

    watch: {

        from(from) {
            if (!this.shouldSlugify) return this.to;
            if (!from) return this.$emit('slugified', '');

            this.slugify();
        },

    },

    created() {
        if (this.to) {
            this.shouldSlugify = false;
        }
    },

    render() {
        return this.$scopedSlots.default({});
    },

    methods: {

        reset() {
            if (this.enabled) {
                this.shouldSlugify = true;
                return this.slugify();
            }

            return Promise.resolve();
        },

        slugify() {
            return new Promise((resolve, reject) => {
                this.$slugify(this.from, this.separator, this.language).then((slug) => {
                    this.$emit('slugified', slug);
                    resolve(slug);
                }).catch((error) => {
                    reject(error);
                });
            });
        }

    }

}
</script>
