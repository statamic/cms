<template>

    <div>
        <select-fieldtype
            :data.sync="data.type"
            :config="conditionSelectFieldtypeConfig">
        </select-fieldtype>

        <template v-if="data.type">

            <br> <br>

            <radio-fieldtype
                :data.sync="data.style"
                :name="condition_style"
                :config="styleRadioFieldtypeConfig"
            ></radio-fieldtype>

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

    </div>

</template>


<script>

export default {

    components: {
        condition: require('./Condition.vue')
    },

    props: ['data'],

    data() {
        return {
            conditions: [],
            conditionSelectFieldtypeConfig: {
                options: [
                    {text: __('Always show'), value: null},
                    {text: `${__('Show when')}...`, value: 'show'},
                    {text: `${__('Hide when')}...`, value: 'hide'}
                ]
            },
            styleRadioFieldtypeConfig: {
                inline: true,
                options: [
                    {text: __('Standard'), value: 'standard'},
                    {text: __('Custom'), value: 'custom'}
                ]
            }
        }
    },

    computed: {

        hasConditions() {
            return this.conditions.length !== 0;
        },

        isStandard() {
            return this.data.style === 'standard';
        },

        isCustom() {
            return this.data.style === 'custom';
        }

    },

    mounted() {
        if (! this.data) {
            this.data = { type: null, style: 'standard', custom: null, conditions: [] };
        }

        this.conditions = this.data.conditions;
    },

    methods: {

        add() {
            this.conditions.push({
                handle: null,
                operator: 'and',
                values: []
            });
        },

        destroy(i) {
            this.conditions.splice(i, 1);
        }

    }

}

</script>
