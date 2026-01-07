<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount, getCurrentInstance } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    /** The title displayed at the top of the form. */
    title: { type: String, required: true },
    /** Optional subtitle displayed below the title. */
    subtitle: { type: String, default: null },
    /** Icon name to display next to the title. [Browse available icons](/?path=/story/components-icon--all-icons) */
    icon: { type: String, default: null },
    /** Text for the submit button. Defaults to the title if not provided. */
    submitText: { type: String, default: null },
    /** When `true`, the submit button shows a loading state. */
    loading: { type: Boolean, default: false },
    /** The URL for form data to be submitted to. */
    route: { type: String, required: true },
    /** Instructions for the title field. */
    titleInstructions: { type: String, default: null },
    /** Instructions for the handle field. */
    handleInstructions: { type: String, default: null },
    /** When `true`, the handle field is not displayed. */
    withoutHandle: { type: Boolean, default: false },
});

const instance = getCurrentInstance();
const { $slug, $axios, $toast, $keys } = instance.appContext.config.globalProperties;

const title = ref(null);
const handle = ref(null);
const slug = $slug.separatedBy('_');
const errors = ref({});
const saveBinding = ref(null);

const canSubmit = computed(() => {
    return title.value && (props.withoutHandle || handle.value);
});

watch(title, (newTitle) => {
    if (newTitle && !props.withoutHandle) {
        handle.value = slug.create(newTitle);
    }
});

const submit = () => {
    let payload = { title: title.value };

    if (!props.withoutHandle) {
        payload.handle = handle.value;
    }

    $axios
        .post(props.route, payload)
        .then((response) => {
            router.get(response.data.redirect);
        })
        .catch((error) => {
            $toast.error(error.response.data.message);
            errors.value = error.response.data.errors;
        });
};

onMounted(() => {
    saveBinding.value = $keys.bindGlobal(['return', 'mod+s'], (e) => {
        e.preventDefault();

        if (canSubmit.value) {
            submit();
        }
    });
});

onBeforeUnmount(() => saveBinding.value?.destroy());
</script>

<template>
    <div class="mx-auto mt-4 space-y-3 lg:space-y-6 max-w-3xl">
        <header v-if="props.title || subtitle" class="text-center max-w-xl mx-auto py-6 lg:pt-12 xl:pt-16">
            <ui-heading v-if="props.title" size="2xl" :level="1" :icon="props.icon" :text="props.title" class="justify-center" />
            <ui-subheading v-if="subtitle" class="mt-6" size="lg" :text="subtitle" />
        </header>

        <!-- Default form fields if no custom content -->
        <slot>
            <ui-card-panel :heading="__('Details')">
                <div class="space-y-8">
                    <ui-field
                        id="title"
                        :label="__('Title')"
                        :instructions="titleInstructions"
                        :instructions-below="true"
                        :errors="errors.title"
                    >
                        <ui-input id="title" v-model="title" autofocus />
                    </ui-field>
                    <ui-field
                        v-if="!withoutHandle"
                        id="handle"
                        :label="__('Handle')"
                        :instructions="handleInstructions"
                        :instructions-below="true"
                        :errors="errors.handle"
                    >
                        <ui-input id="handle" v-model="handle" :loading="slug.busy" />
                    </ui-field>
                </div>
            </ui-card-panel>
        </slot>

        <slot name="footer">
            <footer class="flex justify-center py-3">
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
