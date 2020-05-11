export const KEYS = [
    'if',
    'if_any',
    'show_when',
    'show_when_any',
    'unless',
    'unless_any',
    'hide_when',
    'hide_when_any'
];

export const OPERATORS = [
    'equals',
    'not',
    'contains',
    'contains_any',
    '===',
    '!==',
    '>',
    '>=',
    '<',
    '<=',
    'custom',
];

export const ALIASES = {
    'is': 'equals',
    '==': 'equals',
    'isnt': 'not',
    '!=': 'not',
    'includes': 'contains',
    'includes_any': 'contains_any',
};
