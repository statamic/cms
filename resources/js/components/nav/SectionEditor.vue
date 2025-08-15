<template>
    <stack narrow name="nav-item-editor" @closed="$emit('closed')" v-slot="{ close }">
        <div class="m-2 flex h-full flex-col rounded-xl bg-white dark:bg-gray-800">
            <div
                class="flex items-center justify-between rounded-t-xl border-b border-gray-300 bg-gray-50 px-4 py-2 dark:border-gray-950 dark:bg-gray-900"
            >
                <Heading size="lg">{{ creating ? __('Add Section') : __('Edit Section') }}</Heading>
                <Button icon="x" variant="ghost" class="-me-2" @click="close" />
            </div>

            <div class="flex-1 overflow-auto">
                <div class="p-3 flex flex-col space-y-6">
                    <Field id="display" :label="__('Display')" required>
                        <Input id="display" v-model="section" :focus="true" :error="validate ? __('statamic::validation.required') : null" />
                    </Field>

                    <Button variant="primary" :text="__('Save')" @click="save" />
                </div>
            </div>
        </div>
    </stack>
</template>

<script>
import { Button, Heading, Field, Input } from 'statamic';

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
