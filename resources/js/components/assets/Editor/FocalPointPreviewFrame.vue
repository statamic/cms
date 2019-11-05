<template>
    <div class="frame-image" ref="frame" :style="{ backgroundImage: `url(${encodeURI(imageUrl)})`, backgroundPosition: backgroundPosition, transform: `scale(${z})`, transformOrigin: transformOrigin }"
    />
</template>

<script>
export default {

    props: [
        'x',
        'y',
        'z',
        'imageUrl',
        'imageDimensions'
    ],


    data() {
        return {
            frameDimensions: {
                w: 100,
                h: 100
            }
        };
    },


    mounted() {
        const frame = this.$refs['frame'];
        // this is not responsive, but w/e
        this.frameDimensions = {
            w: frame.clientWidth,
            h: frame.clientHeight
        };
    },


    computed: {

        // the dimensions of the image when used as a background of the frame so that it's shorter dimensions matches the frame dimension along that axis
        bgImageDimensions() {
            const ratio = ({ w, h }) => w / h;
            if (ratio(this.imageDimensions) > ratio(this.frameDimensions)) {
                // height of the image is shorter than width
                return {
                    // background image will be the same height as the frame
                    h: this.frameDimensions.h,
                    // width of the background image is proportional to the scaling of the heights
                    w:
                        (this.frameDimensions.h / this.imageDimensions.h) *
                        this.imageDimensions.w,
                };
            } else {
                return {
                    // background image will be the same width as the frame
                    w: this.frameDimensions.w,
                    // height of the background image is proportional to the scaling of the widths
                    h:
                        (this.frameDimensions.w / this.imageDimensions.w) *
                        this.imageDimensions.h,
                };
            }
        },

        // the width of the frame relative to the width of the image behind it, in percent
        frameWidthPercent() {
            return (this.frameDimensions.w / this.bgImageDimensions.w) * 100;
        },

        // the height of the frame relative to the height of the image behind it, in percent
        frameHeightPercent() {
            return (this.frameDimensions.h / this.bgImageDimensions.h) * 100;
        },

        // how much the frame should be offset from the left of the image, in percent
        relOffsetLeft() {
            let ol = this.x - this.frameWidthPercent / 2;
            ol = Math.max(ol, 0);
            return Math.min(ol, 100 - this.frameWidthPercent);
        },

        // how much the frame should be offset from the left of the image, in px
        offsetLeft() {
            return (this.relOffsetLeft * this.bgImageDimensions.w) / 100;
        },

        // how much the frame should be offset from the top of the image, in percent
        relOffsetTop() {
            let ot = this.y - this.frameHeightPercent / 2;
            ot = Math.max(ot, 0);
            return Math.min(ot, 100 - this.frameHeightPercent);
        },

        // how much the frame should be offset from the top of the image, in px
        offsetTop() {
            return (this.relOffsetTop * this.bgImageDimensions.h) / 100;
        },

        // the background image is offset using the offsets of the frame "above" it
        // since the offsets are for the frame relative to the image, we negate them
        // to get the offsets for the background relative to the frame instead.
        backgroundPosition() {
            return `-${this.offsetLeft}px -${this.offsetTop}px`;
        },

        // the center of the scaling transformation, in percent (relative to the frame dimensions);
        // this has to be calculated because the focal point is not the center of the frame.
        transformOrigin() {
            const origin = {
                x:
                    ((this.x - this.relOffsetLeft) / this.frameWidthPercent) *
                    100,
                y:
                    ((this.y - this.relOffsetTop) / this.frameHeightPercent) *
                    100,
            };
            return `${origin.x}% ${origin.y}%`;
        }

    }

};
</script>