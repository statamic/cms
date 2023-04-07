<template>

    <div class="flex items-center my-4" :class="{'text-red-500': status == 'error'}">

        <div class="mx-2 flex items-center">
            <svg-icon name="micro/warning" class="text-red-500 h-4 w-4" v-if="status === 'error'" />
            <loading-graphic v-else :inline="true" text="" />
        </div>

        <div class="filename">{{ basename }}</div>

        <div
            v-if="status !== 'error'"
            class="bg-white flex-1 h-4 mx-2 rounded"
        >
            <div class="bg-blue h-full rounded"
                :style="{ width: percent+'%' }" />
        </div>

        <div class="px-2" v-if="status === 'error'">
            {{ error }}
            <button @click.prevent="clear" class="flex items-center text-gray-700 hover:text-gray-800">
                <svg-icon name="micro/circle-with-cross" class="h-4 w-4" />
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
