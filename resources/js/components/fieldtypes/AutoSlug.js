export default {

    data() {
        return {
            autoSlugOptions: {
                isActive: true
            }
        };
    },

    computed: {

        autoSlugPublishFieldsComponent() {
            return this.$parent.$parent;
        }

    },

    methods: {

        autoSlug(from, to) {
            // The second argument indicates the name of the instance variable we should
            // be updating. If left blank, we'll assume it's the field's data variable.
            to = to || 'data';

            // If there is already data, we assume there's already a slug.
            // We don't want to automatically generate anything.
            if (this[to]) return;

            // Whenever the "to" field is modified. ie, when the slug field is edited...
            this.$watch(to, (slug) => {
                const fromVal = this.autoSlugPublishFieldsComponent.data[from] || '';
                // Mark it modified if the slug matches the slugified version. This allows
                // the automatic slugification to recommence if the slug is modified to
                this.autoSlugOptions.isActive = slug === this.$slugify(fromVal);
            });

            // Whenever the "from" field is modified. ie, when the watched field is edited...
            this.autoSlugPublishFieldsComponent.$watch(`data.${from}`, (value) => {
                if (!this.autoSlugOptions.isActive) return;
                this[to] = this.$slugify(value);
            });
        }

    }

};
