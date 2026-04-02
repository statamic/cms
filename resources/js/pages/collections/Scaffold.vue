<script setup>
import Head from '@/pages/layout/Head.vue';
import { Header, Button, Panel, Switch, Input } from '@/components/ui';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    collection: Object,
    route: String,
});

const index = `${props.collection.handle}/index`;
const show = `${props.collection.handle}/show`;

const selected = ref({
    index: true,
    show: true,
});

const canSubmit = computed(() => {
    return Object.keys(files.value).length > 0;
});

const files = computed(() => {
    var files = {};

    if (selected.value.index) {
        files.index = index;
    }

    if (selected.value.show) {
        files.show = show;
    }

    return files;
});

const submit = () => {
    router.post(props.route, files.value);
};

let submitKeyBinding;

onMounted(() => {
    submitKeyBinding = Statamic.$keys.bindGlobal(['return'], (e) => {
        if (canSubmit.value) {
            submit();
        }
    });
});

onUnmounted(() => submitKeyBinding.destroy());
</script>

<template>
    <Head :title="[__('Scaffold Views'), __(collection.title), __('Collections')]" />

    <Header :title="__('Scaffold Views')" icon="scaffold">
        <Button variant="primary" tabindex="4" :disabled="!canSubmit" @click="submit">
            {{ __('Create Views') }}
        </Button>
    </Header>

    <Panel :heading="__('messages.collection_scaffold_instructions')">
        <table class="data-table">
            <tbody>
            <tr>
                <td class="w-1/4">
                    <div class="flex items-center gap-2 sm:gap-3">
                        <Switch
                            v-model="selected.index"
                            size="sm"
                            id="field_index"
                        />
                        <label for="field_index" v-text="__('Index Template')" />
                    </div>
                </td>
                <td>
                    <Input v-model="index" :disabled="!selected.index" />
                </td>
            </tr>
            <tr>
                <td class="w-1/4">
                    <div class="flex items-center gap-2 sm:gap-3">
                        <Switch
                            v-model="selected.show"
                            size="sm"
                            id="field_template"
                        />
                        <label for="field_template" v-text="__('Show Template')" />
                    </div>
                </td>
                <td>
                    <Input v-model="show" :disabled="!selected.show" />
                </td>
            </tr>
            </tbody>
        </table>
    </Panel>
</template>
