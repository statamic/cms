<template>
    <div class="max-w-lg mt-2 mx-auto">

        <div class="rounded p-3 lg:px-7 lg:py-5 shadow bg-white">
            <header class="text-center mb-6">
                <h1 class="mb-3">{{ __('Create Navigation') }}</h1>
                <p class="text-grey" v-text="__('messages.navigation_configure_intro')" />
            </header>
            <div class="mb-5">
                <label class="font-bold text-base mb-sm" for="name">{{ __('Title') }}</label>
                <input type="text" v-model="title" class="input-text" autofocus tabindex="1">
                <div class="text-2xs text-grey-60 mt-1 flex items-center">
                    {{ __('messages.navigation_configure_title_instructions') }}
                </div>
            </div>
            <div class="mb-2">
                <label class="font-bold text-base mb-sm" for="name">{{ __('Handle') }}</label>
                <input type="text" v-model="handle" class="input-text" tabindex="2">
                <div class="text-2xs text-grey-60 mt-1 flex items-center">
                    {{ __('messages.navigation_configure_handle_instructions') }}
                </div>
            </div>
        </div>

        <div class="flex justify-center mt-4">
            <button tabindex="4" class="btn-primary mx-auto btn-lg" :disabled="! canSubmit" @click="submit">
                {{ __('Create Navigation')}}
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
        }
    },

    watch: {
        'title': function(val) {
            this.handle = this.$slugify(val, '_');
        }
    },

    computed: {
        canSubmit() {
            return Boolean(this.title && this.handle);
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
