<template>
    <div class="flex items-center">
        <svg-icon name="check" class="w-3 text-green" v-if="this.value" />
        <svg-icon name="close" class="w-3 text-grey-50" v-if="!this.value" />
    </div>
</template>

<script>
export default {

    mixins: [IndexFieldtype],

};
</script>
