<template>
    <div>
        <svg-icon name="check" class="align-middle w-4" v-if="this.value" />
        <svg-icon name="close" class="align-middle w-4 opacity-25" v-if="!this.value" />
    </div>
</template>

<script>
export default {

    mixins: [IndexFieldtype],

};
</script>
