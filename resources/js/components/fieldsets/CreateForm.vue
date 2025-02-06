<template>
    <div class="mx-auto mt-4 max-w-lg">
        <div class="rounded bg-white p-6 shadow dark:bg-dark-600 dark:shadow-dark lg:px-20 lg:py-10">
            <header class="mb-16 text-center">
                <h1 class="mb-6">{{ __('Create Fieldset') }}</h1>
                <p class="text-gray" v-text="__('messages.fields_fieldsets_description')" />
            </header>
            <div class="mb-10">
                <label class="mb-1 text-base font-bold" for="name">{{ __('Title') }}</label>
                <input type="text" v-model="title" class="input-text" autofocus tabindex="1" />
                <div class="mt-2 flex items-center text-2xs text-gray-600">
                    {{ __('messages.fieldsets_title_instructions') }}
                </div>
            </div>
            <div class="mb-4">
                <label class="mb-1 text-base font-bold" for="name">{{ __('Handle') }}</label>
                <div class="relative">
                    <loading-graphic inline text="" v-if="slug.busy" class="absolute right-3 top-3" />
                    <input type="text" v-model="handle" class="input-text" tabindex="2" />
                </div>
                <div class="mt-2 flex items-center text-2xs text-gray-600">
                    {{ __('messages.fieldsets_handle_instructions') }}
                </div>
            </div>
        </div>

        <div class="mt-8 flex justify-center">
            <button tabindex="4" class="btn-primary btn-lg mx-auto" :disabled="!canSubmit" @click="submit">
                {{ __('Create Fieldset') }}
            </button>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        route: {
            type: String,
        },
    },

    data() {
        return {
            title: null,
            handle: null,
            slug: this.$slug.async().separatedBy('_'),
        };
    },

    watch: {
        title(title) {
            this.slug.create(title).then((slug) => (this.handle = slug));
        },
    },

    computed: {
        canSubmit() {
            return Boolean(this.title && this.handle && !this.slug.busy);
        },
    },

    methods: {
        submit() {
            this.$axios
                .post(this.route, { title: this.title, handle: this.handle })
                .then((response) => {
                    window.location = response.data.redirect;
                })
                .catch((error) => {
                    this.$toast.error(error.response.data.message);
                });
        },
    },

    mounted() {
        this.$keys.bindGlobal(['return'], (e) => {
            if (this.canSubmit) {
                this.submit();
            }
        });
    },
};
</script>
