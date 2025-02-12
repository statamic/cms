<template>
    <one-or-many-sites-field
        :handle="handle"
        :value="value"
        :store="store"
        @input="update"
        :column-header="__('Route')"
    ></one-or-many-sites-field>
</template>

<script>
import Fieldtype from '../fieldtypes/Fieldtype.vue';
import OneOrManySitesField from './OneOrManySitesField.vue';

export default {
    mixins: [Fieldtype],
    inject: ['store'],
    components: { OneOrManySitesField },
};
</script>
