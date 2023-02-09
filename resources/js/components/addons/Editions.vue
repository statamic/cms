<template>
    <div class="card mb-6___REPLACED">
        <div class="little-heading p-0 mb-2___REPLACED text-grey-70" v-text="__('Editions')" />

        <div class="flex items-center">
            <div class="btn-group">
                <button
                    v-for="edition in addon.editions"
                    :key="edition.handle"
                    class="btn px-4___REPLACED"
                    :class="{ 'disabled': buttonDisabled(edition) }"
                    :disabled="buttonDisabled(edition)"
                    v-text="label(edition)"
                    @click="select(edition)"
                />
            </div>

            <loading-graphic inline v-if="saving" text="" class="ml-4___REPLACED" />
        </div>
    </div>
</template>

<script>
export default {
    props: {
        addon: { type: Object, required: true }
    },

    data() {
        return {
            selected: this.addon.edition,
            saving: false
        };
    },

    watch: {
        saving(saving) {
            this.$progress.loading(saving);
        },

        'addon.edition': function (edition) {
            this.selected = edition;
        }
    },

    methods: {
        select(edition) {
            this.saving = true;

            this.$axios.post(cp_url("addons/editions"), {
                addon: this.addon.package,
                edition: edition.handle
            }).then(response => {
                this.selected = edition.handle;
                this.saving = false;
            });
        },

        label(edition) {
            const free = __("Free");
            const price = edition.price === 0 ? free : `$${edition.price}`;

            if (price === free && edition.name === free) {
                return edition.name;
            }

            return `${edition.name} (${price})`;
        },

        buttonDisabled(edition) {
            return !this.addon.installed || edition.handle === this.selected;
        }
    }
};
</script>
