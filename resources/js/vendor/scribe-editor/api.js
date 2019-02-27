define([
  './api/command-patch',
  './api/command',
  './api/selection',
  './api/simple-command'
], function (
  buildCommandPatch,
  buildCommand,
  buildSelection,
  buildSimpleCommand
) {

  'use strict';

  return function Api(scribe) {
    this.CommandPatch = buildCommandPatch(scribe);
    this.Command = buildCommand(scribe);
    this.Selection = buildSelection(scribe);
    this.SimpleCommand = buildSimpleCommand(this, scribe);
  };
});
