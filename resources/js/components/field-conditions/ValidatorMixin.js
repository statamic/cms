import Validator from './Validator.js';
import { data_get } from '../../bootstrap/globals.js';

export default {
    inject: ['publishContainer'],

    methods: {
        showField(field, dottedKey) {
            let dottedFieldPath = dottedKey || field.handle;
            let dottedPrefix = dottedKey ? dottedKey.replace(new RegExp('\.' + field.handle + '$'), '') : '';

            // If we know the field is to permanently hidden, bypass validation.
            if (field.visibility === 'hidden' || this.shouldForceHiddenField(dottedFieldPath)) {
                this.setHiddenFieldState({
                    dottedKey: dottedFieldPath,
                    hidden: 'force',
                    omitValue: false,
                });

                return false;
            }

            // Use validation to determine whether field should be shown.
            let validator = new Validator(
                field,
                { ...this.values, ...this.extraValues },
                dottedFieldPath,
                this.publishContainer.store,
            );
            let passes = validator.passesConditions();

            // If the field is configured to always save, never omit value.
            if (field.always_save === true) {
                this.setHiddenFieldState({
                    dottedKey: dottedFieldPath,
                    hidden: !passes,
                    omitValue: false,
                });

                return passes;
            }

            // Ensure DOM is updated to ensure all revealers are properly loaded and tracked before committing to store.
            this.$nextTick(() => {
                this.setHiddenFieldState({
                    dottedKey: dottedFieldPath,
                    hidden: !passes,
                    omitValue: field.type === 'revealer' || !validator.passesNonRevealerConditions(dottedPrefix),
                });
            });

            return passes;
        },

        setHiddenFieldState({ dottedKey, hidden, omitValue }) {
            const currentValue = this.publishContainer.store.hiddenFields[dottedKey];

            // Prevent infinite loops
            if (currentValue && currentValue.hidden === hidden && currentValue.omitValue === omitValue) {
                return;
            }

            this.publishContainer.store.setHiddenField({
                dottedKey,
                hidden,
                omitValue,
            });
        },

        shouldForceHiddenField(dottedFieldPath) {
            return data_get(this.publishContainer.store.hiddenFields[dottedFieldPath], 'hidden') === 'force';
        },
    },
};
