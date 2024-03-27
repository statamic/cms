import Alpine from 'alpinejs'

const nearestVm = (el, callback = vm => true) => {
    let node = el;
    while (node) {
        if (node.__vue__) break;
        node = node.parentNode;
    }
    let vm = node.__vue__;
    if (!vm) {
        return;
    }
    const root = vm.$root;
    while (vm !== root) {
        if (callback(vm)) return vm;
        vm = vm.$parent;
    }
};

Alpine.magic('useField', (el) => {
    return () => {
        const vm = nearestVm(el, vm => vm.$options.name.match(/-fieldtype$/));
        if (!vm) {
            return;
        }
        const data = Alpine.reactive({
            value: vm.value,
            fieldPathPrefix: vm.fieldPathPrefix,
            update: vm.update,
            updateDebounced: vm.updateDebounced,
            updateMeta: vm.updateMeta, 
        });
        vm.$watch('value', () => {
            data.value = vm.value;
        });
        vm.$watch('fieldPathPrefix', () => {
            data.fieldPathPrefix = vm.fieldPathPrefix;
        });
        return data;
    }
});

Alpine.magic('useStore', (el, { Alpine }) => {
    return () => {
        const vm = nearestVm(el, vm => vm.$options.name === 'publish-container');
        if (!vm) {
            return;
        }
        const state = vm.$store.state.publish[vm.name] || [];
        const data = Alpine.reactive({
            values: state.values,
            meta: state.meta,
            errors: state.errors,
            setFieldValue: vm.setFieldValue,
            setFieldMeta: vm.setFieldMeta,
        });
        vm.$store.subscribe((mutation) => {
            if (mutation.type === `publish/${vm.name}/setFieldValue`) {
                data.values = { ...state.values };
            }
            if (mutation.type === `publish/${vm.name}/setFieldMeta`) {
                data.meta = { ...state.meta };
            }
            if (mutation.type === `publish/${vm.name}/setErrors`) {
                data.errors = { ...state.errors };
            }
        });
        return data;
    };
});

Alpine.start();