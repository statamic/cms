<script>
import InlinePublishForm from './InlinePublishForm.vue';

export default {
    mixins: [InlinePublishForm],

    props: {
        site: String,
        itemUrl: String,
    },

    data() {
        return {
            creating: true,
            method: 'post',
            action: 'store',
            title: __('Create'),
        };
    },

    methods: {
        saved(response) {
            this.$emit('created', response.data.data);
            this.$nextTick(() => this.close());
        },
    },
};
</script>
