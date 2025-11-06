// Mock Inertia.js for Storybook
import { h } from 'vue';

export const Link = {
  name: 'InertiaLink',
  props: {
    href: String,
    as: String,
    target: String,
  },
  setup(props, { slots }) {
    return () => h(
      'a',
      {
        href: props.href,
        target: props.target,
      },
      slots.default?.()
    );
  },
};

export const router = {
  visit: () => {},
  get: () => {},
  post: () => {},
  put: () => {},
  patch: () => {},
  delete: () => {},
  reload: () => {},
};

export const usePage = () => ({
  props: {},
  url: '/',
  component: '',
  version: '',
});


