<template>

    <publish-field-meta
        :config="config"
        :initial-value="value"
        :initial-meta="meta"
    >
    <div slot-scope="{ meta, value, loading: loadingMeta }" :class="classes">
        <div class="field-inner">
            <label class="publish-field-label" :class="{'font-bold': config.bold}" :for="fieldId">
                <span
                    :class="{ 'text-grey-60': syncable && isSynced }"
                    v-text="labelText"
                    v-tooltip="{content: config.handle, delay: 500, autoHide: false}"
                />
                <i class="required ml-sm" v-if="config.required">*</i>
                <avatar v-if="isLocked" :user="lockingUser" class="w-4 rounded-full -mt-px ml-1 mr-1" v-tooltip="lockingUser.name" />
                <span v-if="isReadOnly && !isSection" class="text-grey-50 font-normal text-2xs mx-sm">
                    {{ isLocked ? __('Locked') : __('Read Only') }}
                </span>
                <svg-icon name="translate" class="h-4 ml-sm w-4 text-grey-60" v-if="isLocalizable && !isSection" v-tooltip.top="__('Localizable field')" />

                <button
                    v-if="!isReadOnly && !isSection"
                    v-show="syncable && isSynced"
                    class="outline-none"
                    :class="{ flex: syncable && isSynced }"
                    @click="$emit('desynced')"
                >
                    <svg-icon name="hyperlink" class="h-4 w-4 ml-.5 mb-sm text-grey-60"
                        v-tooltip.top="__('messages.field_synced_with_origin')" />
                </button>

                <button
                    v-if="!isReadOnly && !isSection"
                    v-show="syncable && !isSynced"
                    class="outline-none"
                    :class="{ flex: syncable && !isSynced }"
                    @click="$emit('synced')"
                >
                    <svg-icon name="hyperlink-broken" class="h-4 w-4 ml-.5 mb-sm text-grey-60"
                        v-tooltip.top="__('messages.field_desynced_from_origin')" />
                </button>
            </label>

            <div
                class="help-block -mt-1"
                v-if="instructions && config.instructions_position !== 'below'"
                v-html="instructions" />
        </div>

        <loading-graphic v-if="loadingMeta" :size="16" :inline="true" />

        <slot name="fieldtype" v-if="!loadingMeta">
            <div class="text-xs text-red" v-if="!fieldtypeComponentExists">Component <code v-text="fieldtypeComponent"></code> does not exist.</div>
            <component
                v-else
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
            class="help-block mt-1"
            v-if="instructions && config.instructions_position === 'below'"
            v-html="instructions" />

        <div v-if="hasError">
            <small class="help-block text-red mt-1 mb-0" v-for="(error, i) in errors" :key="i" v-text="error" />
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
        canToggleLabel: Boolean,
    },

    data() {
        return {
            showHandle: false
        }
    },

    inject: {
        storeName: { default: null }
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
                ? this.renderMarkdownAndLinks(this.config.instructions)
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

        isSection() {
            return this.config.type === 'section';
        },

        classes() {
            return [
                'form-group publish-field',
                `publish-field__` + this.config.handle,
                `${this.config.component || this.config.type}-fieldtype`,
                `field-${tailwind_width_class(this.config.width)}`,
                this.isReadOnly ? 'read-only-field' : '',
                this.config.classes || '',
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
            if (this.showHandle) return this.config.handle
            return this.config.display
                || Vue.$options.filters.titleize(Vue.$options.filters.deslugify(this.config.handle));
        }

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

        toggleLabel() {
            if (this.canToggleLabel) {
                this.showHandle = ! this.showHandle
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
        }

    }
}

</script>
