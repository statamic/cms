<template>

    <div :class="[ 'svg-icon', { 'using-div': type === 'div', 'using-svg': type === 'svg' } ]"
        v-html="html"></div>

</template>


<script>
export default {

    props: {
        name: String,
        type: {
            type: String,
            default() {
                return 'svg';
            }
        }
    },

    computed: {

        html() {
            if (this.type === 'div') {
                return `<div style="background-image: url('${resource_url('svg/'+this.name+'.svg')}')"></div>`;
            }

            return require(`!!html-loader!./../../svg/${this.name}.svg`);
        }

    }

}
</script>
