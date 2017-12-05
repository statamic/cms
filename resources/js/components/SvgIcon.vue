<template>

    <div class="svg-icon">
        <svg v-if="type === 'svg'"><use xlink:href="#{{ name }}" /></svg>

        <div v-if="type === 'div'"
             :style="'background-image: url(' + resource_url('svg/'+name+'.svg') + ')'">
        </div>
    </div>

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
    }

}
</script>
