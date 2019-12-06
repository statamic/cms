<template>

    <div class="focal-point">
        <div class="focal-point-toolbox card p-0">
            <div class="p-2">
                <label>{{ __('Focal Point') }}</label>
                <small class="help-block">{{ __('messages.focal_point_instructions') }}</small>
                <div class="focal-point-image">
                    <img ref="image" :src="image" @click="define" @load="setImageDimensions" />
                    <div class="focal-point-reticle" :class="{ zoomed: z > 1 }" :style="{
                        top: `${y}%`,
                        left: `${x}%`,
                        width: `${reticleSize}px`,
                        height: `${reticleSize}px`,
                        marginTop: `-${reticleSize/2}px`,
                        marginLeft: `-${reticleSize/2}px`,
                    }"></div>
                </div>
            </div>
            <div class="flex items-center text-sm justify-center mb-2">
                <div class="flex items-center mx-2">
                    <div class="mr-sm">X</div>
                    <div class="value">{{ x }}<sup>%</sup></div>
                </div>
                <div class="flex items-center mx-2">
                    <div class="mr-sm">Y</div>
                    <div class="value">{{ y }}<sup>%</sup></div>
                </div>
                <div class="flex items-center mx-2">
                    <div class="mr-sm">Z</div>
                    <div class="value">{{ z }}</div>
                </div>
            </div>
            <div class="px-2">
                <input type="range" v-model="z" min="1" max="10" step="0.1" class="w-full mb-2" />
                <div class="mb-1 flex flex-wrap items-center justify-center">
                    <button type="button" class="btn mb-1" @click.prevent="close">{{ __('Cancel') }}</button>
                    <button type="button" class="btn mb-1 btn-default mx-1" @click.prevent="reset">{{ __('Reset') }}</button>
                    <button type="button" class="btn mb-1 btn-primary" @click="select">{{ __('Finish') }}</button>
                </div>
            </div>
            <h6 class="p-2 text-center bg-grey-30 rounded-b">{{ __('messages.focal_point_previews_are_examples') }}</h6>
        </div>
        <div v-for="n in 9" :key="n"
             :class="`frame frame-${n}`">
            <focal-point-preview-frame v-if="imageDimensions" :x="x" :y="y" :z="z" :image-url="image" :image-dimensions="imageDimensions" />
        </div>
    </div>

</template>

<script>
import FocalPointPreviewFrame from './FocalPointPreviewFrame.vue';

export default {

    components: {
        FocalPointPreviewFrame,
    },


    props: [
        'data',   // The initial focus point data stored in the asset, if applicable.
        'image'   // The url of the image.
    ],


    data() {
        return {
            x: 50,
            y: 50,
            z: 1,
            imageDimensions: null,
        }
    },


    mounted() {
        const initial = this.data || '50-50-1';
        const coords = initial.split('-');
        this.x = coords[0];
        this.y = coords[1];
        this.z = coords[2] || 1;
    },


    computed: {

        reticleSize() {
            if (!this.imageDimensions || !this.z) return 0;
            const smaller = Math.min(this.imageDimensions.w, this.imageDimensions.h);
            return smaller / this.z;
        },

    },


    methods: {
        setImageDimensions() {
            const image = this.$refs.image;
            this.imageDimensions = { w: image.clientWidth, h: image.clientHeight };
        },

        define(e) {
            var $el = $(e.target);

            var imageW = $el.width();
            var imageH = $el.height();

            var offsetX = e.pageX - $el.offset().left;
            var offsetY = e.pageY - $el.offset().top;

            this.x = ((offsetX/imageW)*100).toFixed();
            this.y = ((offsetY/imageH)*100).toFixed();
        },

        select() {
            this.$emit('selected', this.x + '-' + this.y + '-' + this.z);
            this.close();
        },

        close() {
            this.$emit('closed');
        },

        reset() {
            this.x = 50;
            this.y = 50;
            this.z = 1;
        }

    }

}
</script>
