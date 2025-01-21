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
        },
        async: {
            type: Boolean,
            default: true
        }
    },

    data() {
        let slugifier = this.$slug.in(this.language).separatedBy(this.separator);
        if (this.async) slugifier.async();

        return {
            slugifier,
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
            if (! this.enabled) return Promise.resolve();

            // If the slug doesn't change, we'll emit the event manually.
            // The watcher will only emit the event if the slug changes.
            const initialSlug = this.slug;

            return this.slugify().then(() => {
                this.shouldSlugify = true;
                if (this.slug === initialSlug) this.$emit('slugified', this.slug);
            });

        },

        slugify() {
            if (! this.async) {
                return new Promise((resolve, reject) => {
                    const slug = this.slugifier.create(this.from);
                    this.slug = slug;
                    resolve(slug);
                });
            }

            return new Promise((resolve, reject) => {
                this.$emit('slugifying');
                this.slugifier.create(this.from).then(slug => {
                    this.slug = slug;
                    resolve(slug);
                }).catch(error => reject(error));
            });
        }

    }

}
</script>
