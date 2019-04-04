<template>

    <div class="form-group publish-field select-fieldtype field-w-full">

        <label class="publish-field-label">{{ __('Conditions') }}</label>
        <div class="help-block -mt-1"><p>{{ __('When to show or hide this field.') }}</p></div>

        <select-input
            v-model="showWhen"
            :options="showWhenOptions"
            :placeholder="false"
            class="inline-block" />

        <select-input
            v-if="hasConditions"
            v-model="type"
            :options="typeOptions"
            :placeholder="false"
            class="inline-block ml-2" />

        <text-input
            v-if="hasConditions && isCustom"
            v-model="customMethod"
            class="w-1/2 mt-2" />

        <div v-if="hasConditions && isStandard">

        </div>


        <!--
        <template v-if="data.type">

            <br> <br>

            <template v-if="isStandard">

            <small class="help-block">{{ __('cp.display_standard_instructions') }}</small>
                <table v-if="hasConditions" class="table">
                    <tr is="condition"
                        v-for="(i, condition) in conditions"
                        :key="condition.handle"
                        :index="i"
                        :handle.sync="condition.handle"
                        :operator.sync="condition.operator"
                        :values.sync="condition.values"
                        @deleted="destroy(i)"
                    ></tr>
                </table>

                <button class="btn btn-default" @click="add">
                    {{ __('Add Condition') }}
                </button>
            </template>

            <template v-if="isCustom">
                <small class="help-block">{{ __('cp.display_custom_instructions') }}</small>
                <input type="text" class="input-text" v-model="data.custom" />
            </template>

        </template>
        -->

    </div>

</template>


<script>
import HasInputOptions from '../fieldtypes/HasInputOptions.js'

export default {

    mixins: [HasInputOptions],

    components: {
        condition: require('./Condition.vue')
    },

    props: ['data'],

    data() {
        return {
            showWhen: 'always',
            type: 'standard',
            customMethod: null,
            conditions: [],
        }
    },

    computed: {
        showWhenOptions() {
            return this.normalizeInputOptions({
                always: __('Always show'),
                show_when: __('Show when'),
                hide_when: __('Hide when')
            });
        },

        typeOptions() {
            return this.normalizeInputOptions({
                standard: __('The following conditions pass'),
                custom: __('Custom method passes')
            });
        },

        hasConditions() {
            return this.showWhen !== 'always';
        },

        isStandard() {
            return this.type === 'standard';
        },

        isCustom() {
            return this.type === 'custom';
        },

        // hasConditions() {
        //     return this.conditions.length !== 0;
        // },

        // isStandard() {
        //     return this.data.style === 'standard';
        // },

        // isCustom() {
        //     return this.data.style === 'custom';
        // },
    },

    // mounted() {
    //     if (! this.data) {
    //         this.data = { type: null, style: 'standard', custom: null, conditions: [] };
    //     }

    //     this.conditions = this.data.conditions;
    // },

    methods: {
        // add() {
        //     this.conditions.push({
        //         handle: null,
        //         operator: 'and',
        //         values: []
        //     });
        // },

        // destroy(i) {
        //     this.conditions.splice(i, 1);
        // },
    }
}
</script>
