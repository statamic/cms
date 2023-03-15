<template>

    <div class="w-full">

        <div class="flex">

            <div class="form-group publish-field select-fieldtype field-w-full">
                <label class="publish-field-label">{{ __('Required') }}</label>
                <div class="help-block -mt-1">
                    <p>{{ __('messages.field_validation_required_instructions') }}</p>
                </div>
                <toggle-input v-model="isRequired" />
            </div>

            <div class="form-group publish-field select-fieldtype field-w-full">
                <label class="publish-field-label">{{ __('Sometimes') }}</label>
                <div class="help-block -mt-1">
                    <p>{{ __('messages.field_validation_sometimes_instructions') }}</p>
                </div>
                <toggle-input v-model="sometimesValidate" />
            </div>

        </div>

        <div class="form-group publish-field select-fieldtype field-w-full">
            <label class="publish-field-label">{{ __('Rules') }}</label>
            <div class="help-block -mt-1">
                <p>
                    {{ __('messages.field_validation_advanced_instructions') }}
                    <a :href="laravelDocsLink" target="_blank">{{ __('Learn more') }}</a>
                    <span v-if="helpBlock" class="italic text-grey-50 float-right">
                        {{ __('Example') }}:
                        <span class="italic text-blue-lighter">{{ helpBlock }}</span>
                    </span>
                </p>
            </div>

            <v-select
                v-if="!customRule"
                ref="rulesSelect"
                name="rules"
                :options="allRules"
                :reduce="rule => rule.value"
                :placeholder="__('Add Rule')"
                :multiple="false"
                :searchable="true"
                :value="selectedLaravelRule"
                class="w-full"
                @input="add"
            >
                <template #search="{ attributes, events }">
                    <input
                        ref="searchInput"
                        v-bind="attributes"
                        v-on="events"
                        class="vs__search"
                        @keydown.enter="ifSearchNotFoundAddCustom"
                        @blur="ifSearchNotFoundAddCustom"
                    />
                </template>
                <template #option="{ value, display }">
                    {{ display }} <code class="ml-1">{{ valueWithoutTrailingColon(value) }}</code>
                </template>
                <template #no-options="{ search }">
                    <div class="vs__dropdown-option text-left">{{ __('Add') }} <code class="ml-1">{{ search }}</code></div>
                </template>
            </v-select>

            <text-input
                v-else
                v-model="customRule"
                ref="customRuleInput"
                @keydown.enter.prevent="add(customRule)"
                @blur="add(customRule)"
            />

            <div class="v-select">
                <sortable-list
                    item-class="sortable-item"
                    handle-class="sortable-item"
                    v-model="rules"
                >
                    <div class="vs__selected-options-outside flex flex-wrap outline-none">
                        <span v-for="rule in rules" :key="rule" class="vs__selected mt-1 sortable-item">
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
            sometimesValidate: false,
            rules: [],
            selectedLaravelRule: null,
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

        laravelRules() {
            return _.chain(clone(RULES))
                .filter(rule => rule.minVersion ? SemVer.gte(this.laravelVersion, rule.minVersion) : true)
                .filter(rule => rule.maxVersion ? SemVer.lte(this.laravelVersion, rule.maxVersion) : true)
                .map(rule => {
                    return this.prepareRenderableRule(rule);
                })
                .value();
        },

        extensionRules() {
            return _.chain(clone(Statamic.$config.get('extensionRules')))
                .map(rule => {
                    return this.prepareRenderableRule(rule);
                })
                .value();
        },

        allRules() {
            return _.sortBy([...this.laravelRules, ...this.extensionRules], 'display');
        },

        helpBlock() {
            if (! this.selectedLaravelRule) {
                return false;
            }

            let rule = _.chain(RULES)
                .filter(rule => rule.value === this.selectedLaravelRule)
                .first()
                .value();

            return rule.example || false;
        },

    },

    watch: {

        isRequired(value) {
            if (value === true) {
                this.ensureToggleableRule('required');
            } else {
                this.remove('required');
            }
        },

        sometimesValidate(value) {
            if (value === true) {
                this.ensureToggleableRule('sometimes');
            } else {
                this.remove('sometimes');
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
            this.selectedLaravelRule = null;
            this.customRule = null;
            this.isRequired = this.rules.includes('required');
            this.sometimesValidate = this.rules.includes('sometimes');
        },

        explodeRules(rules) {
            return typeof rules === 'string'
                ? rules.split('|').map(rule => rule.trim())
                : rules;
        },

        prepareRenderableRule(rule) {
            rule.display = clone(rule.label); // Set label to separate `display` property for rendering.

            this.$nextTick(() => {
                rule.label = `${rule.label} ${rule.value}`; // Concatenate so that both `label` and `value` are searchable.
            });

            return rule;
        },

        ensureToggleableRule(rule) {
            if (! this.rules.includes(rule)) {
                this.rules.unshift(rule);
            }
        },

        ensure(rule) {
            this.resetState();

            if (! this.rules.includes(rule)) {
                this.rules.push(rule);
            }
        },

        add(rule) {
            if (this.hasUnfinishedParameters(rule)) {
                this.resetState();
                this.selectedLaravelRule = rule;
                this.customRule = rule;
                this.$nextTick(() => this.$refs.customRuleInput.$refs.input.focus());
            } else {
                this.ensure(rule);
            }
        },

        ifSearchNotFoundAddCustom() {
            let rulesSelect = this.$refs.rulesSelect;
            let rule = rulesSelect.search;

            if (this.searchNotFound(rulesSelect) || this.hasUnfinishedParameters(rule)) return;

            this.add(rule);

            this.$nextTick(() => this.$refs.searchInput.blur());
        },

        remove(rule) {
            this.rules = this.rules.filter(value => value !== rule);
        },

        hasUnfinishedParameters(rule) {
            return rule.substr(rule.length - 1) === ':';
        },

        searchNotFound(rulesSelect) {
            return rulesSelect.search.length === 0 || rulesSelect.filteredOptions.length > 0;
        },

        updated(rules) {
            this.rules = rules;
        },

        valueWithoutTrailingColon(value) {
            return this.hasUnfinishedParameters(value) ? value.replace(':', '') : value;
        }

    }
}
</script>
