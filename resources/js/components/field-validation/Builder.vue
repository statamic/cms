<template>
    <div class="w-full">
        <Field
            :label="__('Required')"
            :instructions="__('messages.field_validation_required_instructions')"
        >
            <Switch v-model="isRequired" />
        </Field>

        <Field
            :label="__('Sometimes')"
            :instructions="__('messages.field_validation_sometimes_instructions')"
        >
            <Switch v-model="sometimesValidate" />
        </Field>

        <Field :label="__('Rules')">
            <Description>
                {{ __('messages.field_validation_advanced_instructions') }}
                <a :href="laravelDocsLink" target="_blank">{{ __('Learn more') }}</a>
                <span v-if="helpBlock" class="italic text-gray-500 ltr:float-right rtl:float-left">
                    {{ __('Example') }}:
                    <span class="italic text-blue-400">{{ helpBlock }}</span>
                </span>
            </Description>

            <v-select
                v-if="!customRule"
                ref="rulesSelect"
                name="rules"
                :options="allRules"
                :reduce="(rule) => rule.value"
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
                    {{ __(display) }} <code class="ltr:ml-2 rtl:mr-2">{{ valueWithoutTrailingColon(value) }}</code>
                </template>
                <template #no-options="{ search }">
                    <div class="vs__dropdown-option ltr:text-left rtl:text-right">
                        {{ __('Add') }} <code class="ltr:ml-2 rtl:mr-2">{{ search }}</code>
                    </div>
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
                    :distance="5"
                    :mirror="false"
                    v-model="rules"
                >
                    <div class="vs__selected-options-outside flex flex-wrap outline-hidden">
                        <span v-for="rule in rules" :key="rule" class="vs__selected sortable-item mt-2">
                            {{ rule }}
                            <button
                                @click="remove(rule)"
                                type="button"
                                :aria-label="__('Delete Rule')"
                                class="vs__deselect"
                            >
                                <span>×</span>
                            </button>
                        </span>
                    </div>
                </sortable-list>
            </div>
        </Field>
    </div>
</template>

<style scoped>
.draggable-source--is-dragging {
    @apply border-dashed bg-transparent opacity-75;
}
</style>

<script>
import RULES from './Rules.js';
import SemVer from 'semver';
import { SortableList } from '../sortable/Sortable';
import { sortBy } from 'lodash-es';
import { Description, Field } from '@statamic/ui';
import Switch from '@statamic/components/ui/Switch.vue'

export default {
    components: {
        Description,
        SortableList,
        Field,
        Switch,
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

    computed: {
        laravelVersion() {
            return this.$config.get('laravelVersion');
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

            let rule = this.allRules.filter((rule) => rule.value === this.selectedLaravelRule).first();

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

            if (!this.rules.includes(rule)) {
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
            this.rules = this.rules.filter((value) => value !== rule);
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
        },
    },
};
</script>
