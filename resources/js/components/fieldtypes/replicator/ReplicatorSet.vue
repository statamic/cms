<template>

    <div class="list-group">
        <div class="list-group-item group-header pl-3 drag-handle" :class="{'collapsed': isHidden}" v-on:dblclick="toggle">
            <div class="flexy">
                <div class="fill">
                    <div class="flexy baseline">
                        <label @click="toggle" class="clickable">{{ display }}</label>
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
                        <li class="divider"></li>
                        <slot name="add-sets"></slot>
                    </ul>
                </div>
            </div>
        </div>
        <div class="list-group-item p-0" v-show="!isHidden">
            <div class="publish-fields">
                <div v-for="field in config.fields" :class="fieldClasses(field)">
                        <label class="block" v-if="hasMultipleFields" :class="{'bold': field.bold}">
                            <template v-if="field.display">{{ field.display }}</template>
                            <template v-if="!field.display">{{ field.name | capitalize }}</template>
                            <i class="required" v-if="field.required">*</i>
                        </label>

                        <small class="help-block" v-if="field.instructions" v-html="field.instructions | markdown"></small>

                        <component :is="componentName(field.type)"
                                   :name="parentName + '.' + index + '.' + field.name"
                                   :data.sync="data[field.name]"
                                   :config="field">
                        </component>
                </div>
            </div>
        </div>
    </div>

</template>

<script>
import ReplicatorSet from './ReplicatorSet';

export default {

    mixins: [ReplicatorSet]

}
</script>
