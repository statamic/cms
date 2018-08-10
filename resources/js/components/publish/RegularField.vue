<template>

    <div v-show="isVisible" :class="classes">
        <div class="field-inner">
            <div v-if="isReadOnly" class="read-only-overlay" :title="translate('cp.read_only')"></div>

            <label class="block" :class="{'bold': field.bold}">
                <template v-if="field.display">{{ field.display }}</template>
                <template v-if="!field.display">{{ field.name | deslugify | titleize }}</template>
                <i class="required" v-if="field.required">*</i>
            </label>

            <small class="help-block" v-if="field.instructions" v-html="field.instructions | markdown"></small>

            <div v-if="env" class="environment-field">
                <i class="icon icon-lock"></i> {{ translate('cp.defined_in_environment') }}
            </div>

            <component v-else
                        :is="componentName"
                        :name="field.name"
                        :data.sync="data"
                        :config="config"
                        :autofocus="autofocus"
                        :leave-alert="true">
            </component>

        </div>
    </div>

</template>

<script>
import Field from './Field';

export default {

    mixins: [Field],

    computed: {

        componentName() {
            return this.field.type.replace('.', '-') + '-fieldtype';
        },

        isLocalizable() {
            return this.config.localizable;
        },

    }

}
</script>
