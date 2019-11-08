<template>

    <div v-if="value">
        {{ __n(':count set|:count sets', value) }}
    </div>

</template>

<script>
export default {

    mixins: [IndexFieldtype],

}
</script>
