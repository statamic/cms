<template>
    <div>
        <div class="flex bg-black text-white px-2 py-1 justify-center space-x-4 text-sm">
            <div class="flex">
                <a :title="__('Zoom In')" class="text-white p-1 text-xl leading-none" @click="scaleDown">
                    -
                </a>
                <a class="text-white p-1">
                    {{ formattedZoom }} %
                </a>
                <a title="__('Zoom Out')" class="text-white p-1 text-xl leading-none" @click="scaleUp">
                    +
                </a>
            </div>
        </div>
        <pdf :src="src" :scale.sync="scale" :page="page" :annotation="true" :resize="true" class="w-full h-full mx-2">
            <template slot="loading">
                {{ __('A wild PDF appears!') }}
            </template>
        </pdf>
    </div>
</template>

<script>
import Pdf from 'pdfvuer';

export default {

    components: {
        Pdf
    },

    props: {
        src: {
            type: String,
            required: true
        }
    },

    data () {
        return {
            page: 1,
            numPages: 0,
            scale: 'page-width'
        }
    },

    computed: {
        formattedZoom () {
            return Number.parseInt(this.scale * 100)
        },
    },

    methods: {
        scaleDown() {
            this.scale = this.scale * 0.85;
        },
        scaleUp() {
            this.scale = this.scale * 1.15;
        }
    },

}
</script>
