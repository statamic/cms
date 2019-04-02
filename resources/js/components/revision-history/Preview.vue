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
            title: this.revision.attributes.data.title,
            itemUrl: `${document.location.pathname}/revisions/${this.revision.id}`,
        }
    },

    methods: {

        saved(response) {
            //
        }

    }

}
</script>
