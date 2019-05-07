<template>
    <v-select
        ref="input"
        v-model="selected"
        :clearable="false"
        :options="options"
        :reduce="selection => selection.value"
        :searchable="true"
        :multiple="false">
        <template slot="option" slot-scope="option">
            <span class="little-dot bg-grey-50 mr-1" :class="option.status" />
            {{ option.label }}
        </template>
    </v-select>
</template>

<script>

export default {

    props: ['options'],

    data() {
        return {
            selected: null
        }
    }
};
</script>
