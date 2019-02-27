define([
  './commands/indent',
  './commands/insert-list',
  './commands/outdent',
  './commands/redo',
  './commands/subscript',
  './commands/superscript',
  './commands/undo'
], function (
  indent,
  insertList,
  outdent,
  redo,
  subscript,
  superscript,
  undo
) {

  'use strict';

  return {
    indent: indent,
    insertList: insertList,
    outdent: outdent,
    redo: redo,
    subscript: subscript,
    superscript: superscript,
    undo: undo
  };

});
