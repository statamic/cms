<script>
import InlinePublishForm from '../inputs/relationship/InlinePublishForm.vue';

export default {

    mixins: [InlinePublishForm],

    props: {
        revision: Object
    },

    data() {
        return {
            creating: false,
            readOnly: true,
            method: 'patch',
            action: 'update',
            itemUrl: `${this.revision.attributes.id}/revisions/${this.revision.id}`,
        }
    },

    computed: {
        title() {
            return this.revision.attributes.data?.title ?? '';
        }
    },

    methods: {

        saved(response) {
            //
        }

    }

}
</script>
