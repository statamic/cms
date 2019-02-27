define([
  'immutable'
], function (Immutable) {
  'use strict';

  function UndoManager(limit, undoScopeHost) {
    this._stack = Immutable.List();
    this._limit = limit;
    this._fireEvent = typeof CustomEvent != 'undefined' && undoScopeHost && undoScopeHost.dispatchEvent;
    this._ush = undoScopeHost;

    this.position = 0;
    this.length = 0;
  }

  UndoManager.prototype.transact = function (transaction, merge) {
    if (arguments.length < 2) {
      throw new TypeError('Not enough arguments to UndoManager.transact.');
    }

    transaction.execute();

    if (this.position > 0) {
      this.clearRedo();
    }

    var transactions;
    if (merge && this.length) {
      transactions = this._stack.first().push(transaction);
      this._stack = this._stack.shift().unshift(transactions);
    }
    else {
      transactions = Immutable.List.of(transaction);
      this._stack = this._stack.unshift(transactions);
      this.length++;

      if (this._limit && this.length > this._limit) {
        this.clearUndo(this._limit);
      }
    }

    this._dispatch('DOMTransaction', transactions);
  };

  UndoManager.prototype.undo = function () {
    if (this.position >= this.length) { return; }

    var transactions = this._stack.get(this.position);
    var i = transactions.size;
    while (i--) {
      transactions.get(i).undo();
    }
    this.position++;

    this._dispatch('undo', transactions);
  };

  UndoManager.prototype.redo = function () {
    if (this.position === 0) { return; }

    this.position--;
    var transactions = this._stack.get(this.position);
    for (var i = 0; i < transactions.size; i++) {
      transactions.get(i).redo();
    }

    this._dispatch('redo', transactions);
  };

  UndoManager.prototype.item = function (index) {
    return index >= 0 && index < this.length ?
      this._stack.get(index).toArray() :
      null;
  };

  UndoManager.prototype.clearUndo = function (position) {
    this._stack = this._stack.take(position !== undefined ? position : this.position);
    this.length = this._stack.size;
  };

  UndoManager.prototype.clearRedo = function () {
    this._stack = this._stack.skip(this.position);
    this.length = this._stack.size;
    this.position = 0;
  };

  UndoManager.prototype._dispatch = function(event, transactions) {
    if (this._fireEvent) {
      this._ush.dispatchEvent(new CustomEvent(event, {
        detail: {transactions: transactions.toArray()},
        bubbles: true,
        cancelable: false
      }));
    }
  }

  return UndoManager;
});
