<template>
    <div class="w-full publish-fields">
        <Field
            class="form-group field-w-33"
            :label="__('Required')"
            :instructions="__('messages.field_validation_required_instructions')"
        >
            <Switch v-model="isRequired" />
        </Field>

        <Field
            class="form-group field-w-33"
            :label="__('Sometimes')"
            :instructions="__('messages.field_validation_sometimes_instructions')"
        >
            <Switch v-model="sometimesValidate" />
        </Field>

        <Field class="form-group field-w-100" :label="__('Rules')">
            <Description class="mb-1.5">
                {{ __('messages.field_validation_advanced_instructions') }}
                <a :href="laravelDocsLink" target="_blank">{{ __('Learn more') }}</a>
                <span v-if="helpBlock" class="italic text-gray-500 ltr:float-right rtl:float-left">
                    {{ __('Example') }}:
                    <span class="italic text-blue-400">{{ helpBlock }}</span>
                </span>
            </Description>

            <Combobox
                v-if="!customRule"
                class="w-full"
                ref="rulesSelect"
                :options="allRules"
                :placeholder="__('Add Rule')"
                :multiple="true"
                :searchable="true"
                :taggable="true"
                :close-dropdown-on-select="true"
                option-label="value"
                :model-value="rules"
                @selected="add($event)"
                @added="ifSearchNotFoundAddCustom"
            >
                <template #option="option">
                    {{ __(option.display) }} <code class="ms-2 text-sm">{{ valueWithoutTrailingColon(option.value) }}</code>
                </template>

                <template #selected-options>
                    <!-- We're rendering these ourselves so they don't go away when we swap out the combobox for an input. -->
                    <div></div>
                </template>
            </Combobox>

            <Input
                v-else
                v-model="customRule"
                ref="customRuleInput"
                @keydown.enter.prevent="add(customRule)"
                @blur="add(customRule)"
            />

            <sortable-list
                item-class="sortable-item"
                handle-class="sortable-item"
                :distance="5"
                :mirror="false"
                v-model="rules"
            >
                <div class="flex flex-wrap gap-2">
                    <div
                        v-for="rule in rules"
                        :key="rule"
                        class="sortable-item mt-2"
                    >
                        <Badge pill size="lg">
                            {{ rule }}

                            <button
                                type="button"
                                class="opacity-75 hover:opacity-100 cursor-pointer"
                                :aria-label="__('Deselect option')"
                                @click="remove(rule)"
                            >
                                &times;
                            </button>
                        </Badge>
                    </div>
                </div>
            </sortable-list>
        </Field>
    </div>
</template>

<script>
import RULES from './Rules.js';
import SemVer from 'semver';
import { SortableList } from '../sortable/Sortable';
import { sortBy } from 'lodash-es';
import { Description, Field, Input, Badge, Button } from '@statamic/ui';
import Switch from '@statamic/components/ui/Switch.vue'
import { Combobox } from '@statamic/ui';
import { ComboboxInput } from 'reka-ui';

export default {
    components: {
        Button,
        ComboboxInput,
        Combobox,
        Description,
        SortableList,
        Field,
        Switch,
        Input,
        Badge,
    },

    props: {
        config: {
            required: true,
        },
    },

    data() {
        return {
            isRequired: false,
            sometimesValidate: false,
            rules: [],
            selectedLaravelRule: null,
            customRule: null,
        };
    },

    mounted() {
        console.log(this.allRules.find((rule) => rule.value === 'required'));
    },

    computed: {
        laravelVersion() {
            return this.$config.get('laravelVersion');
        },

        laravelDocsLink() {
            let version = new RegExp('([0-9]+\.[0-9]+)\.[0-9]+').exec(this.laravelVersion)[1];
            let majorVersion = Number(version.split('.', 1)[0]);

            return `https://laravel.com/docs/${majorVersion}.x/validation#available-validation-rules`;
        },

        laravelRules() {
            return clone(RULES)
                .filter((rule) => (rule.minVersion ? SemVer.gte(this.laravelVersion, rule.minVersion) : true))
                .filter((rule) => (rule.maxVersion ? SemVer.lte(this.laravelVersion, rule.maxVersion) : true))
                .map((rule) => {
                    return this.prepareRenderableRule(rule);
                });
        },

        extensionRules() {
            return clone(Statamic.$config.get('extensionRules')).map((rule) => {
                return this.prepareRenderableRule(rule);
            });
        },

        allRules() {
            return sortBy([...this.laravelRules, ...this.extensionRules], 'display');
        },

        helpBlock() {
            if (!this.selectedLaravelRule) {
                return false;
            }

            let rule = this.allRules.find((rule) => rule.value === this.selectedLaravelRule);

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
            this.rules = this.config.validate ? this.explodeRules(this.config.validate) : [];
        },

        resetState() {
            this.selectedLaravelRule = null;
            this.customRule = null;
            this.isRequired = this.rules.includes('required');
            this.sometimesValidate = this.rules.includes('sometimes');
        },

        explodeRules(rules) {
            return typeof rules === 'string' ? rules.split('|').map((rule) => rule.trim()) : rules;
        },

        prepareRenderableRule(rule) {
            rule.display = clone(rule.label); // Set label to separate `display` property for rendering.

            this.$nextTick(() => {
                rule.label = `${rule.label} ${rule.value}`; // Concatenate so that both `label` and `value` are searchable.
            });

            return rule;
        },

        ensureToggleableRule(rule) {
            if (!this.rules.includes(rule)) {
                this.rules.unshift(rule);
            }
        },

        ensure(rule) {
            this.resetState();

            // todo: once the empty string issue in hasUnfinishedParameters has been fixed, we can remove this workaround
            if (!rule) {
                return;
            }

            if (!this.rules.includes(rule)) {
                this.rules.push(rule);
            }
        },

        add(rule) {
            if (this.hasUnfinishedParameters(rule)) {
                this.resetState();
                this.selectedLaravelRule = rule;
                this.customRule = rule;
                this.$nextTick(() => this.$refs.customRuleInput.focus());
            } else {
                this.ensure(rule);
            }
        },

        ifSearchNotFoundAddCustom() {
            let rulesSelect = this.$refs.rulesSelect;
            let rule = rulesSelect.searchQuery.value;

            if (this.searchNotFound(rulesSelect) || this.hasUnfinishedParameters(rule)) return;

            this.add(rule);

            this.$nextTick(() => this.$refs.searchInput.blur());
        },

        remove(rule) {
            this.rules = this.rules.filter((value) => value !== rule);
        },

        hasUnfinishedParameters(rule) {
            // todo: figure out why we sometimes get here with an empty rule.
            if (! rule) {
                return;
            }

            return rule.substr(rule.length - 1) === ':';
        },

        searchNotFound(rulesSelect) {
            return rulesSelect.searchQuery.value?.length === 0 || rulesSelect?.filteredOptions.length === 0;
        },

        updated(rules) {
            this.rules = rules;
        },

        valueWithoutTrailingColon(value) {
            return this.hasUnfinishedParameters(value) ? value.replace(':', '') : value;
        },
    },
};
</script>
