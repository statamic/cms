<template>

    <div class="form-group publish-field select-fieldtype field-w-full">

        <label class="publish-field-label">{{ __('Conditions') }}</label>
        <div class="help-block -mt-1"><p>{{ __('When to show or hide this field.') }}</p></div>

        <select-input
            v-model="when"
            :options="whenOptions"
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
import { KEYS } from '../publish/FieldConditions.js'

export default {

    mixins: [HasInputOptions],

    components: {
        condition: require('./Condition.vue')
    },

    props: {
        config: {
            required: true
        }
    },

    data() {
        return {
            when: 'always',
            type: 'standard',
            customMethod: null,
            conditions: [],
            any: false,
        }
    },

    computed: {
        whenOptions() {
            return this.normalizeInputOptions({
                always: __('Always show'),
                if: __('Show when'),
                unless: __('Hide when')
            });
        },

        typeOptions() {
            return this.normalizeInputOptions({
                standard: __('The following conditions pass'),
                custom: __('Custom method passes')
            });
        },

        hasConditions() {
            return this.when !== 'always';
        },

        isStandard() {
            return this.type === 'standard';
        },

        isCustom() {
            return this.type === 'custom';
        }
    },

    created() {
        this.getInitialConditions();
    },

    methods: {
        getInitialConditions() {
            let key = _.chain(KEYS)
                .filter(key => this.config[key])
                .first()
                .value();

            let conditions = this.config[key];

            if (! conditions) {
                return;
            }

            this.when = key.startsWith('unless') || key.startsWith('hide_when') ? 'unless' : 'if';
            this.any = key.endsWith('_any');

            if (typeof conditions === 'string') {
                this.type = 'custom';
                this.customMethod = conditions;
            } else {
                this.conditions = conditions;
            }
        },

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
