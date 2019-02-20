<script>
import InlinePublishForm from './InlinePublishForm.vue';

export default {

    mixins: [InlinePublishForm],

    props: {
        site: String,
    },

    data() {
        return {
            method: 'post',
            action: 'store',
            title: __('Create'),
            itemUrl: cp_url(`collections/blog/entries/create/${this.site}`)
        }
    },

    inject: ['storeName'],

    methods: {

        saved(response) {
            this.$emit('created', response.data.entry);
            this.$nextTick(() => this.close());
        }

    }

}
</script>

