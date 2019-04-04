<template>

    <div class="focal-point">
        <div class="focal-point-toolbox card p-0">
            <div class="form-group pb-0">
                <label>{{ __('Focal Point') }}</label>
                <small class="help-block">{{ __('focal_point_instructions') }}</small>
                <div class="focal-point-image">
                    <img ref="image" :src="image" @click="define" />
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
            <div class="flex items-center justify-between px-2">
                <div>
                    <div class="mb-2">
                        <button type="button" class="btn" @click.prevent="close">{{ __('Cancel') }}</button>
                        <button type="button" class="btn btn-default mx-1" @click.prevent="reset">{{ __('Reset') }}</button>
                        <button type="button" class="btn btn-primary" @click="select">{{ __('Save') }}</button>
                    </div>
                    <div>
                        <input type="range" v-model="z" min="1" max="10" step="0.1" class="w-full" />
                    </div>
                </div>
                <div class="focal-point-coordinates">
                    <div class="pair">
                        <div class="axis">X</div>
                        <div class="value">{{ x }}<sup>%</sup></div>
                    </div>
                    <div class="pair">
                        <div class="axis">Y</div>
                        <div class="value">{{ y }}<sup>%</sup></div>
                    </div>
                    <div class="pair">
                        <div class="axis">Z</div>
                        <div class="value">{{ z }}</div>
                    </div>
                </div>
            </div>
            <h6 class="p-2 text-center bg-grey-30 rounded-b">{{ __('Crop previews are for example only') }}</h6>
        </div>
        <div v-for="n in 9" :key="n"
             :class="`frame frame-${n}`">
            <div class="frame-image" :style="{ backgroundImage: 'url('+bgImage+')', backgroundPosition: bgPosition, transform: bgTransform, transformOrigin: bgPosition }" />
        </div>
    </div>

</template>

<script>
export default {

    props: [
        'data',   // The initial focus point data stored in the asset, if applicable.
        'image'   // The url of the image.
    ],


    data() {
        return {
            x: 50,
            y: 50,
            z: 1,
            reticleSize: 0,
        }
    },


    computed: {

        bgPosition() {
            return this.x + '% ' + this.y + '%';
        },

        bgImage() {
            return encodeURI(this.image);
        },

        bgTransform() {
            return `scale(${this.z})`;
        }

    },


    mounted() {
        const initial = this.data || '50-50-1';
        const coords = initial.split('-');
        this.x = coords[0];
        this.y = coords[1];
        this.z = coords[2] || 1;
    },


    watch: {

        z(z) {
            const image = this.$refs.image;
            const smaller = Math.min(image.clientWidth, image.clientHeight);
            this.reticleSize = smaller / z;
        }

    },


    methods: {

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
