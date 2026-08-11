<template>
    <Combobox
        v-if="!loading"
        :clearable="config.clearable"
        :placeholder="__(config.placeholder)"
        :read-only="isReadOnly"
        :disabled="config.disabled"
        :options="options"
        :searchable="true"
        :multiple="false"
        :model-value="value"
        :id="id"
        :discrete-focus-outline="true"
        @update:modelValue="update"
    >
        <template #no-options>
            <div v-text="__('No templates to choose from.')" />
        </template>
    </Combobox>
</template>

<script>
import Fieldtype from './Fieldtype.vue';
import { Combobox } from '@/components/ui';
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

// Raw template list shared across instances for the page-view lifetime.
const templatesCache = ref(null);
let navigationListenerAttached = false;

function ensureCacheClearedOnNavigation() {
    if (navigationListenerAttached) return;
    navigationListenerAttached = true;
    router.on('before', () => {
        templatesCache.value = null;
    });
}

export default {
    components: { Combobox },
    mixins: [Fieldtype],

    data: function () {
        return {
            loading: true,
            options: [],
        };
    },

    mounted() {
        ensureCacheClearedOnNavigation();

        if (templatesCache.value) {
            this.applyTemplates(templatesCache.value);
            return;
        }

        this.$axios.get(cp_url('api/templates')).then((response) => {
            templatesCache.value = response.data;
            this.applyTemplates(response.data);
        });
    },

    methods: {
        applyTemplates(templates) {
            // Filter out partials
            if (this.config.hide_partials) {
                templates = templates.filter((template) => {
                    return !(template.startsWith('partials/') || template.match(/(^_.*|\/_.*|\._.*)/g));
                });
            }

            // Filter out error templates
            templates = templates.filter((template) => {
                return !template.startsWith('errors/');
            });

            // Filter templates in folder
            if (this.config.folder) {
                templates = templates.filter((template) => {
                    return template.startsWith(`${this.config.folder}/`);
                });
            }

            // Set default
            var options = [];

            // Prepend @blueprint as an option
            if (this.config.blueprint) {
                options.push({ label: __('Map to Blueprint'), value: '@blueprint' });
            }

            templates.forEach((template) => {
                options.push({
                    label: this.config.folder ? template.substring(this.config.folder.length + 1) : template,
                    value: template,
                });
            });

            this.options = options;
            this.loading = false;
        },
    },
};
</script>
