<script>
import InlinePublishForm from './InlinePublishForm.vue';

export default {

    mixins: [InlinePublishForm],

    props: {
        item: Object
    },

    data() {
        return {
            creating: false,
            method: 'patch',
            action: 'update',
            title: this.item.title,
            itemUrl: this.item.edit_url
        }
    },

    methods: {

        saved(response) {
            this.$emit('updated', response.data.data);
        }

    }

}
</script>
