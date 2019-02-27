define(['immutable'], function (Immutable) {

  'use strict';

  // TODO: once
  // TODO: unit test
  // Good example of a complete(?) implementation: https://github.com/Wolfy87/EventEmitter
  function EventEmitter() {
    this._listeners = {};
  }

  EventEmitter.prototype.on = function (eventName, fn) {
    var listeners = this._listeners[eventName] || Immutable.Set();

    this._listeners[eventName] = listeners.add(fn);
  };

  EventEmitter.prototype.off = function (eventName, fn) {
    var listeners = this._listeners[eventName] || Immutable.Set();
    if (fn) {
      this._listeners[eventName] = listeners.delete(fn);
    } else {
      this._listeners[eventName] = listeners.clear();
    }
  };

  EventEmitter.prototype.trigger = function (eventName, args) {

    //fire events like my:custom:event -> my:custom -> my
    var events = eventName.split(':');
    while(!!events.length){
      var currentEvent = events.join(':');
      var listeners = this._listeners[currentEvent] || Immutable.Set();
      //trigger handles
      listeners.forEach(function (listener) {
        listener.apply(null, args);
      });
      events.splice((events.length - 1), 1);
    }
  };

  return EventEmitter;

});
