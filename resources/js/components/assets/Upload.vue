<template>

    <div class="flex items-center my-2"
        :class="{
            'text-red': status == 'error',
            'text-green': status === 'pending'
        }"
    >

        <div class="mx-1">
            <span class="icon icon-warning error" v-if="status === 'error'"></span>
            <loading-graphic v-else :inline="true" text="" />
        </div>

        <div class="w-6 mr-1">
            <file-icon :extension="extension"></file-icon>
        </div>

        <div class="filename">{{ basename }}</div>

        <div
            v-if="status !== 'error'"
            class="bg-white flex-1 h-4 mx-1 rounded"
        >
            <div class="bg-blue h-full rounded"
                :style="{ width: percent+'%' }" />
        </div>

        <div class="ml-1" v-if="status === 'error'">
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
