<template>
    <div>
        <div class="tabs-container">
            <div class="publish-tabs tabs">
                <button class="tab-button"
                :class="{ 'active': activeTab === 'configuration' }"
                    @click="activeTab = 'configuration'"
                    v-text="__('Configuration')"
                />
                <button class="tab-button"
                :class="{ 'active': activeTab === 'validation' }"
                    @click="activeTab = 'validation'"
                    v-text="__('Validation')"
                />
            </div>
        </div>

        <publish-container
            v-if="blueprint"
            v-show="activeTab === 'configuration'"
            ref="container"
            name="collection"
            :blueprint="blueprint"
            :values="values"
            reference="collection"
            :meta="meta"
            :errors="errors"
            @updated="values = $event"
        >
            <div slot-scope="{ setFieldValue }">
                <configure-tabs @updated="setFieldValue" :enable-sidebar="false"/>
                <div class="flex justify-between py-4 border-t dark:border-dark-950">
                    <a :href="url" class="btn" v-text="__('Cancel') "/>
                    <button type="submit" class="btn-primary" @click="submit">{{ __('Save') }}</button>
                </div>
            </div>
        </publish-container>
        <div class="p-0 card" v-show="activeTab === 'validation'">
            <div class="publish-fields @container">
                <field-validation-builder
                    :config="{
                        validate: this.rules
                    }"
                    @updated="updateRules($event)"
                />
            </div>
        </div>
    </div>
</template>

<script>
import FieldValidationBuilder from '../field-validation/Rules.vue';

export default {

    components: {
        FieldValidationBuilder
    },

    props: {
        blueprint: Object,
        initialValues: Object,
        meta: Object,
        initialTitle: String,
        url: String,
        listingUrl: String,
    },

    data() {
        return {
            activeTab: 'configuration',
            error: null,
            errors: {},
            title: this.initialTitle,
            values: this.initialValues,
        }
    },

    computed: {
        rules() {
            return this.values.rules;
        }
    },

    methods: {

        clearErrors() {
            this.error = null;
            this.errors = {};
        },

        submit() {
            this.saving = true;
            this.clearErrors();

            this.$axios.patch(this.url, this.values).then(response => {
                window.location = response.data.redirect;
            }).catch(e => this.handleAxiosError(e));
        },

        handleAxiosError(e) {
            this.saving = false;
            if (e.response) {
                const { message, errors } = e.response.data;
                this.error = message;
                this.errors = errors;
                this.$toast.error(message);
            } else {
                this.$toast.error(__('Unable to save changes'));
            }
        },

        updateRules(rules) {
            this.values.rules = rules;
        },

    },

    created() {
        this.$keys.bindGlobal(['mod+s'], e => {
            e.preventDefault();
            this.submit();
        });
    },

}
</script>
