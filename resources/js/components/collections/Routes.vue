<template>
    <one-or-many-sites-field
        :handle="handle"
        :state="$store.state.publish[this.storeName]"
        :model-value="modelValue"
        @update:model-value="update"
        :column-header="__('Route')"
    ></one-or-many-sites-field>
</template>

<script>
import OneOrManySitesField from './OneOrManySitesField.vue'
import Fieldtype from '../fieldtypes/Fieldtype.vue';

export default {
    mixins: [Fieldtype],
    inject: ['storeName'],
    components: { OneOrManySitesField }
}
</script>
