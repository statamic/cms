import { __ } from '@/bootstrap/globals';

export function operatorLabel(operator) {
    const labels = {
        equals: __('Equals'),
        not: __('Does not equal'),
        contains: __('Contains'),
        contains_any: __('Contains Any'),
        custom: __('Custom'),
    };

    return labels[operator] || operator || __('Equals');
}
