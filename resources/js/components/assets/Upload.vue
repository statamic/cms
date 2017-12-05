<template>

    <tr>

        <td class="column-status" :class="status">
            <span class="icon icon-warning error" v-if="status === 'error'"></span>
            <span class="icon icon-circular-graph animation-spin" v-else></span>
        </td>

        <td class="column-thumbnail">
            <div class="img">
                <file-icon :extension="extension"></file-icon>
            </div>
        </td>

        <td class="column-filename">
            <span class="filename">{{ basename }}</span>
        </td>

        <td class="column-progress" v-if="status !== 'error'">
            <div class="progress">
                <div class="progress-bar" :style="{ width: percent+'%' }"></div>
            </div>
        </td>

        <td class="column-error" v-else>{{ error }}</td>

        <td style="width: 30px">
            <a href="#" v-if="status == 'error'" @click.prevent="clear">
                <i class="icon icon-circle-with-cross"></i>
            </a>
        </td>

    </tr>

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
