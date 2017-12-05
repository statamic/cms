<template>

    <div class="asset-upload-listing">
        <div class="asset-upload-listing-header">
            <h6>{{ translate_choice('cp.uploads', uploads.length) }}</h6>
        </div>
        <table>
            <tbody>
                <tr is="upload"
                    v-for="(i, upload) in uploads"
                    :basename="upload.basename"
                    :extension="upload.extension"
                    :percent="upload.percent"
                    :error="upload.errorMessage"
                    @clear="clearUpload(i)">
                </tr>
            </tbody>
        </table>
    </div>

</template>


<script>
export default {

    props: ['uploads'],


    components: {
        Upload: require('./Upload.vue')
    },


    methods: {

        clearUpload(i) {
            this.uploads.splice(i, 1);
        }

    }

}
</script>
