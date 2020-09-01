<template>

    <div class="w-full">

        <div class="form-group publish-field select-fieldtype field-w-full">
            <label class="publish-field-label">{{ __('Required') }}</label>
            <div class="help-block -mt-1">
                <p>{{ __('Control whether or not this field is required.') }}</p>
            </div>
            <toggle-input v-model="isRequired" />
        </div>

        <div class="form-group publish-field select-fieldtype field-w-full">
            <label class="publish-field-label">{{ __('Rules') }}</label>
            <div class="help-block -mt-1">
                <p>
                    {{ __('Add more advanced validation to this field.') }}
                    <a :href="laravelDocsLink" target="_blank">{{ __('Learn more') }}</a>
                    <span v-if="helpBlock" class="italic text-grey-50 float-right">
                        {{ __('Example') }}:
                        <span class="italic text-blue-lighter">{{ helpBlock }}</span>
                    </span>
                </p>
            </div>

            <v-select
                v-if="!customRule"
                ref="rulesInput"
                name="rules"
                :options="predefinedRules"
                :reduce="rule => rule.value"
                :placeholder="__('Add Rule')"
                :multiple="false"
                :searchable="true"
                :value="selection"
                class="w-full"
                @input="add"
            >
                <template #option="{ value, display }">
                    {{ display }} <code class="ml-1">{{ value.replace(':', '') }}</code>
                </template>
            </v-select>

            <text-input
                v-else
                v-model="customRule"
                ref="customRuleInput"
                @keydown.enter.prevent="add(customRule)"
            />

            <div class="v-select">
                <sortable-list
                    item-class="sortable-rule"
                    handle-class="sortable-rule"
                    v-model="rules"
                >
                    <div class="vs__selected-options-outside flex flex-wrap outline-none">
                        <span v-for="rule in rules" :key="rule" class="vs__selected mt-1 sortable-rule">
                            {{ rule }}
                            <button @click="remove(rule)" type="button" :aria-label="__('Delete Rule')" class="vs__deselect">
                                <span>×</span>
                            </button>
                        </span>
                    </div>
                </sortable-list>
            </div>

        </div>
    </div>

</template>


<script>
import RULES from './Rules.js';
import SemVer from 'semver'
import { SortableList, SortableItem, SortableHelpers } from '../sortable/Sortable';

export default {

    components: {
        SortableList,
        SortableItem,
    },

    props: {
        config: {
            required: true
        },
    },

    data() {
        return {
            isRequired: false,
            rules: [],
            selection: null,
            predefinedRule: null,
            customRule: null,
        }
    },

    computed: {
        laravelVersion() {
            return this.$store.state.statamic.config.laravelVersion;
        },

        laravelDocsLink() {
            let version = new RegExp('([0-9]+\.[0-9]+)\.[0-9]+').exec(this.laravelVersion)[1];
            let majorVersion = Number(version.split('.', 1)[0]);

            if (majorVersion >= 6) {
                version = `${majorVersion}.x`;
            }

            return `https://laravel.com/docs/${version}/validation#available-validation-rules`;
        },

        predefinedRules() {
            return _.chain(RULES)
                .filter(rule => rule.minVersion ? SemVer.gte(this.laravelVersion, rule.minVersion) : true)
                .filter(rule => rule.maxVersion ? SemVer.lte(this.laravelVersion, rule.maxVersion) : true)
                .map(rule => {
                    rule.display = clone(rule.label); // Set label to separate `display` property for rendering.
                    rule.label = rule.label + ' ' + rule.value; // Concatenate so that both `label` and `value` are searchable.
                    return rule;
                })
                .value();
        },

        helpBlock() {
            if (! this.predefinedRule) {
                return false;
            }

            let rule = _.chain(RULES)
                .filter(rule => rule.value === this.predefinedRule)
                .first()
                .value();

            return rule.example || false;
        },
    },

    watch: {
        isRequired(value) {
            if (value === true) {
                this.ensureRequired();
            } else {
                this.remove('required');
            }
        },

        rules(value) {
            this.resetState();

            this.$emit('updated', value);
        },
    },

    created() {
        this.getInitial();
    },

    methods: {
        getInitial() {
            this.rules = this.config.validate
                ? this.explodeRules(this.config.validate)
                : [];
        },

        resetState() {
            this.selection = null;
            this.predefinedRule = null;
            this.customRule = null;
            this.isRequired = this.rules.includes('required');
        },

        explodeRules(rules) {
            return typeof rules === 'string'
                ? rules.split('|').map(rule => rule.trim())
                : rules;
        },

        ensureRequired() {
            if (! this.rules.includes('required')) {
                this.rules.unshift('required');
            }
        },

        ensure(rule) {
            this.resetState();

            if (! this.rules.includes(rule)) {
                this.rules.push(rule);
            }
        },

        add(rule) {
            if (this.hasParameters(rule) === ':') {
                this.resetState();
                this.predefinedRule = rule;
                this.customRule = rule;
                this.$nextTick(() => this.$refs.customRuleInput.$refs.input.focus());
            } else {
                this.ensure(rule);
            }
        },

        remove(rule) {
            this.rules = this.rules.filter(value => value !== rule);
        },

        hasParameters(rule) {
            return rule.substr(rule.length - 1);
        },

        updated(rules) {
            this.rules = rules;
        },

    }
}
</script>
