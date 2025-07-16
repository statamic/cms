<script setup>
import { ref, computed, watch, onMounted, getCurrentInstance } from 'vue';

const props = defineProps({
    title: { type: String, required: true },
    subtitle: { type: String, default: null },
    icon: { type: String, default: null },
    submitText: { type: String, default: null },
    loading: { type: Boolean, default: false },
    route: { type: String, required: true },
    titleInstructions: { type: String, default: null },
    handleInstructions: { type: String, default: null },
});

const emit = defineEmits(['submit']);

// Get Vue instance to access global properties
const instance = getCurrentInstance();
const { $slug, $axios, $toast, $keys } = instance.appContext.config.globalProperties;

// Common data
const title = ref(null);
const handle = ref(null);
const slug = $slug.separatedBy('_');

// Common computed
const canSubmit = computed(() => title.value && handle.value);

// Common watch
watch(title, (newTitle) => {
    if (newTitle) {
        handle.value = slug.create(newTitle);
    }
});

// Common methods
const submit = () => {
    $axios
        .post(props.route, { title: title.value, handle: handle.value })
        .then((response) => {
            window.location = response.data.redirect;
        })
        .catch((error) => {
            $toast.error(error.response.data.message);
        });
};

// Common mounted
onMounted(() => {
    $keys.bindGlobal(['return', 'mod+s'], (e) => {
        e.preventDefault();

        if (canSubmit.value) {
            submit();
        }
    });
});
</script>

<template>
    <div class="mx-auto mt-4 space-y-3 lg:space-y-6 max-w-3xl">
        <header v-if="props.title || props.subtitle" class="text-center max-w-xl mx-auto py-6 lg:pt-12 xl:pt-16">
            <ui-heading v-if="props.title" size="2xl" :level="1" :icon="props.icon" :text="props.title" class="justify-center" />
            <ui-subheading v-if="props.subtitle" class="mt-6" size="lg" :text="props.subtitle" />
        </header>

        <!-- Default form fields if no custom content -->
        <slot>
            <ui-card-panel :heading="__('Details')">
                <div class="space-y-8">
                    <ui-field
                        :label="__('Title')"
                        :instructions="props.titleInstructions"
                        :instructions-below="true"
                    >
                        <ui-input v-model="title" autofocus tabindex="1" />
                    </ui-field>
                    <ui-field
                        :label="__('Handle')"
                        :instructions="props.handleInstructions"
                        :instructions-below="true"
                    >
                        <ui-input v-model="handle" tabindex="2" :loading="slug.busy" />
                    </ui-field>
                </div>
            </ui-card-panel>
        </slot>

        <slot name="footer">
            <footer class="flex justify-center">
                <ui-button
                    variant="primary"
                    size="lg"
                    @click="submit"
                    type="submit"
                    :loading="loading"
                    :disabled="!canSubmit"
                    :text="submitText || props.title"
                />
            </footer>
        </slot>
    </div>
</template>
