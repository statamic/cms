// Mock functions and modules for Storybook

// Mock translation function
export function __(key) {
  return key;
}

// Mock date object
export const $date = {
  locale: 'en',
};

// Mock Inertia Link component
export const Link = {
  name: 'InertiaLink',
  props: ['href', 'as'],
  template: '<a :href="href"><slot /></a>',
};

// Mock custom elements (web components used in some UI components)
export const mockComponents = {
  'ui-input-group': {
    name: 'UiInputGroup',
    template: '<div class="flex"><slot /></div>',
  },
  'ui-input-group-prepend': {
    name: 'UiInputGroupPrepend',
    template: '<div class="prepend"><slot /></div>',
  },
  'ui-input-group-append': {
    name: 'UiInputGroupAppend',
    template: '<div class="append"><slot /></div>',
  },
  'ui-heading': {
    name: 'UiHeading',
    props: ['text', 'size'],
    template: '<h3><slot>{{ text }}</slot></h3>',
  },
};

