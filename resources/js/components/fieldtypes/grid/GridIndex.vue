<template>

    <div v-if="value">
        {{ __n(':count rows', value) }}
    </div>

</template>

<script>
export default {

    mixins: [IndexFieldtype],

}
</script>
