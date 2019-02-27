define([
  './set-root-p-element',
  './formatters/html/enforce-p-elements',
  './formatters/html/ensure-selectable-containers',
  './inline-elements-mode'
], function (
  setRootPElement,
  enforcePElements,
  ensureSelectableContainers,
  inlineElementsMode
) {
  'use strict';

  return {
    setRootPElement: setRootPElement,
    enforcePElements: enforcePElements,
    ensureSelectableContainers: ensureSelectableContainers,
    inlineElementsMode: inlineElementsMode
  };
});
