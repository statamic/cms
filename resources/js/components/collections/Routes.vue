<template>
    <one-or-many-sites-field
        :handle="handle"
        :value="value"
        :state="$store.state.publish[this.storeName]"
        @input="update"
        :column-header="__('Route')"
    ></one-or-many-sites-field>
</template>

<script>
import OneOrManySitesField from './OneOrManySitesField.vue'

export default {
    mixins: [Fieldtype],
    inject: ['storeName'],
    components: { OneOrManySitesField }
}
</script>
