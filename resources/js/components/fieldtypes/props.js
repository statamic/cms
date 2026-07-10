
export default {
    value: {
        required: true,
    },
    config: {
        type: Object,
        default: () => {
            return {};
        },
    },
    handle: {
        type: String,
        required: true,
    },
    meta: {
        type: Object,
        default: () => {
            return {};
        },
    },
    readOnly: {
        type: Boolean,
        default: false,
    },
    showFieldPreviews: {
        type: Boolean,
        default: false,
    },
    namePrefix: String,
    fieldPathPrefix: String,
    metaPathPrefix: String,
    id: String,
};
