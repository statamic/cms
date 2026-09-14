<template>
    <portal name="focal-point">
        <div class="focal-point">
            <Card class="focal-point-toolbox" inset>
                <div class="p-6">
                    <Heading size="xl">{{ __('Focal Point') }}</Heading>
                    <Subheading>{{ __('messages.focal_point_instructions') }}</Subheading>
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
                    <div class="mx-2 flex items-center gap-1">
                        <label class="me-1" for="focal-point-x">X</label>
                        <ui-input id="focal-point-x" v-model.number="x" type="number" min="0" max="100" step="1" size="sm" @change="clampPercent('x')" />
                        <span>%</span>
                    </div>
                    <div class="mx-2 flex items-center gap-1">
                        <label class="me-1" for="focal-point-y">Y</label>
                        <ui-input id="focal-point-y" v-model.number="y" type="number" min="0" max="100" step="1" size="sm" @change="clampPercent('y')" />
                        <span>%</span>
                    </div>
                    <div class="mx-2 flex items-center gap-1">
                        <label class="me-1" for="focal-point-z">Z</label>
                        <ui-input id="focal-point-z" v-model.number="z" type="number" min="1" max="10" step="0.1" size="sm" @change="clampZoom" />
                    </div>
                </div>
                <div class="px-4">
                    <input type="range" v-model.number="z" min="1" max="10" step="0.1" class="mb-4 w-full" />
                    <div class="mb-4 flex flex-wrap items-center justify-center gap-2">
                        <Button :text="__('Cancel')" @click="close" />
                        <Button :text="__('Reset')" @click="reset" />
                        <Button variant="primary" :text="__('Finish')" @click="select" />
                    </div>
                </div>
                <h6 class="rounded-b bg-gray-100  p-4 text-center  dark:bg-gray-850">
                    {{ __('messages.focal_point_previews_are_examples') }}
                </h6>
            </Card>
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
import {
    Card,
    Heading,
    Subheading,
    Button,
} from '@ui';
import FocalPointPreviewFrame from './FocalPointPreviewFrame.vue';

export default {
    components: {
        Card,
        Heading,
        Subheading,
        Button,
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
        this.x = Number(coords[0] ?? 50);
        this.y = Number(coords[1] ?? 50);
        this.z = Number(coords[2] ?? 1);
        this.clampPercent('x');
        this.clampPercent('y');
        this.clampZoom();
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

            this.x = Math.round((offsetX / imageW) * 100);
            this.y = Math.round((offsetY / imageH) * 100);
            this.clampPercent('x');
            this.clampPercent('y');
        },

        clampPercent(axis) {
            const value = Number(this[axis]);
            const safeValue = Number.isFinite(value) ? value : 50;
            this[axis] = Math.min(100, Math.max(0, Math.round(safeValue)));
        },

        clampZoom() {
            const value = Number(this.z);
            const safeValue = Number.isFinite(value) ? value : 1;
            const clamped = Math.min(10, Math.max(1, safeValue));
            this.z = Math.round(clamped * 10) / 10;
        },

        select() {
            this.clampPercent('x');
            this.clampPercent('y');
            this.clampZoom();
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
