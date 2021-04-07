<script>
export default {

    props: {
        config: Object,
        initialValue: {},
        initialMeta: {},
    },

    data() {
        return {
            meta: this.initialMeta,
            value: this.initialValue,
            loading: false,
        }
    },

    computed: {

        isPreloadable() {
            return this.$config.get('preloadableFieldtypes').includes(this.config.type);
        }

    },

    render(h) {
        return this.$scopedSlots.default({
            meta: this.meta,
            value: this.value,
            loading: this.loading,
        });
    },

    created() {
        if (! this.isPreloadable) return;

        // For hardcoded field components (ie. when used inside a custom form, without being generated from
        // a blueprint) the developer wouldn't pass in a `meta` prop to the `form-group` component. In
        // this case, we'll want to lazy-load the preloaded meta data, and the pre-processed value.
        if (this.initialMeta === undefined) this.load();
    },

    watch: {

        initialValue(value) {
            this.value = value;
        },

        initialMeta(meta) {
            this.meta = meta;
        }

    },

    methods: {

        load() {
            this.loading = true;

            const params = {
                config: utf8btoa(JSON.stringify(this.config)),
                value: this.value,
            };

            this.$axios.get(cp_url('fields/field-meta'), { params }).then(response => {
                this.meta = response.data.meta;
                this.value = response.data.value;
                this.loading = false;
            });
        }

    }

}
</script>
