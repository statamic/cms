<template>

    <modal :show.sync="show" :saving="saving" class="modal-small">
        <template slot="header">
            {{ translate('cp.rename_file') }}
        </template>

        <template slot="body">
            <div class="alert alert-warning">{{{ warningText | markdown }}}</div>

            <div class="alert alert-danger" v-if="errors">
                <p v-for="error in errors">{{ error }}</p>
            </div>

            <div class="form-group">
                <input type="text" autofocus
                       class="form-control"
                       v-el:input
                       v-model="filename"
                       @keyup.esc="cancel"
                       @keyup.enter="save" />
            </div>
        </template>

        <template slot="footer">
            <button class="btn btn-primary" :disabled="!hasChanged" @click="save">Save</button>
            <button type="button" class="btn" @click="cancel">{{ translate('cp.cancel') }}</button>
        </template>
    </modal>

</template>


<script>
export default {

    props: ['asset'],


    data() {
        return {
            show: true,
            filename: null,
            saving: false,
            errors: null,
            warningText: translate('cp.rename_file_warning')
        }
    },


    computed: {

        hasChanged() {
            return this.asset.filename !== this.filename;
        }

    },


    ready() {
        this.filename = this.asset.filename;
    },


    watch: {

        show(val) {
            if (!val) this.cancel();
        }

    },


    methods: {

        save() {
            if (! this.hasChanged) return;

            this.saving = true;

            const url = cp_url('/assets/rename/' + this.asset.id.replace('::', '/'));

            this.$http.post(url, { filename: this.filename }).success((response) => {
                this.$emit('saved', response);
                this.cancel();
            }).error((response) => {
                this.saving = false;
                this.errors = response;
                this.$els.input.focus();
            })
        },

        cancel() {
            this.$emit('closed');
        }

    }

}
</script>
