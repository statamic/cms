<script>
import PublishForm from './PublishForm.vue';

export default {

    mixins: [PublishForm],

    methods: {

        submitComplete(response) {
            window.location = response.data.redirect;
        }

    }

}
</script>
