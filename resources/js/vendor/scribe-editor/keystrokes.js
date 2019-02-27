define(function() {

  'use strict';

  function isUndoKeyCombination(event) {
    return !event.shiftKey && (event.metaKey || (event.ctrlKey && !event.altKey)) && event.keyCode === 90;
  }

  function isRedoKeyCombination(event) {
    return event.shiftKey && (event.metaKey || (event.ctrlKey && !event.altKey)) && event.keyCode === 90;
  }

  return {
    isUndoKeyCombination: isUndoKeyCombination,
    isRedoKeyCombination: isRedoKeyCombination
  };
});