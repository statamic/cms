<template>
    <stack narrow name="nav-item-editor" @closed="$emit('closed')" v-slot="{ close }">
        <div class="dark:bg-dark-800 flex h-full flex-col bg-white">
            <div
                class="dark:border-dark-900 dark:bg-dark-600 flex items-center justify-between border-b border-gray-300 bg-gray-200 px-6 py-2 text-lg font-medium"
            >
                {{ creating ? __('Add Section') : __('Edit Section') }}
                <button type="button" class="btn-close" @click="close" v-html="'&times'" />
            </div>

            <div class="flex-1 overflow-auto">
                <div class="px-2">
                    <div class="publish-fields @container">
                        <div class="form-group publish-field w-full" :class="{ 'has-error': validate }">
                            <div class="field-inner">
                                <label class="mb-2 text-sm font-medium"
                                    >{{ __('Display') }} <span class="text-red-500">*</span></label
                                >
                                <text-input v-model="section" :focus="true" />
                                <div v-if="validate" class="help-block mt-2 text-red-500">
                                    <p>{{ __('statamic::validation.required') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <button
                        class="btn-primary w-full"
                        :class="{ 'opacity-50': false }"
                        :disabled="false"
                        @click="save"
                        v-text="__('Save')"
                    />
                </div>
            </div>
        </div>
    </stack>
</template>

<script>
export default {
    emits: ['closed', 'updated'],

    props: {
        creating: false,
        sectionItem: {},
    },

    data() {
        return {
            section: this.sectionItem?.data?.text || '',
            saveKeyBinding: null,
            validate: false,
        };
    },

    created() {
        this.saveKeyBinding = this.$keys.bindGlobal(['enter', 'mod+enter', 'mod+s'], (e) => {
            e.preventDefault();
            this.save();
        });
    },

    unmounted() {
        this.saveKeyBinding.destroy();
    },

    methods: {
        save() {
            this.validate = false;

            if (!this.section) {
                this.validate = true;
                return;
            }

            this.$emit('updated', this.section, this.sectionItem);
        },
    },
};
</script>
