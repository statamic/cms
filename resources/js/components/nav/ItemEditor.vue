<template>
    <stack narrow name="nav-item-editor" @closed="$emit('closed')" v-slot="{ close }">
        <div class="m-2 flex h-full flex-col rounded-xl bg-white dark:bg-gray-800">
            <div
                class="flex items-center justify-between rounded-t-xl border-b border-gray-300 bg-gray-50 px-4 py-2 dark:border-gray-950 dark:bg-gray-900"
            >
                <Heading size="lg">{{ creating ? __('Add Nav Item') : __('Edit Nav Item') }}</Heading>
                <Button icon="x" variant="ghost" class="-me-2" @click="close" />
            </div>

            <div class="flex-1 overflow-auto">
                <div class="p-3 flex flex-col space-y-6">
                    <Field id="display" :label="__('Display')" required>
                        <Input id="display" v-model="config.display" :focus="true" :error="validateDisplay ? __('statamic::validation.required') : null" />
                    </Field>

                    <Field id="url" :label="__('URL')" required>
                        <Input id="url" v-model="config.url" :error="validateUrl ? __('statamic::validation.required') : null" />
                    </Field>

                    <Field v-if="!isChild" id="icon" :label="__('Icon')">
                        <publish-field-meta
                            :config="{ handle: 'icon', type: 'icon' }"
                            :initial-value="config.icon"
                            v-slot="{ meta, value, loading, config: fieldtypeConfig }"
                        >
                            <icon-fieldtype
                                v-if="!loading"
                                handle="icon"
                                :config="fieldtypeConfig"
                                :meta="meta"
                                :value="value"
                                @update:value="config.icon = $event"
                            />
                        </publish-field-meta>
                    </Field>

                    <Button variant="primary" :text="__('Save')" @click="save" />
                </div>
            </div>
        </div>
    </stack>
</template>

<script>
import { Button, Heading, Field, Input } from '@/components/ui';
import { data_get } from '../../bootstrap/globals.js';

export default {
    components: {
        Button,
        Heading,
        Field,
        Input,
    },

    emits: ['closed', 'updated'],

    props: {
        creating: false,
        item: null,
        isChild: false,
    },

    data() {
        return {
            config: { ...(this.item?.data?.config || this.createNewItem()) },
            saveKeyBinding: null,
            validateDisplay: false,
            validateUrl: false,
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
        createNewItem() {
            return {
                display: '',
                url: '',
                icon: null,
            };
        },

        save() {
            this.validateDisplay = false;
            this.validateUrl = false;

            if (!this.config.display) {
                this.validateDisplay = true;
            }

            if (!this.config.url) {
                this.validateUrl = true;
            }

            if (this.validateDisplay || this.validateUrl) {
                return;
            }

            let config = clone(this.config);

            if (this.isChild) {
                config.icon = null;
            } else if (!config.icon) {
                config.icon = data_get(this.item, 'original.icon');
            }

            this.$emit('updated', this.config, this.item);
        },
    },
};
</script>
