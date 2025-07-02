<template>
    <portal name="focal-point">
        <div class="focal-point">
            <div class="focal-point-toolbox card p-0">
                <div class="p-4">
                    <label>{{ __('Focal Point') }}</label>
                    <small class="help-block">{{ __('messages.focal_point_instructions') }}</small>
                    <div class="focal-point-image">
                        <img ref="image" :src="image" @click="define" @load="setImageDimensions" />
                        <div
                            class="focal-point-reticle"
                            :class="{ zoomed: z > 1 }"
                            :style="{
                                top: `${y}%`,
                                left: `${x}%`,
                                width: `${reticleSize}px`,
                                height: `${reticleSize}px`,
                                marginTop: `-${reticleSize / 2}px`,
                                marginLeft: `-${reticleSize / 2}px`,
                            }"
                        ></div>
                    </div>
                </div>
                <div class="mb-4 flex items-center justify-center text-sm">
                    <div class="mx-4 flex items-center">
                        <div class="ltr:mr-1 rtl:ml-1">X</div>
                        <div class="value">{{ x }}<sup>%</sup></div>
                    </div>
                    <div class="mx-4 flex items-center">
                        <div class="ltr:mr-1 rtl:ml-1">Y</div>
                        <div class="value">{{ y }}<sup>%</sup></div>
                    </div>
                    <div class="mx-4 flex items-center">
                        <div class="ltr:mr-1 rtl:ml-1">Z</div>
                        <div class="value">{{ z }}</div>
                    </div>
                </div>
                <div class="px-4">
                    <input type="range" v-model="z" min="1" max="10" step="0.1" class="mb-4 w-full" />
                    <div class="mb-2 flex flex-wrap items-center justify-center">
                        <button type="button" class="btn mb-2" @click.prevent="close">{{ __('Cancel') }}</button>
                        <button type="button" class="btn btn-default mx-2 mb-2" @click.prevent="reset">
                            {{ __('Reset') }}
                        </button>
                        <button type="button" class="btn btn-primary mb-2" @click="select">{{ __('Finish') }}</button>
                    </div>
                </div>
                <h6 class="dark:border-dark-200 dark:bg-dark-550 rounded-b bg-gray-300 p-4 text-center">
                    {{ __('messages.focal_point_previews_are_examples') }}
                </h6>
            </div>
            <div v-for="n in 9" :key="n" :class="`frame frame-${n}`">
                <focal-point-preview-frame
                    v-if="imageDimensions"
                    :x="x"
                    :y="y"
                    :z="z"
                    :image-url="image"
                    :image-dimensions="imageDimensions"
                />
            </div>
        </div>
    </portal>
</template>

<script>
import FocalPointPreviewFrame from './FocalPointPreviewFrame.vue';

export default {
    components: {
        FocalPointPreviewFrame,
    },

    props: [
        'data', // The initial focus point data stored in the asset, if applicable.
        'image', // The url of the image.
    ],

    data() {
        return {
            x: 50,
            y: 50,
            z: 1,
            imageDimensions: null,
        };
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
            var rect = e.target.getBoundingClientRect();

            var imageW = rect.width;
            var imageH = rect.height;

            var offsetX = e.clientX - rect.left;
            var offsetY = e.clientY - rect.top;

            this.x = ((offsetX / imageW) * 100).toFixed();
            this.y = ((offsetY / imageH) * 100).toFixed();
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
        },
    },
};
</script>
