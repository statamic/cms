<template>
    <input
        type="text"
        v-model="slug"
        class="form-control w-full mono"
        placeholder="{{ translate('cp.new').toLowerCase() }}-{{ contentType.toLowerCase() }}"
    />
</template>


<script>
import MetaFieldtype from './MetaFieldtype';

export default {

    mixins: [MetaFieldtype, AutoSlug],

    computed: {

        slug: {
            get() {
                return this.formData.fields.slug;
            },
            set(slug) {
                this.formData.fields.slug = slug;
            }
        },

        displayFallback() {
            return translate('cp.slug');
        },

        isRequired() {
            return true;
        },

        isLocalizable() {
            return true;
        }

    },

    mounted() {
        this.autoSlug('title', 'slug');
    }

}
</script>

