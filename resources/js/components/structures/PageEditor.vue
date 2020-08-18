<template>

    <stack narrow name="page-tree-linker" @closed="$emit('closed')">
        <div slot-scope="{ close }" class="bg-white h-full flex flex-col">

            <div class="bg-grey-20 px-3 py-1 border-b border-grey-30 text-lg font-medium flex items-center justify-between">
                {{ __('Link') }}
                <button
                    type="button"
                    class="btn-close"
                    @click="close"
                    v-html="'&times'" />
            </div>

            <div class="flex-1 overflow-auto">

                <publish-fields-container>
                    <form-group
                        :display="__('Title')"
                        handle="title"
                        v-model="title"
                        :instructions="__('Link display text. Leave blank to use the URL.')"
                    />
                    <form-group
                        :display="__('URL')"
                        handle="url"
                        v-model="url"
                        :instructions="__('Enter any internal or external URL. Leave blank for a text-only item.')"
                    />

                </publish-fields-container>

                <div class="p-3">
                    <button @click="submit" class="btn-primary w-full">{{ __('Submit') }}</button>
                </div>

            </div>

        </div>
    </stack>

</template>

<script>
export default {

    props: {
        initialTitle: String,
        initialUrl: String,
    },

    data() {
        return {
            title: this.initialTitle,
            url: this.initialUrl,
        }
    },

    methods: {
        submit() {
            if (!this.title && !this.url) {
                alert('You need at least a title or URL.');
                return;
            }

            this.$emit('submitted', {
                title: this.title,
                url: this.url,
            });
        }
    },

    created() {
        // Allow key commands with a focused input
        this.$keys.stop(e => {
            return ! ['enter'].includes(e.code.toLowerCase())
        })

        this.$keys.bind('enter', this.submit)
    },

}
</script>
