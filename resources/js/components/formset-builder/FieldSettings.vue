<template>
    <div>

        <div class="form-group">
            <label>{{ __('Display Text') }}</label>
            <small class="help-block">{{ __('cp.display_text_instructions') }}</small>
            <input type="text" class="input-text" v-model="field.display" v-focus="true" />
        </div>

        <div class="form-group">
            <label>{{ __('cp.field_name') }}</label>
            <small class="help-block">{{ __('cp.field_name_instructions') }}</small>
            <input type="text" class="input-text" v-model="field.name" @keydown="isNameModified = true" />
        </div>

        <div class="form-group">
            <label>{{ __('cp.validation_rules') }}</label>
            <small class="help-block">{{ __('Validation rule instructions...') }}</small>
            <input type="text" class="input-text" v-model="field.validate" />
        </div>

    </div>
</template>

<script>
export default {

    props: ['field'],

    data: function() {
        return {
            isNameModified: true
        };
    },

    mounted() {
        var self = this;

        // For new fields, we'll slugify the display name into the field name.
        // If they edit the name, we'll stop.
        if (this.field.isNew) {
            this.isNameModified = false;
            delete this.field.isNew;

            this.$watch('field.display', function(display) {
                if (! this.isNameModified) {
                    this.field.name = this.$slugify(display, '_');
                }
            });
        }
    }

};
</script>
