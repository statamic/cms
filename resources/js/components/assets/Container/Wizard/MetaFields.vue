<template>

    <div class="publish-fields">

        <div class="form-group">
            <label class="block">{{ translate('cp.title') }}</label>
            <small class="help-block">{{ translate('cp.asset_name_instructions') }}</small>
            <input type="text" class="form-control" v-model="title" autofocus="autofocus" />
        </div>

        <div class="form-group">
            <label class="block">{{ translate('cp.handle') }}</label>
            <div class="help-block">{{{ translate('cp.asset_handle_instructions') }}}</div>
            <input type="text" class="form-control" v-model="handle" @keydown="isHandleModified = true" />
        </div>

        <div class="form-group">
            <button class="btn btn-default" @click="submit" :disabled="!canContinue">Next Step</button>
        </div>

    </div>

</template>

<script>
export default {

    props: ['title', 'handle'],


    data() {
        return {
            isHandleModified: false
        }
    },


    computed: {

        canContinue() {
            return this.title && this.handle;
        }

    },


    watch: {

        title(title) {
            if (this.isHandleModified) {
                return;
            }

            this.handle = this.$slugify(title, '_');
        }

    },


    methods: {

        submit() {
            this.$emit('submit');
        }

    }

}
</script>
