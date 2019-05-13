<script>
import PublishForm from './PublishForm.vue';

export default {

    mixins: [PublishForm],

    methods: {

        submitComplete(response) {
            this.title = response.data.title;
            this.$notify.success('Saved');
            this.$refs.container.saved();
        }

    }

}
</script>
