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

    computed: {

        async slug() {
            if (!this.shouldSlugify) return this.to;
            if (!this.from) return '';

            // use generateSlug method and get the response.data.slug from it
            // return this.generateSlug().data.slug;


            // return await this.$slugify(this.from, this.separator, this.language);
        }

    },

    watch: {

        to(to) {
            if (to !== this.slug) this.shouldSlugify = false;
        },

        slug(slug) {
            this.$emit('slugified', slug);
        }

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

        // async generateSlug() {
        //     return await this.$axios.post(cp_url('fieldtypes/slug'), {
        //         from: this.from,
        //         separator: this.separator,
        //         language: this.language
        //     });
        // },

        reset() {
            if (this.enabled) this.shouldSlugify = true;
        }

    }

}
</script>
