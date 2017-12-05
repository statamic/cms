<template>

    <div class="list-group">
        <div class="list-group-item group-header drag-handle" :class="{'collapsed': isHidden}" v-on:dblclick="toggle">
            <div class="flexy">
                <div class="fill">
                    <div class="flexy baseline">
                        <label @click="toggle" class="clickable">{{ display }}</label>
                        <div v-if="isHidden">
                            <small class="replicator-set-summary fill" v-text="collapsedPreview"></small>
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
        <div class="list-group-item" v-show="!isHidden">
            <div class="row">
                <div v-for="field in config.fields" class="{{ colClass(field.width) }}">
                    <div class="form-group {{ field.type }}-fieldtype">
                        <label class="block" v-if="hasMultipleFields" :class="{'bold': field.bold}">
                            <template v-if="field.display">{{ field.display }}</template>
                            <template v-if="!field.display">{{ field.name | capitalize }}</template>
                            <i class="required" v-if="field.required">*</i>
                        </label>

                        <small class="help-block" v-if="field.instructions" v-html="field.instructions | markdown"></small>

                        <component :is="field.type + '-fieldtype'"
                                   :name="parentName + '.' + index + '.' + field.name"
                                   :data.sync="data[field.name]"
                                   :config="field">
                        </component>
                    </div>
                </div>
            </div>
        </div>
    </div>

</template>

<script>
export default {

    props: ['data', 'index', 'config', 'parentName', 'sets'],

    data() {
        return {
            collapsedPreview: null
        }
    },

    computed: {

        display() {
            return this.config.display || this.data.type;
        },

        instructions() {
            return this.config.instructions;
        },

        hasMultipleFields() {
            return this.config.fields.length > 1;
        },

        isHidden() {
            return this.data['#hidden'] === true;
        }

    },

    watch: {

        data: {
            deep: true,
            handler() {
                this.collapsedPreview = this.getCollapsedPreview();
            }
        }

    },

    ready() {
        this.collapsedPreview = this.getCollapsedPreview();
    },

    methods: {

        toggle() {
            (this.isHidden) ? this.expand() : this.collapse();
        },

        expand(all) {
            Vue.set(this.data, '#hidden', false);

            // The 'all' variable will be true if it was called from the expandAll() method.
            this.$emit('expanded', this, all);
        },

        collapse() {
            Vue.set(this.data, '#hidden', true);
        },

        delete() {
            swal({
                type: 'warning',
                title: translate('cp.are_you_sure'),
                confirmButtonText: translate('cp.yes_im_sure'),
                cancelButtonText: translate('cp.cancel'),
                showCancelButton: true
            }, () => {
                this.$emit('deleted', this.index);
            });
        },

        /**
         * Bootstrap Column Width class
         * Takes a percentage based integer and converts it to a bootstrap column number
         * eg. 100 => 12, 50 => 6, etc.
         */
        colClass: function(width) {
            if (this.$root.isPreviewing) {
                return 'col-md-12';
            }

            width = width || 100;
            return 'col-md-' + Math.round(width / 8.333);
        },

        getCollapsedPreview() {
            return _.map(this.$children, (fieldtype) => {
                return (typeof fieldtype.getReplicatorPreviewText !== 'undefined')
                    ? fieldtype.getReplicatorPreviewText()
                    : JSON.stringify(fieldtype.data);
            }).filter(t => t !== null && t !== '' && t !== undefined).join(' / ');
        },

        focus() {
            // We want to focus the first field.
            const field = this.$children[0];

            // If the component doesn't know how to focus, we cannot.
            if (typeof field.focus !== 'function') return;

            field.focus();
        }

    }

}
</script>
