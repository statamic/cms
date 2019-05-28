<template>

    <stack narrow name="page-tree-linker" @closed="$emit('closed')">
        <div slot-scope="{ close }" class="bg-white h-full flex flex-col">

            <div class="bg-grey-20 px-3 py-1 border-b border-grey-30 text-lg font-medium flex items-center justify-between">
                {{ __('Page') }}
                <button
                    type="button"
                    class="btn-close"
                    @click="close"
                    v-html="'&times'" />
            </div>

            <div class="flex-1 overflow-auto">

                <div class="publish-fields">
                    <form-group
                        display="Title"
                        handle="title"
                        instructions="The display text for this link. Leave blank to use the URL."
                        v-model="title"
                    />
                    <form-group
                        display="URL"
                        instructions="May be an internal or external URL. Leave blank if you want a text-only page (for headers, etc)"
                        handle="url"
                        v-model="url"
                    />

                </div>

                <div class="p-3">
                    <button @click="submit" class="btn btn-primary w-full">Submit</button>
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

    }

}
</script>
