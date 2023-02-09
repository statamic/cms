<template>

    <div class="flex items-center my-4___REPLACED"
        :class="{
            'text-red': status == 'error',
            'text-green': status === 'pending'
        }"
    >

        <div class="mx-2___REPLACED">
            <span class="icon icon-warning error" v-if="status === 'error'"></span>
            <loading-graphic v-else :inline="true" text="" />
        </div>

        <div class="w-6 mr-2___REPLACED">
            <file-icon :extension="extension"></file-icon>
        </div>

        <div class="filename">{{ basename }}</div>

        <div
            v-if="status !== 'error'"
            class="bg-white flex-1 h-4 mx-2___REPLACED rounded"
        >
            <div class="bg-blue h-full rounded"
                :style="{ width: percent+'%' }" />
        </div>

        <div class="ml-2___REPLACED" v-if="status === 'error'">
            {{ error }}
            <button v-if="status == 'error'" @click.prevent="clear">
                <i class="icon icon-circle-with-cross"></i>
            </button>
        </div>

    </div>

</template>


<script>
export default {

    props: ['extension', 'basename', 'percent', 'error'],


    computed: {

        status() {
            if (this.error) {
                return 'error';
            } else if (this.percent === 100) {
                return 'pending';
            } else {
                return 'uploading';
            }
        }

    },


    methods: {

        clear() {
            this.$emit('clear');
        }

    }

}
</script>
