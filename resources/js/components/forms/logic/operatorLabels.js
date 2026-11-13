import { __ } from '@/bootstrap/globals';

export function operatorLabel(operator) {
    const labels = {
        equals: __('equals'),
        not: __('does not equal'),
        contains: __('contains'),
        contains_any: __('contains any of'),
        '===': __('equals'),
        '!==': __('does not equal'),
        '>': __('is greater than'),
        '<': __('is less than'),
        '>=': __('is at least'),
        '<=': __('is at most'),
        custom: __('custom'),
    };

    return labels[operator] || operator || __('equals');
}
