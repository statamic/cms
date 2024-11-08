<template>

    <publish-field-meta
        :config="config"
        :initial-value="value"
        :initial-meta="meta"
    >
    <div slot-scope="{ meta, value, loading: loadingMeta }" :class="classes">
        <div class="field-inner">
            <label v-if="showLabel" class="publish-field-label" :class="{'font-bold': config.bold}" :for="fieldId">
                <span
                    v-if="showLabelText"
                    class="rtl:ml-1 ltr:mr-1"
                    :class="{ 'text-gray-600': syncable && isSynced }"
                    v-text="__(labelText)"
                    v-tooltip="{content: config.handle, delay: 500, autoHide: false}"
                />
                <i class="required rtl:ml-1 ltr:mr-1" v-if="showLabelText && config.required">*</i>
                <avatar v-if="isLocked" :user="lockingUser" class="w-6 h-6 rounded-full -mt-px rtl:mr-2 ltr:ml-2 rtl:ml-2 ltr:mr-2" v-tooltip="lockingUser.name" />
                <span v-if="isReadOnly && !isTab && !isSection" class="text-gray-500 dark:text-dark-200 font-normal text-2xs rtl:ml-1 ltr:mr-1 mt-0.5">
                    {{ isLocked ? __('Locked') : __('Read Only') }}
                </span>
                <svg-icon name="translate" class="h-4 rtl:ml-1 ltr:mr-1 w-4 text-gray-600" v-if="isLocalizable && !isTab" v-tooltip.top="__('Localizable field')" />

                <button
                    v-if="!isReadOnly && !isTab"
                    v-show="syncable && isSynced"
                    class="outline-none"
                    :class="{ flex: syncable && isSynced }"
                    @click="$emit('desynced')"
                >
                    <svg-icon name="light/hyperlink" class="h-4 w-4 rtl:ml-1.5 ltr:mr-1.5 mb-1 text-gray-600"
                        v-tooltip.top="__('messages.field_synced_with_origin')" />
                </button>

                <button
                    v-if="!isReadOnly && !isTab"
                    v-show="syncable && !isSynced"
                    class="outline-none"
                    :class="{ flex: syncable && !isSynced }"
                    @click="$emit('synced')"
                >
                    <svg-icon name="light/hyperlink-broken" class="h-4 w-4 rtl:ml-1.5 ltr:mr-1.5 mb-1 text-gray-600"
                        v-tooltip.top="__('messages.field_desynced_from_origin')" />
                </button>
            </label>

            <div
                class="help-block" :class="{ '-mt-2': showLabel }"
                v-if="instructions && config.instructions_position !== 'below'"
                v-html="instructions" />

            <div class="field-dropdown" v-if="mounted && hasDropdown">
                <div class="quick-list">
                    <div class="quick-list-content">
                        <a
                            v-for="(action, index) in fieldQuickActions"
                            :key="index"
                            @click="fieldRunAction(action)"
                            v-tooltip="action.title"
                        >
                            <svg-icon :name="fieldActionIcon(action)" class="h-3 w-3" />
                        </a>
                    </div>
                    <dropdown-list placement="left-start" :offset="[7, -3]">
                        <dropdown-actions :actions="fieldActions" @run="fieldRunAction" v-if="fieldActions.length" />
                        <div class="divider" />
                        <dropdown-actions :actions="fieldInternalActions" @run="fieldRunAction" v-if="fieldInternalActions.length" />
                    </dropdown-list>
                </div>
            </div>

        </div>

        <loading-graphic v-if="loadingMeta" :size="16" :inline="true" />

        <slot name="fieldtype" v-if="!loadingMeta">
            <div class="text-xs text-red-500" v-if="!fieldtypeComponentExists">Component <code v-text="fieldtypeComponent"></code> does not exist.</div>
            <component
                v-else
                ref="field"
                :is="fieldtypeComponent"
                :config="config"
                :value="value"
                :meta="meta"
                :handle="config.handle"
                :name-prefix="namePrefix"
                :field-path-prefix="fieldPathPrefix"
                :read-only="isReadOnly"
                @input="$emit('input', $event)"
                @meta-updated="$emit('meta-updated', $event)"
                @focus="focused"
                @blur="blurred"
            /> <!-- TODO: name prop should include prefixing when used recursively like inside a grid. -->
        </slot>

        <div
            class="help-block mt-2"
            v-if="instructions && config.instructions_position === 'below'"
            v-html="instructions" />

        <div v-if="hasError">
            <small class="help-block text-red-500 mt-2 mb-0" v-for="(error, i) in errors" :key="i" v-text="error" />
        </div>

    </div>
    </publish-field-meta>

</template>

<script>
import { marked } from 'marked';

export default {

    props: {
        config: {
            type: Object,
            required: true
        },
        value: {
            required: true
        },
        meta: {
        },
        errors: {
            type: Array
        },
        readOnly: Boolean,
        syncable: Boolean,
        namePrefix: String,
        fieldPathPrefix: String,
    },

    inject: {
        storeName: { default: null },
        isInsideConfigFields: { default: false },
    },

    data() {
        return {
            mounted: false,
        }
    },

    mounted() {
        this.mounted = true;
    },

    computed: {

        fieldtypeComponent() {
            return `${this.config.component || this.config.type}-fieldtype`;
        },

        fieldtypeComponentExists() {
            return Vue.options.components[this.fieldtypeComponent] !== undefined;
        },

        instructions() {
            return this.config.instructions
                ? this.renderMarkdownAndLinks(__(this.config.instructions))
                : null
        },

        hasError() {
            return this.errors && this.errors.length > 0;
        },

        isReadOnly() {
            if (this.storeState.isRoot === false && !this.config.localizable) return true;

            return this.isLocked || this.readOnly || this.config.visibility === 'read_only' || false;
        },

        isLocalizable() {
            return this.$config.get('sites').length > 1 && this.config.localizable;
        },

        isTab() {
            return this.config.type === 'tab';
        },

        isSection() {
            return this.config.type === 'section';
        },

        classes() {
            return [
                'form-group publish-field',
                `publish-field__` + this.config.handle,
                `${this.config.component || this.config.type}-fieldtype`,,
                this.isReadOnly ? 'read-only-field' : '',
                this.isInsideConfigFields ? 'config-field' : `${tailwind_width_class(this.config.width)}`,
                this.mounted && this.hasDropdown && !this.isInsideConfigFields ? 'has-dropdown' : '',
                this.config.classes || '',
                this.config.full_width_setting ? 'full-width-setting' : '',
                { 'has-error': this.hasError || this.hasNestedError }
            ];
        },

        fieldId() {
            return 'field_'+this.config.handle;
        },

        locks() {
            return this.storeState.fieldLocks || {};
        },

        isLocked() {
            return Object.keys(this.locks).includes(this.config.handle);
        },

        lockingUser() {
            if (this.isLocked) {
                let user = this.locks[this.config.handle];
                if (typeof user === 'object') return user;
            }
        },

        isSynced() {
            if (!this.syncable) return;
            return !this.storeState.localizedFields.includes(this.config.handle);
        },

        storeState() {
            return this.$store.state.publish[this.storeName] || {};
        },

        hasNestedError() {
            const prefix = `${this.fieldPathPrefix || this.config.handle}.`;

            return Object.keys(this.storeState.errors ?? []).some(handle => handle.startsWith(prefix));
        },

        labelText() {
             return this.config.display
                 || Vue.$options.filters.titleize(Vue.$options.filters.deslugify(this.config.handle));
         },

        showLabelText() {
            return !this.config.hide_display;
        },

        showLabel() {
            return this.showLabelText // Need to see the text
                || this.isReadOnly // Need to see the "Read Only" text
                || this.config.required // Need to see the asterisk
                || this.isLocked // Need to see the avatar
                || this.isLocalizable // Need to see the icon
                || this.syncable // Need to see the icon
        },

        fieldActions() {
            return this.$refs.field.visibleActions;
        },

        fieldInternalActions() {
            return this.$refs.field.visibleInternalActions;
        },

        fieldQuickActions() {
            return this.$refs.field.visibleQuickActions;
        },

        hasDropdown() {
            return this.fieldActions.length > 0 || this.fieldInternalActions.length > 0;;
        },

    },

    methods: {

        focused() {
            if (!this.isLocked) {
                this.$emit('focus');
            }
        },

        blurred() {
            if (!this.isLocked) {
                this.$emit('blur');
            }
        },

        renderMarkdownAndLinks(text) {
            var renderer = new marked.Renderer();

            renderer.link = function(href, title, text) {
                var link = marked.Renderer.prototype.link.call(this, href, title, text);
                return link.replace("<a","<a target='_blank' ");
            };

            marked.setOptions({
                renderer: renderer
            });

            return marked(text);
        },

        fieldRunAction(action) {
            this.$refs.field.runAction(action);
        },

        fieldActionIcon({ icon }) {
            icon = icon || 'image';
            return typeof icon === 'function' ? icon({ field: this }) : icon;
        },

    }
}

</script>
