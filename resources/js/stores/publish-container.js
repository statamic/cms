import { defineStore } from 'pinia';

export const usePublishContainerStore = function (name, initial) {
    return defineStore(name, {
        state: () => ({
            blueprint: initial.blueprint,
            values: initial.values,
            extraValues: initial.extraValues,
            hiddenFields: {},
            jsonSubmittingFields: [],
            revealerFields: [],
            meta: initial.meta,
            localizedFields: initial.localizedFields,
            site: initial.site,
            fieldLocks: {},
            errors: {},
            isRoot: initial.isRoot,
            preloadedAssets: [],
            autosaveInterval: null,
            reference: initial.reference,
        }),
        actions: {
            setFieldValue(payload) {
                const { handle, value } = payload;
                this.values[handle] = value;
            },
            setValues(values) {
                this.values = values;
            },
            setExtraValues(values) {
                this.extraValues = values;
            },
            setHiddenField(field) {
                this.hiddenFields[field.dottedKey] = {
                    hidden: field.hidden,
                    omitValue: field.omitValue,
                };
            },
            setFieldSubmitsJson(dottedKey) {
                if (this.jsonSubmittingFields.indexOf(dottedKey) === -1) {
                    this.jsonSubmittingFields.push(dottedKey);
                }
            },
            unsetFieldSubmitsJson(dottedKey) {
                const index = this.jsonSubmittingFields.indexOf(dottedKey);
                if (index !== -1) {
                    this.jsonSubmittingFields.splice(index, 1);
                }
            },
            setRevealerField(dottedKey) {
                if (this.revealerFields.indexOf(dottedKey) === -1) {
                    this.revealerFields.push(dottedKey);
                }
            },
            unsetRevealerField(dottedKey) {
                const index = this.revealerFields.indexOf(dottedKey);
                if (index !== -1) {
                    this.revealerFields.splice(index, 1);
                }
            },
            setMeta(meta) {
                this.meta = meta;
            },
            setFieldMeta(payload) {
                const { handle, value } = payload;
                this.meta[handle] = value;
            },
            setIsRoot(isRoot) {
                this.isRoot = isRoot;
            },
            setBlueprint(blueprint) {
                this.blueprint = blueprint;
            },
            setErrors(errors) {
                this.errors = errors;
            },
            setSite(site) {
                this.site = site;
            },
            setLocalizedFields(fields) {
                this.localizedFields = fields;
            },
            lockField({ handle, user }) {
                this.fieldLocks[handle] = user || true;
            },
            unlockField(handle) {
                delete this.fieldLocks[handle];
            },
            initialize(payload) {
                this.blueprint = payload.blueprint;
                this.values = payload.values;
                this.meta = payload.meta;
                this.site = payload.site;
            },
            setPreloadedAssets(assets) {
                this.preloadedAssets = assets;
            },
            setAutosaveInterval(interval) {
                if (this.autosaveInterval) {
                    clearInterval(this.autosaveInterval);
                }
                this.autosaveInterval = interval;
            },
            clearAutosaveInterval() {
                clearInterval(this.autosaveInterval);
            },
        },
    })();
};
