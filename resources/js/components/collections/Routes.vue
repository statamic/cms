<template>
    <one-or-many-sites-field
        :handle="handle"
        :value="value"
        @update:value="update"
        :column-header="__('Route')"
    ></one-or-many-sites-field>
</template>

<script>
import Fieldtype from '../fieldtypes/Fieldtype.vue';
import OneOrManySitesField from './OneOrManySitesField.vue';

export default {
    mixins: [Fieldtype],
    components: { OneOrManySitesField },
};
</script>
