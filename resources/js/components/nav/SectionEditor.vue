<template>
    <Stack
	    size="narrow"
	    :title="creating ? __('Add Section') : __('Edit Section')"
	    open
	    @update:open="$emit('closed')"
    >
        <div class="m-2 flex h-full flex-col rounded-xl bg-white dark:bg-gray-800">
            <div class="flex-1 overflow-auto">
                <div class="p-3 flex flex-col space-y-6">
                    <Field id="display" :label="__('Display')" required>
                        <Input id="display" v-model="section" :focus="true" :error="validate ? __('statamic::validation.required') : null" />
                    </Field>

                    <Button variant="primary" :text="__('Save')" @click="save" />
                </div>
            </div>
        </div>
    </Stack>
</template>

<script>
import { Button, Heading, Field, Input, Stack } from '@/components/ui';

export default {
    components: {
        Button,
        Heading,
        Field,
        Input,
	    Stack,
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

	beforeUnmount() {
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
