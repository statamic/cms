<template>

    <component :is="type"
        v-html="html">
    </component>

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
