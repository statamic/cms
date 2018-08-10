<template>

    <div class="bard-block bard-set" :class="{'bard-set-solo': goingSolo}">

        <slot name="divider-start"></slot>

        <div class="list-group">
            <div class="list-group-item group-header pl-3 bard-drag-handle" :class="{'collapsed': isHidden}" @dblclick="toggle" v-if="! goingSolo">
                <div class="flexy">
                    <div class="fill">
                        <div class="flexy baseline">
                            <label @click="toggle" class="cursor-pointer m-0">{{ display }}</label>
                            <div v-if="isHidden">
                                <small class="replicator-set-summary fill" v-html="collapsedPreview"></small>
                            </div>
                        </div>
                        <small class="help-block" v-if="instructions && !isHidden" v-html="instructions | markdown"></small>
                    </div>
                    <div class="btn-group icon-group action-more">
                        <button type="button" class="btn-more dropdown-toggle"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="icon icon-dots-three-vertical"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <slot name="expand-collapse"></slot>
                            <li class="warning"><a @click="delete">{{ translate('cp.delete_set') }}</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <div v-show="!isHidden || goingSolo" :class="{'list-group-item p-0': ! goingSolo}" v-if="config.fields.length">
                <div class="publish-fields">
                    <div v-for="field in config.fields" :class="fieldClasses(field)">
                        <div :class="{'bard-drag-handle': goingSolo}">
                            <label v-if="hasMultipleFields" class="block" :class="{'bold': field.bold}">
                                <template v-if="field.display">{{ field.display }}</template>
                                <template v-if="!field.display">{{ field.name | capitalize }}</template>
                                <i class="required" v-if="field.required">*</i>
                            </label>

                            <small class="help-block" v-if="field.instructions" v-html="field.instructions | markdown"></small>
                        </div>

                        <component :is="componentName(field.type)"
                                :name="parentName + '.' + index + '.' + field.name"
                                :data.sync="data[field.name]"
                                :config="field">
                        </component>
                    </div>
                </div>
            </div>
        </div>

        <slot name="divider-end"></slot>

    </div>

</template>

<script>
import ReplicatorSet from '../replicator/ReplicatorSet';

export default {

    mixins: [ReplicatorSet],

    methods: {
        focusAt(position) {
            this.focus();
        }
    },

    computed: {
        goingSolo() {
            const firstFieldtype = _.first(this.config.fields).type;
            const supportedFieldtypes = ['assets'];

            return this.config.fields.length === 1
                && _.contains(supportedFieldtypes, firstFieldtype);
        }
    },

    events: {
        'asset-field.delete-bard-set': function () {
            this.$emit('deleted', this.index);
        }
    }

}
</script>
