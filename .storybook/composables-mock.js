// Mock composables for Storybook
import { getCurrentInstance } from 'vue';

export function hasComponent(name) {
  const instance = getCurrentInstance();
  if (!instance) return false;

  // Check if component exists in slots
  const slots = instance.slots || {};
  return !!slots[name];
}


