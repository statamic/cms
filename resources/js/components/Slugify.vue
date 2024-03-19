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
            slug: null,
            shouldSlugify: this.enabled && !this.to
        }
    },

    watch: {

        from: {
            immediate: true,
            handler() {
                if (!this.shouldSlugify) {
                    this.slug = this.to;
                } else if (!this.from) {
                    this.slug = '';
                } else {
                    this.slugify();
                }
            }
        },

        to(to) {
            if (to !== this.slug) this.shouldSlugify = false;
        },

        slug(slug) {
            this.$emit('slugified', slug);
        }

    },

    render() {
        return this.$scopedSlots.default({});
    },

    methods: {

        reset() {
            if (this.enabled) {
                return this.slugify().then(() => this.shouldSlugify = true);
            }

            return Promise.resolve();
        },

        slugify() {
            return new Promise((resolve, reject) => {
                this.$slugify(this.from, this.separator, this.language).then((slug) => {
                    this.slug = slug;
                    resolve(slug);
                }).catch((error) => {
                    reject(error);
                });
            });
        }

    }

}
</script>
