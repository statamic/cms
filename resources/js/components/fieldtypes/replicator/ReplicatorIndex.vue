<template>

    <div v-if="value">
        {{ __n(':count set|:count sets', value) }}
    </div>

</template>

<script>
import IndexFieldtype from '../IndexFieldtype.vue';

export default {

    mixins: [IndexFieldtype],

}
</script>
