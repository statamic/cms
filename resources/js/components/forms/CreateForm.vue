<template>
    <div class="max-w-lg mt-4 mx-auto">

        <div class="rounded p-6 lg:px-20 lg:py-10 shadow bg-white dark:bg-dark-600 dark:shadow-dark">
            <header class="text-center mb-16">
                <h1 class="mb-6">{{ __('Create Form') }}</h1>
                <p class="text-gray" v-text="__('messages.form_configure_intro')" />
            </header>
            <div class="mb-10">
                <label class="font-bold text-base mb-1" for="name">{{ __('Title') }}</label>
                <input type="text" v-model="title" class="input-text" autofocus tabindex="1">
                <div class="text-2xs text-gray-600 mt-2 flex items-center">
                    {{ __('messages.form_configure_title_instructions') }}
                </div>
            </div>
            <div class="mb-4">
                <label class="font-bold text-base mb-1" for="name">{{ __('Handle') }}</label>
                <div class="relative">
                    <loading-graphic inline text="" v-if="slug.busy" class="absolute top-3 right-3"/>
                    <input type="text" v-model="handle" class="input-text" tabindex="2">
                </div>
                <div class="text-2xs text-gray-600 mt-2 flex items-center">
                    {{ __('messages.form_configure_handle_instructions') }}
                </div>
            </div>
        </div>

        <div class="flex justify-center mt-8">
            <button tabindex="4" class="btn-primary mx-auto btn-lg" :disabled="! canSubmit" @click="submit">
                {{ __('Create Form')}}
            </button>
        </div>
    </div>
</template>

<script>

export default {

    props: {
        route: {
            type: String
        }
    },

    data() {
        return {
            title: null,
            handle: null,
            slug: this.$slug.async().separatedBy('_'),
        }
    },

    watch: {
        title(title) {
            this.slug.create(title).then(slug => this.handle = slug);
        }
    },

    computed: {
        canSubmit() {
            return Boolean(this.title && this.handle && !this.slug.busy);
        },
    },

    methods: {
        submit() {
            this.$axios.post(this.route, {title: this.title, handle: this.handle}).then(response => {
                window.location = response.data.redirect;
            }).catch(error => {
                this.$toast.error(error.response.data.message);
            });
        }
    },

    mounted() {
        this.$keys.bindGlobal(['return'], e => {
            if (this.canSubmit) {
                this.submit();
            }
        });
    }
}
</script>
