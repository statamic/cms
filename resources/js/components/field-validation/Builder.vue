<template>

    <div class="form-group publish-field select-fieldtype field-w-full">
        <label class="publish-field-label">{{ __('Validation Rules') }}</label>
        <div class="help-block -mt-1">
            <p>
                {{ __('messages.field_validation_instructions') }}
                <a :href="laravelDocsLink" target="_blank">{{ __('Learn more') }}</a>
            </p>
        </div>

        <div class="list-fieldtype">
            <list-fieldtype
                handle="rules"
                :value="rules"
                ref="list"
                @input="updated" />
        </div>

        <p v-if="helpBlock" class="text-xs text-grey-60 ml-2 mt-1">
            {{ __('Example') }}:
            <span class="italic text-blue">{{ helpBlock }}</span>
        </p>

        <select-input
            v-model="predefinedRule"
            :options="predefinedRules"
            placeholder="Add Rule"
            @input="add"
            class="inline-block mt-3" />
    </div>

</template>


<script>
import RULES from './Rules.js';
import SemVer from 'semver'

export default {

    props: {
        config: {
            required: true
        },
    },

    data() {
        return {
            rules: [],
            predefinedRule: null,
        }
    },

    computed: {
        laravelVersion() {
            return this.$store.state.statamic.config.laravelVersion;
        },

        laravelDocsLink() {
            let version = new RegExp('([0-9]+\.[0-9])\.[0-9]+').exec(this.laravelVersion)[1];
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

        saveableRules() {
            return this.rules.map(rule => rule.trim()).join('|');
        }
    },

    watch: {
        saveableRules: {
            deep: true,
            handler(rules) {
                this.$emit('updated', rules);
            }
        }
    },

    created() {
        this.getInitial();
    },

    methods: {
        getInitial() {
            this.rules = this.config.validate
                ? this.config.validate.split('|').map(rule => rule.trim())
                : [];
        },

        add(rule) {
            this.$refs.list.newItem = rule;
            this.$refs.list.$refs.newItem.focus();
        },

        updated(rules) {
            this.predefinedRule = null;
            this.rules = rules;
        }
    }
}
</script>
