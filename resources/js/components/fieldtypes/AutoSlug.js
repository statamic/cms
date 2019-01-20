export default {

    data() {
        return {
            autoSlugFromField: null,
            autoSlugOptions: {
                isActive: true
            }
        };
    },

    computed: {

        autoSlugFromValue() {
            if (!this.autoSlugFromField) return;
            return this.$store.state.publish[this.storeName].values[this.autoSlugFromField];
        }

    },

    methods: {

        autoSlug(from, to) {
            this.autoSlugFromField = from;

            // The second argument indicates the name of the instance variable we should
            // be updating. If left blank, we'll assume it's the field's value variable.
            to = to || 'value';

            // If there is already data, we assume there's already a slug.
            // We don't want to automatically generate anything.
            if (this[to]) return;

            // Whenever the "to" field is modified. ie, when the slug field is edited...
            this.$watch(to, (slug) => {
                const fromVal = this.autoSlugFromValue || '';
                // Mark it modified if the slug matches the slugified version. This allows
                // the automatic slugification to recommence if the slug is modified to
                this.autoSlugOptions.isActive = slug === this.$slugify(fromVal);
            });

            // Whenever the "from" field is modified. ie, when the watched field is edited...
            this.$watch('autoSlugFromValue', (value) => {
                if (!this.autoSlugOptions.isActive) return;

                const slugified = this.$slugify(value);

                // If the target is "value", we want to emit an event rather than modifying
                // the prop. Otherwise, we can just modify the specified instance variable.
                (to === 'value') ? this.update(slugified) : this[to] = slugified;
            });
        }

    }

};
