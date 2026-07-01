const __vite__mapDeps=(i,m=__vite__mapDeps,d=(m.f||(m.f=["./pusher-KzJEM2gB.js","./chunk-B-1-B7_t.js","./style-BLzh5t5_.css"])))=>i.map(i=>d[i]);
import { r as __toESM, t as __commonJSMin } from "./chunk-B-1-B7_t.js";
import { F as onBeforeUnmount, L as onMounted, N as nextTick, _t as ref, et as watch, pt as markRaw } from "./vue.esm-bundler-BbHU-fTn.js";
import { G as axios, c as router } from "./index.esm-B4SStoAz.js";
import { t as __vitePreload } from "./preload-helper-CW7Fztz1.js";
import { h as nanoid, i as data_get$1 } from "./globals-CR4DMcIG.js";
//#region resources/js/util/debounce.js
function debounce_default(func, wait, immediate) {
	let timeout;
	const debounced = function() {
		const context = this, args = arguments;
		clearTimeout(timeout);
		if (immediate && !timeout) func.apply(context, args);
		timeout = setTimeout(function() {
			timeout = null;
			if (!immediate) func.apply(context, args);
		}, wait);
	};
	debounced.cancel = function() {
		clearTimeout(timeout);
		timeout = null;
	};
	return debounced;
}
//#endregion
//#region resources/js/components/keys/Binding.js
var import_mousetrap = /* @__PURE__ */ __toESM((/* @__PURE__ */ __commonJSMin(((exports, module) => {
	/**
	* Copyright 2012-2017 Craig Campbell
	*
	* Licensed under the Apache License, Version 2.0 (the "License");
	* you may not use this file except in compliance with the License.
	* You may obtain a copy of the License at
	*
	* http://www.apache.org/licenses/LICENSE-2.0
	*
	* Unless required by applicable law or agreed to in writing, software
	* distributed under the License is distributed on an "AS IS" BASIS,
	* WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
	* See the License for the specific language governing permissions and
	* limitations under the License.
	*
	* Mousetrap is a simple keyboard shortcut library for Javascript with
	* no external dependencies
	*
	* @version 1.6.5
	* @url craig.is/killing/mice
	*/
	(function(window, document, undefined) {
		if (!window) return;
		/**
		* mapping of special keycodes to their corresponding keys
		*
		* everything in this dictionary cannot use keypress events
		* so it has to be here to map to the correct keycodes for
		* keyup/keydown events
		*
		* @type {Object}
		*/
		var _MAP = {
			8: "backspace",
			9: "tab",
			13: "enter",
			16: "shift",
			17: "ctrl",
			18: "alt",
			20: "capslock",
			27: "esc",
			32: "space",
			33: "pageup",
			34: "pagedown",
			35: "end",
			36: "home",
			37: "left",
			38: "up",
			39: "right",
			40: "down",
			45: "ins",
			46: "del",
			91: "meta",
			93: "meta",
			224: "meta"
		};
		/**
		* mapping for special characters so they can support
		*
		* this dictionary is only used incase you want to bind a
		* keyup or keydown event to one of these keys
		*
		* @type {Object}
		*/
		var _KEYCODE_MAP = {
			106: "*",
			107: "+",
			109: "-",
			110: ".",
			111: "/",
			186: ";",
			187: "=",
			188: ",",
			189: "-",
			190: ".",
			191: "/",
			192: "`",
			219: "[",
			220: "\\",
			221: "]",
			222: "'"
		};
		/**
		* this is a mapping of keys that require shift on a US keypad
		* back to the non shift equivelents
		*
		* this is so you can use keyup events with these keys
		*
		* note that this will only work reliably on US keyboards
		*
		* @type {Object}
		*/
		var _SHIFT_MAP = {
			"~": "`",
			"!": "1",
			"@": "2",
			"#": "3",
			"$": "4",
			"%": "5",
			"^": "6",
			"&": "7",
			"*": "8",
			"(": "9",
			")": "0",
			"_": "-",
			"+": "=",
			":": ";",
			"\"": "'",
			"<": ",",
			">": ".",
			"?": "/",
			"|": "\\"
		};
		/**
		* this is a list of special strings you can use to map
		* to modifier keys when you specify your keyboard shortcuts
		*
		* @type {Object}
		*/
		var _SPECIAL_ALIASES = {
			"option": "alt",
			"command": "meta",
			"return": "enter",
			"escape": "esc",
			"plus": "+",
			"mod": /Mac|iPod|iPhone|iPad/.test(navigator.platform) ? "meta" : "ctrl"
		};
		/**
		* variable to store the flipped version of _MAP from above
		* needed to check if we should use keypress or not when no action
		* is specified
		*
		* @type {Object|undefined}
		*/
		var _REVERSE_MAP;
		/**
		* loop through the f keys, f1 to f19 and add them to the map
		* programatically
		*/
		for (var i = 1; i < 20; ++i) _MAP[111 + i] = "f" + i;
		/**
		* loop through to map numbers on the numeric keypad
		*/
		for (i = 0; i <= 9; ++i) _MAP[i + 96] = i.toString();
		/**
		* cross browser add event method
		*
		* @param {Element|HTMLDocument} object
		* @param {string} type
		* @param {Function} callback
		* @returns void
		*/
		function _addEvent(object, type, callback) {
			if (object.addEventListener) {
				object.addEventListener(type, callback, false);
				return;
			}
			object.attachEvent("on" + type, callback);
		}
		/**
		* takes the event and returns the key character
		*
		* @param {Event} e
		* @return {string}
		*/
		function _characterFromEvent(e) {
			if (e.type == "keypress") {
				var character = String.fromCharCode(e.which);
				if (!e.shiftKey) character = character.toLowerCase();
				return character;
			}
			if (_MAP[e.which]) return _MAP[e.which];
			if (_KEYCODE_MAP[e.which]) return _KEYCODE_MAP[e.which];
			return String.fromCharCode(e.which).toLowerCase();
		}
		/**
		* checks if two arrays are equal
		*
		* @param {Array} modifiers1
		* @param {Array} modifiers2
		* @returns {boolean}
		*/
		function _modifiersMatch(modifiers1, modifiers2) {
			return modifiers1.sort().join(",") === modifiers2.sort().join(",");
		}
		/**
		* takes a key event and figures out what the modifiers are
		*
		* @param {Event} e
		* @returns {Array}
		*/
		function _eventModifiers(e) {
			var modifiers = [];
			if (e.shiftKey) modifiers.push("shift");
			if (e.altKey) modifiers.push("alt");
			if (e.ctrlKey) modifiers.push("ctrl");
			if (e.metaKey) modifiers.push("meta");
			return modifiers;
		}
		/**
		* prevents default for this event
		*
		* @param {Event} e
		* @returns void
		*/
		function _preventDefault(e) {
			if (e.preventDefault) {
				e.preventDefault();
				return;
			}
			e.returnValue = false;
		}
		/**
		* stops propogation for this event
		*
		* @param {Event} e
		* @returns void
		*/
		function _stopPropagation(e) {
			if (e.stopPropagation) {
				e.stopPropagation();
				return;
			}
			e.cancelBubble = true;
		}
		/**
		* determines if the keycode specified is a modifier key or not
		*
		* @param {string} key
		* @returns {boolean}
		*/
		function _isModifier(key) {
			return key == "shift" || key == "ctrl" || key == "alt" || key == "meta";
		}
		/**
		* reverses the map lookup so that we can look for specific keys
		* to see what can and can't use keypress
		*
		* @return {Object}
		*/
		function _getReverseMap() {
			if (!_REVERSE_MAP) {
				_REVERSE_MAP = {};
				for (var key in _MAP) {
					if (key > 95 && key < 112) continue;
					if (_MAP.hasOwnProperty(key)) _REVERSE_MAP[_MAP[key]] = key;
				}
			}
			return _REVERSE_MAP;
		}
		/**
		* picks the best action based on the key combination
		*
		* @param {string} key - character for key
		* @param {Array} modifiers
		* @param {string=} action passed in
		*/
		function _pickBestAction(key, modifiers, action) {
			if (!action) action = _getReverseMap()[key] ? "keydown" : "keypress";
			if (action == "keypress" && modifiers.length) action = "keydown";
			return action;
		}
		/**
		* Converts from a string key combination to an array
		*
		* @param  {string} combination like "command+shift+l"
		* @return {Array}
		*/
		function _keysFromString(combination) {
			if (combination === "+") return ["+"];
			combination = combination.replace(/\+{2}/g, "+plus");
			return combination.split("+");
		}
		/**
		* Gets info for a specific key combination
		*
		* @param  {string} combination key combination ("command+s" or "a" or "*")
		* @param  {string=} action
		* @returns {Object}
		*/
		function _getKeyInfo(combination, action) {
			var keys;
			var key;
			var i;
			var modifiers = [];
			keys = _keysFromString(combination);
			for (i = 0; i < keys.length; ++i) {
				key = keys[i];
				if (_SPECIAL_ALIASES[key]) key = _SPECIAL_ALIASES[key];
				if (action && action != "keypress" && _SHIFT_MAP[key]) {
					key = _SHIFT_MAP[key];
					modifiers.push("shift");
				}
				if (_isModifier(key)) modifiers.push(key);
			}
			action = _pickBestAction(key, modifiers, action);
			return {
				key,
				modifiers,
				action
			};
		}
		function _belongsTo(element, ancestor) {
			if (element === null || element === document) return false;
			if (element === ancestor) return true;
			return _belongsTo(element.parentNode, ancestor);
		}
		function Mousetrap(targetElement) {
			var self = this;
			targetElement = targetElement || document;
			if (!(self instanceof Mousetrap)) return new Mousetrap(targetElement);
			/**
			* element to attach key events to
			*
			* @type {Element}
			*/
			self.target = targetElement;
			/**
			* a list of all the callbacks setup via Mousetrap.bind()
			*
			* @type {Object}
			*/
			self._callbacks = {};
			/**
			* direct map of string combinations to callbacks used for trigger()
			*
			* @type {Object}
			*/
			self._directMap = {};
			/**
			* keeps track of what level each sequence is at since multiple
			* sequences can start out with the same sequence
			*
			* @type {Object}
			*/
			var _sequenceLevels = {};
			/**
			* variable to store the setTimeout call
			*
			* @type {null|number}
			*/
			var _resetTimer;
			/**
			* temporary state where we will ignore the next keyup
			*
			* @type {boolean|string}
			*/
			var _ignoreNextKeyup = false;
			/**
			* temporary state where we will ignore the next keypress
			*
			* @type {boolean}
			*/
			var _ignoreNextKeypress = false;
			/**
			* are we currently inside of a sequence?
			* type of action ("keyup" or "keydown" or "keypress") or false
			*
			* @type {boolean|string}
			*/
			var _nextExpectedAction = false;
			/**
			* resets all sequence counters except for the ones passed in
			*
			* @param {Object} doNotReset
			* @returns void
			*/
			function _resetSequences(doNotReset) {
				doNotReset = doNotReset || {};
				var activeSequences = false, key;
				for (key in _sequenceLevels) {
					if (doNotReset[key]) {
						activeSequences = true;
						continue;
					}
					_sequenceLevels[key] = 0;
				}
				if (!activeSequences) _nextExpectedAction = false;
			}
			/**
			* finds all callbacks that match based on the keycode, modifiers,
			* and action
			*
			* @param {string} character
			* @param {Array} modifiers
			* @param {Event|Object} e
			* @param {string=} sequenceName - name of the sequence we are looking for
			* @param {string=} combination
			* @param {number=} level
			* @returns {Array}
			*/
			function _getMatches(character, modifiers, e, sequenceName, combination, level) {
				var i;
				var callback;
				var matches = [];
				var action = e.type;
				if (!self._callbacks[character]) return [];
				if (action == "keyup" && _isModifier(character)) modifiers = [character];
				for (i = 0; i < self._callbacks[character].length; ++i) {
					callback = self._callbacks[character][i];
					if (!sequenceName && callback.seq && _sequenceLevels[callback.seq] != callback.level) continue;
					if (action != callback.action) continue;
					if (action == "keypress" && !e.metaKey && !e.ctrlKey || _modifiersMatch(modifiers, callback.modifiers)) {
						var deleteCombo = !sequenceName && callback.combo == combination;
						var deleteSequence = sequenceName && callback.seq == sequenceName && callback.level == level;
						if (deleteCombo || deleteSequence) self._callbacks[character].splice(i, 1);
						matches.push(callback);
					}
				}
				return matches;
			}
			/**
			* actually calls the callback function
			*
			* if your callback function returns false this will use the jquery
			* convention - prevent default and stop propogation on the event
			*
			* @param {Function} callback
			* @param {Event} e
			* @returns void
			*/
			function _fireCallback(callback, e, combo, sequence) {
				if (self.stopCallback(e, e.target || e.srcElement, combo, sequence)) return;
				if (callback(e, combo) === false) {
					_preventDefault(e);
					_stopPropagation(e);
				}
			}
			/**
			* handles a character key event
			*
			* @param {string} character
			* @param {Array} modifiers
			* @param {Event} e
			* @returns void
			*/
			self._handleKey = function(character, modifiers, e) {
				var callbacks = _getMatches(character, modifiers, e);
				var i;
				var doNotReset = {};
				var maxLevel = 0;
				var processedSequenceCallback = false;
				for (i = 0; i < callbacks.length; ++i) if (callbacks[i].seq) maxLevel = Math.max(maxLevel, callbacks[i].level);
				for (i = 0; i < callbacks.length; ++i) {
					if (callbacks[i].seq) {
						if (callbacks[i].level != maxLevel) continue;
						processedSequenceCallback = true;
						doNotReset[callbacks[i].seq] = 1;
						_fireCallback(callbacks[i].callback, e, callbacks[i].combo, callbacks[i].seq);
						continue;
					}
					if (!processedSequenceCallback) _fireCallback(callbacks[i].callback, e, callbacks[i].combo);
				}
				var ignoreThisKeypress = e.type == "keypress" && _ignoreNextKeypress;
				if (e.type == _nextExpectedAction && !_isModifier(character) && !ignoreThisKeypress) _resetSequences(doNotReset);
				_ignoreNextKeypress = processedSequenceCallback && e.type == "keydown";
			};
			/**
			* handles a keydown event
			*
			* @param {Event} e
			* @returns void
			*/
			function _handleKeyEvent(e) {
				if (typeof e.which !== "number") e.which = e.keyCode;
				var character = _characterFromEvent(e);
				if (!character) return;
				if (e.type == "keyup" && _ignoreNextKeyup === character) {
					_ignoreNextKeyup = false;
					return;
				}
				self.handleKey(character, _eventModifiers(e), e);
			}
			/**
			* called to set a 1 second timeout on the specified sequence
			*
			* this is so after each key press in the sequence you have 1 second
			* to press the next key before you have to start over
			*
			* @returns void
			*/
			function _resetSequenceTimer() {
				clearTimeout(_resetTimer);
				_resetTimer = setTimeout(_resetSequences, 1e3);
			}
			/**
			* binds a key sequence to an event
			*
			* @param {string} combo - combo specified in bind call
			* @param {Array} keys
			* @param {Function} callback
			* @param {string=} action
			* @returns void
			*/
			function _bindSequence(combo, keys, callback, action) {
				_sequenceLevels[combo] = 0;
				/**
				* callback to increase the sequence level for this sequence and reset
				* all other sequences that were active
				*
				* @param {string} nextAction
				* @returns {Function}
				*/
				function _increaseSequence(nextAction) {
					return function() {
						_nextExpectedAction = nextAction;
						++_sequenceLevels[combo];
						_resetSequenceTimer();
					};
				}
				/**
				* wraps the specified callback inside of another function in order
				* to reset all sequence counters as soon as this sequence is done
				*
				* @param {Event} e
				* @returns void
				*/
				function _callbackAndReset(e) {
					_fireCallback(callback, e, combo);
					if (action !== "keyup") _ignoreNextKeyup = _characterFromEvent(e);
					setTimeout(_resetSequences, 10);
				}
				for (var i = 0; i < keys.length; ++i) {
					var wrappedCallback = i + 1 === keys.length ? _callbackAndReset : _increaseSequence(action || _getKeyInfo(keys[i + 1]).action);
					_bindSingle(keys[i], wrappedCallback, action, combo, i);
				}
			}
			/**
			* binds a single keyboard combination
			*
			* @param {string} combination
			* @param {Function} callback
			* @param {string=} action
			* @param {string=} sequenceName - name of sequence if part of sequence
			* @param {number=} level - what part of the sequence the command is
			* @returns void
			*/
			function _bindSingle(combination, callback, action, sequenceName, level) {
				self._directMap[combination + ":" + action] = callback;
				combination = combination.replace(/\s+/g, " ");
				var sequence = combination.split(" ");
				var info;
				if (sequence.length > 1) {
					_bindSequence(combination, sequence, callback, action);
					return;
				}
				info = _getKeyInfo(combination, action);
				self._callbacks[info.key] = self._callbacks[info.key] || [];
				_getMatches(info.key, info.modifiers, { type: info.action }, sequenceName, combination, level);
				self._callbacks[info.key][sequenceName ? "unshift" : "push"]({
					callback,
					modifiers: info.modifiers,
					action: info.action,
					seq: sequenceName,
					level,
					combo: combination
				});
			}
			/**
			* binds multiple combinations to the same callback
			*
			* @param {Array} combinations
			* @param {Function} callback
			* @param {string|undefined} action
			* @returns void
			*/
			self._bindMultiple = function(combinations, callback, action) {
				for (var i = 0; i < combinations.length; ++i) _bindSingle(combinations[i], callback, action);
			};
			_addEvent(targetElement, "keypress", _handleKeyEvent);
			_addEvent(targetElement, "keydown", _handleKeyEvent);
			_addEvent(targetElement, "keyup", _handleKeyEvent);
		}
		/**
		* binds an event to mousetrap
		*
		* can be a single key, a combination of keys separated with +,
		* an array of keys, or a sequence of keys separated by spaces
		*
		* be sure to list the modifier keys first to make sure that the
		* correct key ends up getting bound (the last key in the pattern)
		*
		* @param {string|Array} keys
		* @param {Function} callback
		* @param {string=} action - 'keypress', 'keydown', or 'keyup'
		* @returns void
		*/
		Mousetrap.prototype.bind = function(keys, callback, action) {
			var self = this;
			keys = keys instanceof Array ? keys : [keys];
			self._bindMultiple.call(self, keys, callback, action);
			return self;
		};
		/**
		* unbinds an event to mousetrap
		*
		* the unbinding sets the callback function of the specified key combo
		* to an empty function and deletes the corresponding key in the
		* _directMap dict.
		*
		* TODO: actually remove this from the _callbacks dictionary instead
		* of binding an empty function
		*
		* the keycombo+action has to be exactly the same as
		* it was defined in the bind method
		*
		* @param {string|Array} keys
		* @param {string} action
		* @returns void
		*/
		Mousetrap.prototype.unbind = function(keys, action) {
			var self = this;
			return self.bind.call(self, keys, function() {}, action);
		};
		/**
		* triggers an event that has already been bound
		*
		* @param {string} keys
		* @param {string=} action
		* @returns void
		*/
		Mousetrap.prototype.trigger = function(keys, action) {
			var self = this;
			if (self._directMap[keys + ":" + action]) self._directMap[keys + ":" + action]({}, keys);
			return self;
		};
		/**
		* resets the library back to its initial state.  this is useful
		* if you want to clear out the current keyboard shortcuts and bind
		* new ones - for example if you switch to another page
		*
		* @returns void
		*/
		Mousetrap.prototype.reset = function() {
			var self = this;
			self._callbacks = {};
			self._directMap = {};
			return self;
		};
		/**
		* should we stop this event before firing off callbacks
		*
		* @param {Event} e
		* @param {Element} element
		* @return {boolean}
		*/
		Mousetrap.prototype.stopCallback = function(e, element) {
			var self = this;
			if ((" " + element.className + " ").indexOf(" mousetrap ") > -1) return false;
			if (_belongsTo(element, self.target)) return false;
			if ("composedPath" in e && typeof e.composedPath === "function") {
				var initialEventTarget = e.composedPath()[0];
				if (initialEventTarget !== e.target) element = initialEventTarget;
			}
			return element.tagName == "INPUT" || element.tagName == "SELECT" || element.tagName == "TEXTAREA" || element.isContentEditable;
		};
		/**
		* exposes _handleKey publicly so it can be overwritten by extensions
		*/
		Mousetrap.prototype.handleKey = function() {
			var self = this;
			return self._handleKey.apply(self, arguments);
		};
		/**
		* allow custom key mappings
		*/
		Mousetrap.addKeycodes = function(object) {
			for (var key in object) if (object.hasOwnProperty(key)) _MAP[key] = object[key];
			_REVERSE_MAP = null;
		};
		/**
		* Init the global mousetrap functions
		*
		* This method is needed to allow the global mousetrap functions to work
		* now that mousetrap is a constructor function.
		*/
		Mousetrap.init = function() {
			var documentMousetrap = Mousetrap(document);
			for (var method in documentMousetrap) if (method.charAt(0) !== "_") Mousetrap[method] = function(method) {
				return function() {
					return documentMousetrap[method].apply(documentMousetrap, arguments);
				};
			}(method);
		};
		Mousetrap.init();
		window.Mousetrap = Mousetrap;
		if (typeof module !== "undefined" && module.exports) module.exports = Mousetrap;
		if (typeof define === "function" && define.amd) define(function() {
			return Mousetrap;
		});
	})(typeof window !== "undefined" ? window : null, typeof window !== "undefined" ? document : null);
})))(), 1);
var Binding = class {
	constructor(bindings) {
		this.bindings = bindings;
	}
	bind(bindings, callback) {
		if (typeof bindings === "string") bindings = [bindings];
		bindings.forEach((binding) => {
			this.bindings[binding] = this.bindings[binding] || [];
			this.bindings[binding].push(callback);
			this.bindMousetrap(binding, callback);
		});
		this.bound = bindings;
		return this;
	}
	destroy() {
		this.bound.forEach((binding) => {
			this.bindings[binding].pop();
			const previous = this.bindings[binding].slice(-1)[0];
			previous ? import_mousetrap.default.bind(binding, previous) : import_mousetrap.default.unbind(binding);
		});
	}
	stop(callback) {
		import_mousetrap.default.prototype.stopCallback = callback;
	}
	bindMousetrap(binding, callback) {
		import_mousetrap.default.bind(binding, callback);
	}
};
//#endregion
//#region node_modules/mousetrap/plugins/global-bind/mousetrap-global-bind.js
/**
* adds a bindGlobal method to Mousetrap that allows you to
* bind specific keyboard shortcuts that will still work
* inside a text input field
*
* usage:
* Mousetrap.bindGlobal('ctrl+s', _saveChanges);
*/
(function(Mousetrap) {
	if (!Mousetrap) return;
	var _globalCallbacks = {};
	var _originalStopCallback = Mousetrap.prototype.stopCallback;
	Mousetrap.prototype.stopCallback = function(e, element, combo, sequence) {
		var self = this;
		if (self.paused) return true;
		if (_globalCallbacks[combo] || _globalCallbacks[sequence]) return false;
		return _originalStopCallback.call(self, e, element, combo);
	};
	Mousetrap.prototype.bindGlobal = function(keys, callback, action) {
		this.bind(keys, callback, action);
		if (keys instanceof Array) {
			for (var i = 0; i < keys.length; i++) _globalCallbacks[keys[i]] = true;
			return;
		}
		_globalCallbacks[keys] = true;
	};
	Mousetrap.init();
})(typeof Mousetrap !== "undefined" ? Mousetrap : void 0);
//#endregion
//#region resources/js/components/keys/GlobalBinding.js
var GlobalBinding = class extends Binding {
	bindMousetrap(binding, callback) {
		import_mousetrap.default.bindGlobal(binding, callback);
	}
};
//#endregion
//#region resources/js/components/keys/Keys.js
var Keys = class {
	constructor() {
		this.bindings = {};
		this.globals = {};
	}
	bind(bindings, callback) {
		return new Binding(this.bindings).bind(bindings, callback);
	}
	stop(callback) {
		return new Binding(this.bindings).stop(callback);
	}
	bindGlobal(bindings, callback) {
		return new GlobalBinding(this.globals).bind(bindings, callback);
	}
};
//#endregion
//#region resources/js/components/Component.js
var Component = class {
	constructor(id, name, props) {
		this.id = id;
		this.name = name;
		this.props = props;
		this.events = {};
	}
	prop(prop, value) {
		this.props[prop] = value;
	}
	on(event, handler) {
		this.events[event] = handler;
	}
	destroy() {
		Statamic.$components.destroy(this.id);
	}
};
//#endregion
//#region resources/js/components/Components.js
var Components = class {
	#booted = false;
	constructor() {
		this.queue = {};
		this.components = ref([]);
	}
	boot(app) {
		if (this.#booted) return;
		this.app = app;
		Object.entries(this.queue).forEach(([name, component]) => {
			this.app.component(name, component);
		});
		this.#booted = true;
	}
	register(name, component) {
		if (this.#booted) {
			this.app.component(name, component);
			return;
		}
		this.queue[name] = component;
	}
	append(name, { props }) {
		const component = new Component(`appended-${nanoid()}`, name, props);
		this.components.value.push(component);
		return component;
	}
	get(id) {
		let appended = this.getAppended(id);
		if (appended) return appended;
	}
	has(name) {
		return this.app.component(name) !== void 0;
	}
	getAppended(id) {
		return this.components.value.find((c) => c.id === id);
	}
	destroy(id) {
		let appended = this.getAppended(id);
		if (appended) {
			const index = this.components.value.indexOf(appended);
			this.components.value.splice(index, 1);
		}
	}
};
//#endregion
//#region node_modules/tiny-emitter/index.js
var require_tiny_emitter = /* @__PURE__ */ __commonJSMin(((exports, module) => {
	function E() {}
	E.prototype = {
		on: function(name, callback, ctx) {
			var e = this.e || (this.e = {});
			(e[name] || (e[name] = [])).push({
				fn: callback,
				ctx
			});
			return this;
		},
		once: function(name, callback, ctx) {
			var self = this;
			function listener() {
				self.off(name, listener);
				callback.apply(ctx, arguments);
			}
			listener._ = callback;
			return this.on(name, listener, ctx);
		},
		emit: function(name) {
			var data = [].slice.call(arguments, 1);
			var evtArr = ((this.e || (this.e = {}))[name] || []).slice();
			var i = 0;
			var len = evtArr.length;
			for (; i < len; i++) evtArr[i].fn.apply(evtArr[i].ctx, data);
			return this;
		},
		off: function(name, callback) {
			var e = this.e || (this.e = {});
			var evts = e[name];
			var liveEvents = [];
			if (evts && callback) {
				for (var i = 0, len = evts.length; i < len; i++) if (evts[i].fn !== callback && evts[i].fn._ !== callback) liveEvents.push(evts[i]);
			}
			liveEvents.length ? e[name] = liveEvents : delete e[name];
			return this;
		}
	};
	module.exports = E;
	module.exports.TinyEmitter = E;
}));
//#endregion
//#region resources/js/composables/global-event-bus.js
var import_instance = /* @__PURE__ */ __toESM((/* @__PURE__ */ __commonJSMin(((exports, module) => {
	module.exports = new (require_tiny_emitter())();
})))(), 1);
function useGlobalEventBus() {
	return {
		$on: (...args) => import_instance.default.on(...args),
		$once: (...args) => import_instance.default.once(...args),
		$off: (...args) => import_instance.default.off(...args),
		$emit: (...args) => import_instance.default.emit(...args)
	};
}
/* NProgress, (c) 2013, 2014 Rico Sta. Cruz - http://ricostacruz.com/nprogress
* @license MIT */
//#endregion
//#region resources/js/composables/progress-bar.js
var import_nprogress = /* @__PURE__ */ __toESM((/* @__PURE__ */ __commonJSMin(((exports, module) => {
	(function(root, factory) {
		if (typeof define === "function" && define.amd) define(factory);
		else if (typeof exports === "object") module.exports = factory();
		else root.NProgress = factory();
	})(exports, function() {
		var NProgress = {};
		NProgress.version = "0.2.0";
		var Settings = NProgress.settings = {
			minimum: .08,
			easing: "ease",
			positionUsing: "",
			speed: 200,
			trickle: true,
			trickleRate: .02,
			trickleSpeed: 800,
			showSpinner: true,
			barSelector: "[role=\"bar\"]",
			spinnerSelector: "[role=\"spinner\"]",
			parent: "body",
			template: "<div class=\"bar\" role=\"bar\"><div class=\"peg\"></div></div><div class=\"spinner\" role=\"spinner\"><div class=\"spinner-icon\"></div></div>"
		};
		/**
		* Updates configuration.
		*
		*     NProgress.configure({
		*       minimum: 0.1
		*     });
		*/
		NProgress.configure = function(options) {
			var key, value;
			for (key in options) {
				value = options[key];
				if (value !== void 0 && options.hasOwnProperty(key)) Settings[key] = value;
			}
			return this;
		};
		/**
		* Last number.
		*/
		NProgress.status = null;
		/**
		* Sets the progress bar status, where `n` is a number from `0.0` to `1.0`.
		*
		*     NProgress.set(0.4);
		*     NProgress.set(1.0);
		*/
		NProgress.set = function(n) {
			var started = NProgress.isStarted();
			n = clamp(n, Settings.minimum, 1);
			NProgress.status = n === 1 ? null : n;
			var progress = NProgress.render(!started), bar = progress.querySelector(Settings.barSelector), speed = Settings.speed, ease = Settings.easing;
			progress.offsetWidth;
			queue(function(next) {
				if (Settings.positionUsing === "") Settings.positionUsing = NProgress.getPositioningCSS();
				css(bar, barPositionCSS(n, speed, ease));
				if (n === 1) {
					css(progress, {
						transition: "none",
						opacity: 1
					});
					progress.offsetWidth;
					setTimeout(function() {
						css(progress, {
							transition: "all " + speed + "ms linear",
							opacity: 0
						});
						setTimeout(function() {
							NProgress.remove();
							next();
						}, speed);
					}, speed);
				} else setTimeout(next, speed);
			});
			return this;
		};
		NProgress.isStarted = function() {
			return typeof NProgress.status === "number";
		};
		/**
		* Shows the progress bar.
		* This is the same as setting the status to 0%, except that it doesn't go backwards.
		*
		*     NProgress.start();
		*
		*/
		NProgress.start = function() {
			if (!NProgress.status) NProgress.set(0);
			var work = function() {
				setTimeout(function() {
					if (!NProgress.status) return;
					NProgress.trickle();
					work();
				}, Settings.trickleSpeed);
			};
			if (Settings.trickle) work();
			return this;
		};
		/**
		* Hides the progress bar.
		* This is the *sort of* the same as setting the status to 100%, with the
		* difference being `done()` makes some placebo effect of some realistic motion.
		*
		*     NProgress.done();
		*
		* If `true` is passed, it will show the progress bar even if its hidden.
		*
		*     NProgress.done(true);
		*/
		NProgress.done = function(force) {
			if (!force && !NProgress.status) return this;
			return NProgress.inc(.3 + .5 * Math.random()).set(1);
		};
		/**
		* Increments by a random amount.
		*/
		NProgress.inc = function(amount) {
			var n = NProgress.status;
			if (!n) return NProgress.start();
			else {
				if (typeof amount !== "number") amount = (1 - n) * clamp(Math.random() * n, .1, .95);
				n = clamp(n + amount, 0, .994);
				return NProgress.set(n);
			}
		};
		NProgress.trickle = function() {
			return NProgress.inc(Math.random() * Settings.trickleRate);
		};
		/**
		* Waits for all supplied jQuery promises and
		* increases the progress as the promises resolve.
		*
		* @param $promise jQUery Promise
		*/
		(function() {
			var initial = 0, current = 0;
			NProgress.promise = function($promise) {
				if (!$promise || $promise.state() === "resolved") return this;
				if (current === 0) NProgress.start();
				initial++;
				current++;
				$promise.always(function() {
					current--;
					if (current === 0) {
						initial = 0;
						NProgress.done();
					} else NProgress.set((initial - current) / initial);
				});
				return this;
			};
		})();
		/**
		* (Internal) renders the progress bar markup based on the `template`
		* setting.
		*/
		NProgress.render = function(fromStart) {
			if (NProgress.isRendered()) return document.getElementById("nprogress");
			addClass(document.documentElement, "nprogress-busy");
			var progress = document.createElement("div");
			progress.id = "nprogress";
			progress.innerHTML = Settings.template;
			var bar = progress.querySelector(Settings.barSelector), perc = fromStart ? "-100" : toBarPerc(NProgress.status || 0), parent = document.querySelector(Settings.parent), spinner;
			css(bar, {
				transition: "all 0 linear",
				transform: "translate3d(" + perc + "%,0,0)"
			});
			if (!Settings.showSpinner) {
				spinner = progress.querySelector(Settings.spinnerSelector);
				spinner && removeElement(spinner);
			}
			if (parent != document.body) addClass(parent, "nprogress-custom-parent");
			parent.appendChild(progress);
			return progress;
		};
		/**
		* Removes the element. Opposite of render().
		*/
		NProgress.remove = function() {
			removeClass(document.documentElement, "nprogress-busy");
			removeClass(document.querySelector(Settings.parent), "nprogress-custom-parent");
			var progress = document.getElementById("nprogress");
			progress && removeElement(progress);
		};
		/**
		* Checks if the progress bar is rendered.
		*/
		NProgress.isRendered = function() {
			return !!document.getElementById("nprogress");
		};
		/**
		* Determine which positioning CSS rule to use.
		*/
		NProgress.getPositioningCSS = function() {
			var bodyStyle = document.body.style;
			var vendorPrefix = "WebkitTransform" in bodyStyle ? "Webkit" : "MozTransform" in bodyStyle ? "Moz" : "msTransform" in bodyStyle ? "ms" : "OTransform" in bodyStyle ? "O" : "";
			if (vendorPrefix + "Perspective" in bodyStyle) return "translate3d";
			else if (vendorPrefix + "Transform" in bodyStyle) return "translate";
			else return "margin";
		};
		/**
		* Helpers
		*/
		function clamp(n, min, max) {
			if (n < min) return min;
			if (n > max) return max;
			return n;
		}
		/**
		* (Internal) converts a percentage (`0..1`) to a bar translateX
		* percentage (`-100%..0%`).
		*/
		function toBarPerc(n) {
			return (-1 + n) * 100;
		}
		/**
		* (Internal) returns the correct CSS for changing the bar's
		* position given an n percentage, and speed and ease from Settings
		*/
		function barPositionCSS(n, speed, ease) {
			var barCSS;
			if (Settings.positionUsing === "translate3d") barCSS = { transform: "translate3d(" + toBarPerc(n) + "%,0,0)" };
			else if (Settings.positionUsing === "translate") barCSS = { transform: "translate(" + toBarPerc(n) + "%,0)" };
			else barCSS = { "margin-left": toBarPerc(n) + "%" };
			barCSS.transition = "all " + speed + "ms " + ease;
			return barCSS;
		}
		/**
		* (Internal) Queues a function to be executed.
		*/
		var queue = (function() {
			var pending = [];
			function next() {
				var fn = pending.shift();
				if (fn) fn(next);
			}
			return function(fn) {
				pending.push(fn);
				if (pending.length == 1) next();
			};
		})();
		/**
		* (Internal) Applies css properties to an element, similar to the jQuery 
		* css method.
		*
		* While this helper does assist with vendor prefixed property names, it 
		* does not perform any manipulation of values prior to setting styles.
		*/
		var css = (function() {
			var cssPrefixes = [
				"Webkit",
				"O",
				"Moz",
				"ms"
			], cssProps = {};
			function camelCase(string) {
				return string.replace(/^-ms-/, "ms-").replace(/-([\da-z])/gi, function(match, letter) {
					return letter.toUpperCase();
				});
			}
			function getVendorProp(name) {
				var style = document.body.style;
				if (name in style) return name;
				var i = cssPrefixes.length, capName = name.charAt(0).toUpperCase() + name.slice(1), vendorName;
				while (i--) {
					vendorName = cssPrefixes[i] + capName;
					if (vendorName in style) return vendorName;
				}
				return name;
			}
			function getStyleProp(name) {
				name = camelCase(name);
				return cssProps[name] || (cssProps[name] = getVendorProp(name));
			}
			function applyCss(element, prop, value) {
				prop = getStyleProp(prop);
				element.style[prop] = value;
			}
			return function(element, properties) {
				var args = arguments, prop, value;
				if (args.length == 2) for (prop in properties) {
					value = properties[prop];
					if (value !== void 0 && properties.hasOwnProperty(prop)) applyCss(element, prop, value);
				}
				else applyCss(element, args[1], args[2]);
			};
		})();
		/**
		* (Internal) Determines if an element or space separated list of class names contains a class name.
		*/
		function hasClass(element, name) {
			return (typeof element == "string" ? element : classList(element)).indexOf(" " + name + " ") >= 0;
		}
		/**
		* (Internal) Adds a class to an element.
		*/
		function addClass(element, name) {
			var oldList = classList(element), newList = oldList + name;
			if (hasClass(oldList, name)) return;
			element.className = newList.substring(1);
		}
		/**
		* (Internal) Removes a class from an element.
		*/
		function removeClass(element, name) {
			var oldList = classList(element), newList;
			if (!hasClass(element, name)) return;
			newList = oldList.replace(" " + name + " ", " ");
			element.className = newList.substring(1, newList.length - 1);
		}
		/**
		* (Internal) Gets a space separated list of the class names on the element. 
		* The list is wrapped with a single space on each end to facilitate finding 
		* matches within the list.
		*/
		function classList(element) {
			return (" " + (element.className || "") + " ").replace(/\s+/gi, " ");
		}
		/**
		* (Internal) Removes an element from the DOM.
		*/
		function removeElement(element) {
			element && element.parentNode && element.parentNode.removeChild(element);
		}
		return NProgress;
	});
})))(), 1);
import_nprogress.default.configure({ showSpinner: false });
var progressing = ref(false);
var progressNames = ref([]);
var timer = ref(null);
function names$1() {
	return progressNames.value;
}
function start() {
	progressing.value = true;
	timer.value = setTimeout(() => import_nprogress.default.start(), 500);
}
function stop() {
	if (timer.value) clearTimeout(timer.value);
	import_nprogress.default.done();
	progressing.value = false;
}
function add$1(name) {
	if (progressNames.value.indexOf(name) == -1) progressNames.value = [...progressNames.value, name];
}
function remove$1(name) {
	const newValues = [...progressNames.value];
	const i = newValues.indexOf(name);
	if (i === -1) return;
	newValues.splice(i, 1);
	progressNames.value = newValues;
}
function loading(name, loading) {
	loading ? add$1(name) : remove$1(name);
}
function count$1() {
	return progressNames.value.length;
}
function isComplete() {
	return count$1() === 0;
}
watch(names$1, (newNames) => {
	if (newNames.length > 0 && !progressing.value) start();
	if (newNames.length === 0 && progressing.value) stop();
}, { immediate: true });
function useProgressBar() {
	return {
		loading,
		start: add$1,
		complete: remove$1,
		names: names$1,
		count: count$1,
		isComplete
	};
}
//#endregion
//#region resources/js/components/field-actions/FieldActions.js
var FieldActions = class {
	constructor() {
		this.actions = {};
	}
	add(name, action) {
		if (this.actions[name] === void 0) this.actions[name] = [];
		this.actions[name].push(action);
	}
	get(name) {
		return this.actions[name] || [];
	}
};
//#endregion
//#region resources/js/components/FieldConditions.js
var conditions$1 = ref({});
var FieldConditions = class {
	add(name, condition) {
		conditions$1.value[name] = condition;
	}
	get(name) {
		return conditions$1.value[name];
	}
};
//#endregion
//#region resources/js/components/Callbacks.js
var Callbacks = class {
	constructor() {
		this.callbacks = [];
	}
	add(name, callback) {
		this.callbacks[name] = callback;
	}
	call(name, ...args) {
		if (this.callbacks[name]) return this.callbacks[name](...args);
	}
};
//#endregion
//#region resources/js/composables/dirty-state.js
var dirty$1 = ref([]);
var inertiaWarningListener = null;
var dirtyUrl = null;
var dirtyState = null;
function names() {
	return dirty$1.value;
}
function clear() {
	dirty$1.value = [];
}
function count() {
	return dirty$1.value.length;
}
function add(name) {
	if (dirty$1.value.indexOf(name) == -1) {
		if (!dirty$1.value.length) {
			dirtyUrl = window.location.href;
			dirtyState = window.history.state;
		}
		dirty$1.value = [...dirty$1.value, name];
	}
}
function remove(name) {
	dirty$1.value = dirty$1.value.filter((n) => n !== name);
}
function isWarningEnabled() {
	return Statamic.$preferences.get("confirm_dirty_navigation", true);
}
function enableWarning() {
	if (!isWarningEnabled()) return;
	inertiaWarningListener ??= router.on("before", (event) => {
		const confirmed = confirm(__("statamic::messages.dirty_navigation_warning"));
		if (confirmed) {
			router.on("success", () => clear());
			disableWarning();
		}
		return confirmed;
	});
	window.onbeforeunload = () => "";
}
function disableWarning() {
	window.onbeforeunload = null;
	inertiaWarningListener && inertiaWarningListener();
	inertiaWarningListener = null;
}
window.addEventListener("popstate", (event) => {
	if (!dirty$1.value.length) return;
	if (!isWarningEnabled()) return;
	if (dirtyUrl) {
		const origin = new URL(dirtyUrl);
		if (window.location.pathname === origin.pathname && window.location.search === origin.search) return;
	}
	event.stopImmediatePropagation();
	if (dirtyUrl && dirtyState) window.history.pushState(dirtyState, "", dirtyUrl);
	if (!confirm(__("statamic::messages.dirty_navigation_warning"))) return;
	clear();
	disableWarning();
	window.history.back();
});
function state(name, state) {
	state ? add(name) : remove(name);
}
function has(name) {
	return dirty$1.value.includes(name);
}
watch(dirty$1, (newNames) => {
	newNames.length ? enableWarning() : disableWarning();
}, { immediate: true });
function useDirtyState() {
	return {
		state,
		add,
		remove,
		names,
		count,
		has,
		disableWarning
	};
}
//#endregion
//#region node_modules/speakingurl/lib/speakingurl.js
var require_speakingurl$1 = /* @__PURE__ */ __commonJSMin(((exports, module) => {
	(function(root) {
		"use strict";
		/**
		* charMap
		* @type {Object}
		*/
		var charMap = {
			"À": "A",
			"Á": "A",
			"Â": "A",
			"Ã": "A",
			"Ä": "Ae",
			"Å": "A",
			"Æ": "AE",
			"Ç": "C",
			"È": "E",
			"É": "E",
			"Ê": "E",
			"Ë": "E",
			"Ì": "I",
			"Í": "I",
			"Î": "I",
			"Ï": "I",
			"Ð": "D",
			"Ñ": "N",
			"Ò": "O",
			"Ó": "O",
			"Ô": "O",
			"Õ": "O",
			"Ö": "Oe",
			"Ő": "O",
			"Ø": "O",
			"Ù": "U",
			"Ú": "U",
			"Û": "U",
			"Ü": "Ue",
			"Ű": "U",
			"Ý": "Y",
			"Þ": "TH",
			"ß": "ss",
			"à": "a",
			"á": "a",
			"â": "a",
			"ã": "a",
			"ä": "ae",
			"å": "a",
			"æ": "ae",
			"ç": "c",
			"è": "e",
			"é": "e",
			"ê": "e",
			"ë": "e",
			"ì": "i",
			"í": "i",
			"î": "i",
			"ï": "i",
			"ð": "d",
			"ñ": "n",
			"ò": "o",
			"ó": "o",
			"ô": "o",
			"õ": "o",
			"ö": "oe",
			"ő": "o",
			"ø": "o",
			"ù": "u",
			"ú": "u",
			"û": "u",
			"ü": "ue",
			"ű": "u",
			"ý": "y",
			"þ": "th",
			"ÿ": "y",
			"ẞ": "SS",
			"ا": "a",
			"أ": "a",
			"إ": "i",
			"آ": "aa",
			"ؤ": "u",
			"ئ": "e",
			"ء": "a",
			"ب": "b",
			"ت": "t",
			"ث": "th",
			"ج": "j",
			"ح": "h",
			"خ": "kh",
			"د": "d",
			"ذ": "th",
			"ر": "r",
			"ز": "z",
			"س": "s",
			"ش": "sh",
			"ص": "s",
			"ض": "dh",
			"ط": "t",
			"ظ": "z",
			"ع": "a",
			"غ": "gh",
			"ف": "f",
			"ق": "q",
			"ك": "k",
			"ل": "l",
			"م": "m",
			"ن": "n",
			"ه": "h",
			"و": "w",
			"ي": "y",
			"ى": "a",
			"ة": "h",
			"ﻻ": "la",
			"ﻷ": "laa",
			"ﻹ": "lai",
			"ﻵ": "laa",
			"گ": "g",
			"چ": "ch",
			"پ": "p",
			"ژ": "zh",
			"ک": "k",
			"ی": "y",
			"َ": "a",
			"ً": "an",
			"ِ": "e",
			"ٍ": "en",
			"ُ": "u",
			"ٌ": "on",
			"ْ": "",
			"٠": "0",
			"١": "1",
			"٢": "2",
			"٣": "3",
			"٤": "4",
			"٥": "5",
			"٦": "6",
			"٧": "7",
			"٨": "8",
			"٩": "9",
			"۰": "0",
			"۱": "1",
			"۲": "2",
			"۳": "3",
			"۴": "4",
			"۵": "5",
			"۶": "6",
			"۷": "7",
			"۸": "8",
			"۹": "9",
			"က": "k",
			"ခ": "kh",
			"ဂ": "g",
			"ဃ": "ga",
			"င": "ng",
			"စ": "s",
			"ဆ": "sa",
			"ဇ": "z",
			"စျ": "za",
			"ည": "ny",
			"ဋ": "t",
			"ဌ": "ta",
			"ဍ": "d",
			"ဎ": "da",
			"ဏ": "na",
			"တ": "t",
			"ထ": "ta",
			"ဒ": "d",
			"ဓ": "da",
			"န": "n",
			"ပ": "p",
			"ဖ": "pa",
			"ဗ": "b",
			"ဘ": "ba",
			"မ": "m",
			"ယ": "y",
			"ရ": "ya",
			"လ": "l",
			"ဝ": "w",
			"သ": "th",
			"ဟ": "h",
			"ဠ": "la",
			"အ": "a",
			"ြ": "y",
			"ျ": "ya",
			"ွ": "w",
			"ြွ": "yw",
			"ျွ": "ywa",
			"ှ": "h",
			"ဧ": "e",
			"၏": "-e",
			"ဣ": "i",
			"ဤ": "-i",
			"ဉ": "u",
			"ဦ": "-u",
			"ဩ": "aw",
			"သြော": "aw",
			"ဪ": "aw",
			"၀": "0",
			"၁": "1",
			"၂": "2",
			"၃": "3",
			"၄": "4",
			"၅": "5",
			"၆": "6",
			"၇": "7",
			"၈": "8",
			"၉": "9",
			"္": "",
			"့": "",
			"း": "",
			"č": "c",
			"ď": "d",
			"ě": "e",
			"ň": "n",
			"ř": "r",
			"š": "s",
			"ť": "t",
			"ů": "u",
			"ž": "z",
			"Č": "C",
			"Ď": "D",
			"Ě": "E",
			"Ň": "N",
			"Ř": "R",
			"Š": "S",
			"Ť": "T",
			"Ů": "U",
			"Ž": "Z",
			"ހ": "h",
			"ށ": "sh",
			"ނ": "n",
			"ރ": "r",
			"ބ": "b",
			"ޅ": "lh",
			"ކ": "k",
			"އ": "a",
			"ވ": "v",
			"މ": "m",
			"ފ": "f",
			"ދ": "dh",
			"ތ": "th",
			"ލ": "l",
			"ގ": "g",
			"ޏ": "gn",
			"ސ": "s",
			"ޑ": "d",
			"ޒ": "z",
			"ޓ": "t",
			"ޔ": "y",
			"ޕ": "p",
			"ޖ": "j",
			"ޗ": "ch",
			"ޘ": "tt",
			"ޙ": "hh",
			"ޚ": "kh",
			"ޛ": "th",
			"ޜ": "z",
			"ޝ": "sh",
			"ޞ": "s",
			"ޟ": "d",
			"ޠ": "t",
			"ޡ": "z",
			"ޢ": "a",
			"ޣ": "gh",
			"ޤ": "q",
			"ޥ": "w",
			"ަ": "a",
			"ާ": "aa",
			"ި": "i",
			"ީ": "ee",
			"ު": "u",
			"ޫ": "oo",
			"ެ": "e",
			"ޭ": "ey",
			"ޮ": "o",
			"ޯ": "oa",
			"ް": "",
			"ა": "a",
			"ბ": "b",
			"გ": "g",
			"დ": "d",
			"ე": "e",
			"ვ": "v",
			"ზ": "z",
			"თ": "t",
			"ი": "i",
			"კ": "k",
			"ლ": "l",
			"მ": "m",
			"ნ": "n",
			"ო": "o",
			"პ": "p",
			"ჟ": "zh",
			"რ": "r",
			"ს": "s",
			"ტ": "t",
			"უ": "u",
			"ფ": "p",
			"ქ": "k",
			"ღ": "gh",
			"ყ": "q",
			"შ": "sh",
			"ჩ": "ch",
			"ც": "ts",
			"ძ": "dz",
			"წ": "ts",
			"ჭ": "ch",
			"ხ": "kh",
			"ჯ": "j",
			"ჰ": "h",
			"α": "a",
			"β": "v",
			"γ": "g",
			"δ": "d",
			"ε": "e",
			"ζ": "z",
			"η": "i",
			"θ": "th",
			"ι": "i",
			"κ": "k",
			"λ": "l",
			"μ": "m",
			"ν": "n",
			"ξ": "ks",
			"ο": "o",
			"π": "p",
			"ρ": "r",
			"σ": "s",
			"τ": "t",
			"υ": "y",
			"φ": "f",
			"χ": "x",
			"ψ": "ps",
			"ω": "o",
			"ά": "a",
			"έ": "e",
			"ί": "i",
			"ό": "o",
			"ύ": "y",
			"ή": "i",
			"ώ": "o",
			"ς": "s",
			"ϊ": "i",
			"ΰ": "y",
			"ϋ": "y",
			"ΐ": "i",
			"Α": "A",
			"Β": "B",
			"Γ": "G",
			"Δ": "D",
			"Ε": "E",
			"Ζ": "Z",
			"Η": "I",
			"Θ": "TH",
			"Ι": "I",
			"Κ": "K",
			"Λ": "L",
			"Μ": "M",
			"Ν": "N",
			"Ξ": "KS",
			"Ο": "O",
			"Π": "P",
			"Ρ": "R",
			"Σ": "S",
			"Τ": "T",
			"Υ": "Y",
			"Φ": "F",
			"Χ": "X",
			"Ψ": "PS",
			"Ω": "O",
			"Ά": "A",
			"Έ": "E",
			"Ί": "I",
			"Ό": "O",
			"Ύ": "Y",
			"Ή": "I",
			"Ώ": "O",
			"Ϊ": "I",
			"Ϋ": "Y",
			"ā": "a",
			"ē": "e",
			"ģ": "g",
			"ī": "i",
			"ķ": "k",
			"ļ": "l",
			"ņ": "n",
			"ū": "u",
			"Ā": "A",
			"Ē": "E",
			"Ģ": "G",
			"Ī": "I",
			"Ķ": "k",
			"Ļ": "L",
			"Ņ": "N",
			"Ū": "U",
			"Ќ": "Kj",
			"ќ": "kj",
			"Љ": "Lj",
			"љ": "lj",
			"Њ": "Nj",
			"њ": "nj",
			"Тс": "Ts",
			"тс": "ts",
			"ą": "a",
			"ć": "c",
			"ę": "e",
			"ł": "l",
			"ń": "n",
			"ś": "s",
			"ź": "z",
			"ż": "z",
			"Ą": "A",
			"Ć": "C",
			"Ę": "E",
			"Ł": "L",
			"Ń": "N",
			"Ś": "S",
			"Ź": "Z",
			"Ż": "Z",
			"Є": "Ye",
			"І": "I",
			"Ї": "Yi",
			"Ґ": "G",
			"є": "ye",
			"і": "i",
			"ї": "yi",
			"ґ": "g",
			"ă": "a",
			"Ă": "A",
			"ș": "s",
			"Ș": "S",
			"ț": "t",
			"Ț": "T",
			"ţ": "t",
			"Ţ": "T",
			"а": "a",
			"б": "b",
			"в": "v",
			"г": "g",
			"д": "d",
			"е": "e",
			"ё": "yo",
			"ж": "zh",
			"з": "z",
			"и": "i",
			"й": "i",
			"к": "k",
			"л": "l",
			"м": "m",
			"н": "n",
			"о": "o",
			"п": "p",
			"р": "r",
			"с": "s",
			"т": "t",
			"у": "u",
			"ф": "f",
			"х": "kh",
			"ц": "c",
			"ч": "ch",
			"ш": "sh",
			"щ": "sh",
			"ъ": "",
			"ы": "y",
			"ь": "",
			"э": "e",
			"ю": "yu",
			"я": "ya",
			"А": "A",
			"Б": "B",
			"В": "V",
			"Г": "G",
			"Д": "D",
			"Е": "E",
			"Ё": "Yo",
			"Ж": "Zh",
			"З": "Z",
			"И": "I",
			"Й": "I",
			"К": "K",
			"Л": "L",
			"М": "M",
			"Н": "N",
			"О": "O",
			"П": "P",
			"Р": "R",
			"С": "S",
			"Т": "T",
			"У": "U",
			"Ф": "F",
			"Х": "Kh",
			"Ц": "C",
			"Ч": "Ch",
			"Ш": "Sh",
			"Щ": "Sh",
			"Ъ": "",
			"Ы": "Y",
			"Ь": "",
			"Э": "E",
			"Ю": "Yu",
			"Я": "Ya",
			"ђ": "dj",
			"ј": "j",
			"ћ": "c",
			"џ": "dz",
			"Ђ": "Dj",
			"Ј": "j",
			"Ћ": "C",
			"Џ": "Dz",
			"ľ": "l",
			"ĺ": "l",
			"ŕ": "r",
			"Ľ": "L",
			"Ĺ": "L",
			"Ŕ": "R",
			"ş": "s",
			"Ş": "S",
			"ı": "i",
			"İ": "I",
			"ğ": "g",
			"Ğ": "G",
			"ả": "a",
			"Ả": "A",
			"ẳ": "a",
			"Ẳ": "A",
			"ẩ": "a",
			"Ẩ": "A",
			"đ": "d",
			"Đ": "D",
			"ẹ": "e",
			"Ẹ": "E",
			"ẽ": "e",
			"Ẽ": "E",
			"ẻ": "e",
			"Ẻ": "E",
			"ế": "e",
			"Ế": "E",
			"ề": "e",
			"Ề": "E",
			"ệ": "e",
			"Ệ": "E",
			"ễ": "e",
			"Ễ": "E",
			"ể": "e",
			"Ể": "E",
			"ỏ": "o",
			"ọ": "o",
			"Ọ": "o",
			"ố": "o",
			"Ố": "O",
			"ồ": "o",
			"Ồ": "O",
			"ổ": "o",
			"Ổ": "O",
			"ộ": "o",
			"Ộ": "O",
			"ỗ": "o",
			"Ỗ": "O",
			"ơ": "o",
			"Ơ": "O",
			"ớ": "o",
			"Ớ": "O",
			"ờ": "o",
			"Ờ": "O",
			"ợ": "o",
			"Ợ": "O",
			"ỡ": "o",
			"Ỡ": "O",
			"Ở": "o",
			"ở": "o",
			"ị": "i",
			"Ị": "I",
			"ĩ": "i",
			"Ĩ": "I",
			"ỉ": "i",
			"Ỉ": "i",
			"ủ": "u",
			"Ủ": "U",
			"ụ": "u",
			"Ụ": "U",
			"ũ": "u",
			"Ũ": "U",
			"ư": "u",
			"Ư": "U",
			"ứ": "u",
			"Ứ": "U",
			"ừ": "u",
			"Ừ": "U",
			"ự": "u",
			"Ự": "U",
			"ữ": "u",
			"Ữ": "U",
			"ử": "u",
			"Ử": "ư",
			"ỷ": "y",
			"Ỷ": "y",
			"ỳ": "y",
			"Ỳ": "Y",
			"ỵ": "y",
			"Ỵ": "Y",
			"ỹ": "y",
			"Ỹ": "Y",
			"ạ": "a",
			"Ạ": "A",
			"ấ": "a",
			"Ấ": "A",
			"ầ": "a",
			"Ầ": "A",
			"ậ": "a",
			"Ậ": "A",
			"ẫ": "a",
			"Ẫ": "A",
			"ắ": "a",
			"Ắ": "A",
			"ằ": "a",
			"Ằ": "A",
			"ặ": "a",
			"Ặ": "A",
			"ẵ": "a",
			"Ẵ": "A",
			"⓪": "0",
			"①": "1",
			"②": "2",
			"③": "3",
			"④": "4",
			"⑤": "5",
			"⑥": "6",
			"⑦": "7",
			"⑧": "8",
			"⑨": "9",
			"⑩": "10",
			"⑪": "11",
			"⑫": "12",
			"⑬": "13",
			"⑭": "14",
			"⑮": "15",
			"⑯": "16",
			"⑰": "17",
			"⑱": "18",
			"⑲": "18",
			"⑳": "18",
			"⓵": "1",
			"⓶": "2",
			"⓷": "3",
			"⓸": "4",
			"⓹": "5",
			"⓺": "6",
			"⓻": "7",
			"⓼": "8",
			"⓽": "9",
			"⓾": "10",
			"⓿": "0",
			"⓫": "11",
			"⓬": "12",
			"⓭": "13",
			"⓮": "14",
			"⓯": "15",
			"⓰": "16",
			"⓱": "17",
			"⓲": "18",
			"⓳": "19",
			"⓴": "20",
			"Ⓐ": "A",
			"Ⓑ": "B",
			"Ⓒ": "C",
			"Ⓓ": "D",
			"Ⓔ": "E",
			"Ⓕ": "F",
			"Ⓖ": "G",
			"Ⓗ": "H",
			"Ⓘ": "I",
			"Ⓙ": "J",
			"Ⓚ": "K",
			"Ⓛ": "L",
			"Ⓜ": "M",
			"Ⓝ": "N",
			"Ⓞ": "O",
			"Ⓟ": "P",
			"Ⓠ": "Q",
			"Ⓡ": "R",
			"Ⓢ": "S",
			"Ⓣ": "T",
			"Ⓤ": "U",
			"Ⓥ": "V",
			"Ⓦ": "W",
			"Ⓧ": "X",
			"Ⓨ": "Y",
			"Ⓩ": "Z",
			"ⓐ": "a",
			"ⓑ": "b",
			"ⓒ": "c",
			"ⓓ": "d",
			"ⓔ": "e",
			"ⓕ": "f",
			"ⓖ": "g",
			"ⓗ": "h",
			"ⓘ": "i",
			"ⓙ": "j",
			"ⓚ": "k",
			"ⓛ": "l",
			"ⓜ": "m",
			"ⓝ": "n",
			"ⓞ": "o",
			"ⓟ": "p",
			"ⓠ": "q",
			"ⓡ": "r",
			"ⓢ": "s",
			"ⓣ": "t",
			"ⓤ": "u",
			"ⓦ": "v",
			"ⓥ": "w",
			"ⓧ": "x",
			"ⓨ": "y",
			"ⓩ": "z",
			"“": "\"",
			"”": "\"",
			"‘": "'",
			"’": "'",
			"∂": "d",
			"ƒ": "f",
			"™": "(TM)",
			"©": "(C)",
			"œ": "oe",
			"Œ": "OE",
			"®": "(R)",
			"†": "+",
			"℠": "(SM)",
			"…": "...",
			"˚": "o",
			"º": "o",
			"ª": "a",
			"•": "*",
			"၊": ",",
			"။": ".",
			"$": "USD",
			"€": "EUR",
			"₢": "BRN",
			"₣": "FRF",
			"£": "GBP",
			"₤": "ITL",
			"₦": "NGN",
			"₧": "ESP",
			"₩": "KRW",
			"₪": "ILS",
			"₫": "VND",
			"₭": "LAK",
			"₮": "MNT",
			"₯": "GRD",
			"₱": "ARS",
			"₲": "PYG",
			"₳": "ARA",
			"₴": "UAH",
			"₵": "GHS",
			"¢": "cent",
			"¥": "CNY",
			"元": "CNY",
			"円": "YEN",
			"﷼": "IRR",
			"₠": "EWE",
			"฿": "THB",
			"₨": "INR",
			"₹": "INR",
			"₰": "PF",
			"₺": "TRY",
			"؋": "AFN",
			"₼": "AZN",
			"лв": "BGN",
			"៛": "KHR",
			"₡": "CRC",
			"₸": "KZT",
			"ден": "MKD",
			"zł": "PLN",
			"₽": "RUB",
			"₾": "GEL"
		};
		/**
		* special look ahead character array
		* These characters form with consonants to become 'single'/consonant combo
		* @type [Array]
		*/
		var lookAheadCharArray = ["်", "ް"];
		/**
		* diatricMap for languages where transliteration changes entirely as more diatrics are added
		* @type {Object}
		*/
		var diatricMap = {
			"ာ": "a",
			"ါ": "a",
			"ေ": "e",
			"ဲ": "e",
			"ိ": "i",
			"ီ": "i",
			"ို": "o",
			"ု": "u",
			"ူ": "u",
			"ေါင်": "aung",
			"ော": "aw",
			"ော်": "aw",
			"ေါ": "aw",
			"ေါ်": "aw",
			"်": "်",
			"က်": "et",
			"ိုက်": "aik",
			"ောက်": "auk",
			"င်": "in",
			"ိုင်": "aing",
			"ောင်": "aung",
			"စ်": "it",
			"ည်": "i",
			"တ်": "at",
			"ိတ်": "eik",
			"ုတ်": "ok",
			"ွတ်": "ut",
			"ေတ်": "it",
			"ဒ်": "d",
			"ိုဒ်": "ok",
			"ုဒ်": "ait",
			"န်": "an",
			"ာန်": "an",
			"ိန်": "ein",
			"ုန်": "on",
			"ွန်": "un",
			"ပ်": "at",
			"ိပ်": "eik",
			"ုပ်": "ok",
			"ွပ်": "ut",
			"န်ုပ်": "nub",
			"မ်": "an",
			"ိမ်": "ein",
			"ုမ်": "on",
			"ွမ်": "un",
			"ယ်": "e",
			"ိုလ်": "ol",
			"ဉ်": "in",
			"ံ": "an",
			"ိံ": "ein",
			"ုံ": "on",
			"ައް": "ah",
			"ަށް": "ah"
		};
		/**
		* langCharMap language specific characters translations
		* @type   {Object}
		*/
		var langCharMap = {
			"en": {},
			"az": {
				"ç": "c",
				"ə": "e",
				"ğ": "g",
				"ı": "i",
				"ö": "o",
				"ş": "s",
				"ü": "u",
				"Ç": "C",
				"Ə": "E",
				"Ğ": "G",
				"İ": "I",
				"Ö": "O",
				"Ş": "S",
				"Ü": "U"
			},
			"cs": {
				"č": "c",
				"ď": "d",
				"ě": "e",
				"ň": "n",
				"ř": "r",
				"š": "s",
				"ť": "t",
				"ů": "u",
				"ž": "z",
				"Č": "C",
				"Ď": "D",
				"Ě": "E",
				"Ň": "N",
				"Ř": "R",
				"Š": "S",
				"Ť": "T",
				"Ů": "U",
				"Ž": "Z"
			},
			"fi": {
				"ä": "a",
				"Ä": "A",
				"ö": "o",
				"Ö": "O"
			},
			"hu": {
				"ä": "a",
				"Ä": "A",
				"ö": "o",
				"Ö": "O",
				"ü": "u",
				"Ü": "U",
				"ű": "u",
				"Ű": "U"
			},
			"lt": {
				"ą": "a",
				"č": "c",
				"ę": "e",
				"ė": "e",
				"į": "i",
				"š": "s",
				"ų": "u",
				"ū": "u",
				"ž": "z",
				"Ą": "A",
				"Č": "C",
				"Ę": "E",
				"Ė": "E",
				"Į": "I",
				"Š": "S",
				"Ų": "U",
				"Ū": "U"
			},
			"lv": {
				"ā": "a",
				"č": "c",
				"ē": "e",
				"ģ": "g",
				"ī": "i",
				"ķ": "k",
				"ļ": "l",
				"ņ": "n",
				"š": "s",
				"ū": "u",
				"ž": "z",
				"Ā": "A",
				"Č": "C",
				"Ē": "E",
				"Ģ": "G",
				"Ī": "i",
				"Ķ": "k",
				"Ļ": "L",
				"Ņ": "N",
				"Š": "S",
				"Ū": "u",
				"Ž": "Z"
			},
			"pl": {
				"ą": "a",
				"ć": "c",
				"ę": "e",
				"ł": "l",
				"ń": "n",
				"ó": "o",
				"ś": "s",
				"ź": "z",
				"ż": "z",
				"Ą": "A",
				"Ć": "C",
				"Ę": "e",
				"Ł": "L",
				"Ń": "N",
				"Ó": "O",
				"Ś": "S",
				"Ź": "Z",
				"Ż": "Z"
			},
			"sv": {
				"ä": "a",
				"Ä": "A",
				"ö": "o",
				"Ö": "O"
			},
			"sk": {
				"ä": "a",
				"Ä": "A"
			},
			"sr": {
				"љ": "lj",
				"њ": "nj",
				"Љ": "Lj",
				"Њ": "Nj",
				"đ": "dj",
				"Đ": "Dj"
			},
			"tr": {
				"Ü": "U",
				"Ö": "O",
				"ü": "u",
				"ö": "o"
			}
		};
		/**
		* symbolMap language specific symbol translations
		* translations must be transliterated already
		* @type   {Object}
		*/
		var symbolMap = {
			"ar": {
				"∆": "delta",
				"∞": "la-nihaya",
				"♥": "hob",
				"&": "wa",
				"|": "aw",
				"<": "aqal-men",
				">": "akbar-men",
				"∑": "majmou",
				"¤": "omla"
			},
			"az": {},
			"ca": {
				"∆": "delta",
				"∞": "infinit",
				"♥": "amor",
				"&": "i",
				"|": "o",
				"<": "menys que",
				">": "mes que",
				"∑": "suma dels",
				"¤": "moneda"
			},
			"cs": {
				"∆": "delta",
				"∞": "nekonecno",
				"♥": "laska",
				"&": "a",
				"|": "nebo",
				"<": "mensi nez",
				">": "vetsi nez",
				"∑": "soucet",
				"¤": "mena"
			},
			"de": {
				"∆": "delta",
				"∞": "unendlich",
				"♥": "Liebe",
				"&": "und",
				"|": "oder",
				"<": "kleiner als",
				">": "groesser als",
				"∑": "Summe von",
				"¤": "Waehrung"
			},
			"dv": {
				"∆": "delta",
				"∞": "kolunulaa",
				"♥": "loabi",
				"&": "aai",
				"|": "noonee",
				"<": "ah vure kuda",
				">": "ah vure bodu",
				"∑": "jumula",
				"¤": "faisaa"
			},
			"en": {
				"∆": "delta",
				"∞": "infinity",
				"♥": "love",
				"&": "and",
				"|": "or",
				"<": "less than",
				">": "greater than",
				"∑": "sum",
				"¤": "currency"
			},
			"es": {
				"∆": "delta",
				"∞": "infinito",
				"♥": "amor",
				"&": "y",
				"|": "u",
				"<": "menos que",
				">": "mas que",
				"∑": "suma de los",
				"¤": "moneda"
			},
			"fa": {
				"∆": "delta",
				"∞": "bi-nahayat",
				"♥": "eshgh",
				"&": "va",
				"|": "ya",
				"<": "kamtar-az",
				">": "bishtar-az",
				"∑": "majmooe",
				"¤": "vahed"
			},
			"fi": {
				"∆": "delta",
				"∞": "aarettomyys",
				"♥": "rakkaus",
				"&": "ja",
				"|": "tai",
				"<": "pienempi kuin",
				">": "suurempi kuin",
				"∑": "summa",
				"¤": "valuutta"
			},
			"fr": {
				"∆": "delta",
				"∞": "infiniment",
				"♥": "Amour",
				"&": "et",
				"|": "ou",
				"<": "moins que",
				">": "superieure a",
				"∑": "somme des",
				"¤": "monnaie"
			},
			"ge": {
				"∆": "delta",
				"∞": "usasruloba",
				"♥": "siqvaruli",
				"&": "da",
				"|": "an",
				"<": "naklebi",
				">": "meti",
				"∑": "jami",
				"¤": "valuta"
			},
			"gr": {},
			"hu": {
				"∆": "delta",
				"∞": "vegtelen",
				"♥": "szerelem",
				"&": "es",
				"|": "vagy",
				"<": "kisebb mint",
				">": "nagyobb mint",
				"∑": "szumma",
				"¤": "penznem"
			},
			"it": {
				"∆": "delta",
				"∞": "infinito",
				"♥": "amore",
				"&": "e",
				"|": "o",
				"<": "minore di",
				">": "maggiore di",
				"∑": "somma",
				"¤": "moneta"
			},
			"lt": {
				"∆": "delta",
				"∞": "begalybe",
				"♥": "meile",
				"&": "ir",
				"|": "ar",
				"<": "maziau nei",
				">": "daugiau nei",
				"∑": "suma",
				"¤": "valiuta"
			},
			"lv": {
				"∆": "delta",
				"∞": "bezgaliba",
				"♥": "milestiba",
				"&": "un",
				"|": "vai",
				"<": "mazak neka",
				">": "lielaks neka",
				"∑": "summa",
				"¤": "valuta"
			},
			"my": {
				"∆": "kwahkhyaet",
				"∞": "asaonasme",
				"♥": "akhyait",
				"&": "nhin",
				"|": "tho",
				"<": "ngethaw",
				">": "kyithaw",
				"∑": "paungld",
				"¤": "ngwekye"
			},
			"mk": {},
			"nl": {
				"∆": "delta",
				"∞": "oneindig",
				"♥": "liefde",
				"&": "en",
				"|": "of",
				"<": "kleiner dan",
				">": "groter dan",
				"∑": "som",
				"¤": "valuta"
			},
			"pl": {
				"∆": "delta",
				"∞": "nieskonczonosc",
				"♥": "milosc",
				"&": "i",
				"|": "lub",
				"<": "mniejsze niz",
				">": "wieksze niz",
				"∑": "suma",
				"¤": "waluta"
			},
			"pt": {
				"∆": "delta",
				"∞": "infinito",
				"♥": "amor",
				"&": "e",
				"|": "ou",
				"<": "menor que",
				">": "maior que",
				"∑": "soma",
				"¤": "moeda"
			},
			"ro": {
				"∆": "delta",
				"∞": "infinit",
				"♥": "dragoste",
				"&": "si",
				"|": "sau",
				"<": "mai mic ca",
				">": "mai mare ca",
				"∑": "suma",
				"¤": "valuta"
			},
			"ru": {
				"∆": "delta",
				"∞": "beskonechno",
				"♥": "lubov",
				"&": "i",
				"|": "ili",
				"<": "menshe",
				">": "bolshe",
				"∑": "summa",
				"¤": "valjuta"
			},
			"sk": {
				"∆": "delta",
				"∞": "nekonecno",
				"♥": "laska",
				"&": "a",
				"|": "alebo",
				"<": "menej ako",
				">": "viac ako",
				"∑": "sucet",
				"¤": "mena"
			},
			"sr": {},
			"tr": {
				"∆": "delta",
				"∞": "sonsuzluk",
				"♥": "ask",
				"&": "ve",
				"|": "veya",
				"<": "kucuktur",
				">": "buyuktur",
				"∑": "toplam",
				"¤": "para birimi"
			},
			"uk": {
				"∆": "delta",
				"∞": "bezkinechnist",
				"♥": "lubov",
				"&": "i",
				"|": "abo",
				"<": "menshe",
				">": "bilshe",
				"∑": "suma",
				"¤": "valjuta"
			},
			"vn": {
				"∆": "delta",
				"∞": "vo cuc",
				"♥": "yeu",
				"&": "va",
				"|": "hoac",
				"<": "nho hon",
				">": "lon hon",
				"∑": "tong",
				"¤": "tien te"
			}
		};
		var uricChars = [
			";",
			"?",
			":",
			"@",
			"&",
			"=",
			"+",
			"$",
			",",
			"/"
		].join("");
		var uricNoSlashChars = [
			";",
			"?",
			":",
			"@",
			"&",
			"=",
			"+",
			"$",
			","
		].join("");
		var markChars = [
			".",
			"!",
			"~",
			"*",
			"'",
			"(",
			")"
		].join("");
		/**
		* getSlug
		* @param  {string} input input string
		* @param  {object|string} opts config object or separator string/char
		* @api    public
		* @return {string}  sluggified string
		*/
		var getSlug = function getSlug(input, opts) {
			var separator = "-";
			var result = "";
			var diatricString = "";
			var convertSymbols = true;
			var customReplacements = {};
			var maintainCase;
			var titleCase;
			var truncate;
			var uricFlag;
			var uricNoSlashFlag;
			var markFlag;
			var symbol;
			var langChar;
			var lucky;
			var i;
			var ch;
			var l;
			var lastCharWasSymbol;
			var lastCharWasDiatric;
			var allowedChars = "";
			if (typeof input !== "string") return "";
			if (typeof opts === "string") separator = opts;
			symbol = symbolMap.en;
			langChar = langCharMap.en;
			if (typeof opts === "object") {
				maintainCase = opts.maintainCase || false;
				customReplacements = opts.custom && typeof opts.custom === "object" ? opts.custom : customReplacements;
				truncate = +opts.truncate > 1 && opts.truncate || false;
				uricFlag = opts.uric || false;
				uricNoSlashFlag = opts.uricNoSlash || false;
				markFlag = opts.mark || false;
				convertSymbols = opts.symbols === false || opts.lang === false ? false : true;
				separator = opts.separator || separator;
				if (uricFlag) allowedChars += uricChars;
				if (uricNoSlashFlag) allowedChars += uricNoSlashChars;
				if (markFlag) allowedChars += markChars;
				symbol = opts.lang && symbolMap[opts.lang] && convertSymbols ? symbolMap[opts.lang] : convertSymbols ? symbolMap.en : {};
				langChar = opts.lang && langCharMap[opts.lang] ? langCharMap[opts.lang] : opts.lang === false || opts.lang === true ? {} : langCharMap.en;
				if (opts.titleCase && typeof opts.titleCase.length === "number" && Array.prototype.toString.call(opts.titleCase)) {
					opts.titleCase.forEach(function(v) {
						customReplacements[v + ""] = v + "";
					});
					titleCase = true;
				} else titleCase = !!opts.titleCase;
				if (opts.custom && typeof opts.custom.length === "number" && Array.prototype.toString.call(opts.custom)) opts.custom.forEach(function(v) {
					customReplacements[v + ""] = v + "";
				});
				Object.keys(customReplacements).forEach(function(v) {
					var r;
					if (v.length > 1) r = new RegExp("\\b" + escapeChars(v) + "\\b", "gi");
					else r = new RegExp(escapeChars(v), "gi");
					input = input.replace(r, customReplacements[v]);
				});
				for (ch in customReplacements) allowedChars += ch;
			}
			allowedChars += separator;
			allowedChars = escapeChars(allowedChars);
			input = input.replace(/(^\s+|\s+$)/g, "");
			lastCharWasSymbol = false;
			lastCharWasDiatric = false;
			for (i = 0, l = input.length; i < l; i++) {
				ch = input[i];
				if (isReplacedCustomChar(ch, customReplacements)) lastCharWasSymbol = false;
				else if (langChar[ch]) {
					ch = lastCharWasSymbol && langChar[ch].match(/[A-Za-z0-9]/) ? " " + langChar[ch] : langChar[ch];
					lastCharWasSymbol = false;
				} else if (ch in charMap) {
					if (i + 1 < l && lookAheadCharArray.indexOf(input[i + 1]) >= 0) {
						diatricString += ch;
						ch = "";
					} else if (lastCharWasDiatric === true) {
						ch = diatricMap[diatricString] + charMap[ch];
						diatricString = "";
					} else ch = lastCharWasSymbol && charMap[ch].match(/[A-Za-z0-9]/) ? " " + charMap[ch] : charMap[ch];
					lastCharWasSymbol = false;
					lastCharWasDiatric = false;
				} else if (ch in diatricMap) {
					diatricString += ch;
					ch = "";
					if (i === l - 1) ch = diatricMap[diatricString];
					lastCharWasDiatric = true;
				} else if (symbol[ch] && !(uricFlag && uricChars.indexOf(ch) !== -1) && !(uricNoSlashFlag && uricNoSlashChars.indexOf(ch) !== -1)) {
					ch = lastCharWasSymbol || result.substr(-1).match(/[A-Za-z0-9]/) ? separator + symbol[ch] : symbol[ch];
					ch += input[i + 1] !== void 0 && input[i + 1].match(/[A-Za-z0-9]/) ? separator : "";
					lastCharWasSymbol = true;
				} else {
					if (lastCharWasDiatric === true) {
						ch = diatricMap[diatricString] + ch;
						diatricString = "";
						lastCharWasDiatric = false;
					} else if (lastCharWasSymbol && (/[A-Za-z0-9]/.test(ch) || result.substr(-1).match(/A-Za-z0-9]/))) ch = " " + ch;
					lastCharWasSymbol = false;
				}
				result += ch.replace(new RegExp("[^\\w\\s" + allowedChars + "_-]", "g"), separator);
			}
			if (titleCase) result = result.replace(/(\w)(\S*)/g, function(_, i, r) {
				var j = i.toUpperCase() + (r !== null ? r : "");
				return Object.keys(customReplacements).indexOf(j.toLowerCase()) < 0 ? j : j.toLowerCase();
			});
			result = result.replace(/\s+/g, separator).replace(new RegExp("\\" + separator + "+", "g"), separator).replace(new RegExp("(^\\" + separator + "+|\\" + separator + "+$)", "g"), "");
			if (truncate && result.length > truncate) {
				lucky = result.charAt(truncate) === separator;
				result = result.slice(0, truncate);
				if (!lucky) result = result.slice(0, result.lastIndexOf(separator));
			}
			if (!maintainCase && !titleCase) result = result.toLowerCase();
			return result;
		};
		/**
		* createSlug curried(opts)(input)
		* @param   {object|string} opts config object or input string
		* @return  {Function} function getSlugWithConfig()
		**/
		var createSlug = function createSlug(opts) {
			/**
			* getSlugWithConfig
			* @param   {string} input string
			* @return  {string} slug string
			*/
			return function getSlugWithConfig(input) {
				return getSlug(input, opts);
			};
		};
		/**
		* escape Chars
		* @param   {string} input string
		*/
		var escapeChars = function escapeChars(input) {
			return input.replace(/[-\\^$*+?.()|[\]{}\/]/g, "\\$&");
		};
		/**
		* check if the char is an already converted char from custom list
		* @param   {char} ch character to check
		* @param   {object} customReplacements custom translation map
		*/
		var isReplacedCustomChar = function(ch, customReplacements) {
			for (var c in customReplacements) if (customReplacements[c] === ch) return true;
		};
		if (typeof module !== "undefined" && module.exports) {
			module.exports = getSlug;
			module.exports.createSlug = createSlug;
		} else if (typeof define !== "undefined" && define.amd) define([], function() {
			return getSlug;
		});
		else try {
			if (root.getSlug || root.createSlug) throw "speakingurl: globals exists /(getSlug|createSlug)/";
			else {
				root.getSlug = getSlug;
				root.createSlug = createSlug;
			}
		} catch (e) {}
	})(exports);
}));
//#endregion
//#region resources/js/components/slugs/Slug.js
var import_speakingurl = /* @__PURE__ */ __toESM((/* @__PURE__ */ __commonJSMin(((exports, module) => {
	module.exports = require_speakingurl$1();
})))(), 1);
var Slug = class {
	busy = false;
	#string;
	#separator = "-";
	#language;
	#debounced;
	#controller;
	#async = false;
	constructor() {
		this.#setInitialLanguage();
	}
	separatedBy(separator) {
		if (separator) this.#separator = separator;
		return this;
	}
	in(language) {
		if (language) this.#language = language;
		return this;
	}
	#setInitialLanguage() {
		const selectedSite = Statamic.$config.get("selectedSite");
		const site = Statamic.$config.get("sites").find((site) => site.handle === selectedSite);
		this.#language = site?.lang ?? Statamic.$config.get("lang");
	}
	async() {
		this.#async = true;
		this.#debounced = debounce_default(function(resolve, reject) {
			return this.#performRequest().then((slug) => resolve(slug)).catch((e) => reject(e));
		}, 300);
		return this;
	}
	create(string) {
		this.#string = (string + "").trim();
		return this.#async ? this.#createAsynchronously() : this.#createSynchronously();
	}
	#createSynchronously() {
		const symbols = Statamic.$config.get("asciiReplaceExtraSymbols");
		const charmap = Statamic.$config.get("charmap");
		let custom = charmap[this.#language] ?? {};
		custom["'"] = "";
		custom["’"] = "";
		custom[" - "] = " ";
		custom["("] = "";
		custom[")"] = "";
		custom = symbols ? this.#replaceCurrencySymbols(custom, charmap) : this.#removeCurrencySymbols(custom, charmap);
		if (this.#separator !== "-") custom["-"] = this.#separator;
		return (0, import_speakingurl.default)(this.#string, {
			separator: this.#separator,
			lang: this.#language,
			custom,
			symbols
		});
	}
	#replaceCurrencySymbols(custom, charmap) {
		return {
			...custom,
			...charmap.currency
		};
	}
	#removeCurrencySymbols(custom, charmap) {
		for (const key in charmap.currency_short) custom[key] = "";
		return custom;
	}
	#createAsynchronously() {
		if (!this.#string) {
			this.#controller?.abort();
			this.#debounced.cancel();
			this.busy = false;
			return Promise.resolve("");
		}
		this.busy = true;
		return new Promise((resolve, reject) => this.#debounced(resolve, reject));
	}
	#performRequest() {
		const payload = {
			string: this.#string,
			separator: this.#separator,
			language: this.#language
		};
		if (this.#controller) this.#controller.abort();
		this.#controller = new AbortController();
		let aborted = false;
		return axios.post(cp_url("slug"), payload, {
			signal: this.#controller.signal,
			transformResponse: [(data) => data]
		}).then((response) => response.data).catch((e) => {
			if (axios.isCancel(e)) aborted = true;
			throw e;
		}).finally(() => {
			if (!aborted) this.busy = false;
		});
	}
};
//#endregion
//#region resources/js/components/slugs/Manager.js
var Manager = class {
	make() {
		return markRaw(new Slug());
	}
	create(string) {
		return this.make().create(string);
	}
	separatedBy(separator) {
		return this.make().separatedBy(separator);
	}
	in(language) {
		return this.make().in(language);
	}
	async() {
		return this.make().async();
	}
};
//#endregion
//#region resources/js/components/Hooks.js
var Hooks = class {
	constructor() {
		this.hooks = {};
	}
	on(key, callback, priority = 10) {
		if (this.hooks[key] === void 0) this.hooks[key] = [];
		this.hooks[key].push({
			callback,
			priority
		});
	}
	run(key, payload) {
		return new Promise((resolve, reject) => {
			this.getCallbacks(key).sort((a, b) => b.priority - a.priority).map((hook) => this.convertToPromiseCallback(hook.callback, payload)).reduce((promise, callback) => {
				return promise.then((result) => callback().then(Array.prototype.concat.bind(result)));
			}, Promise.resolve([])).then((results) => resolve(results)).catch((error) => reject(error));
		});
	}
	getCallbacks(key) {
		return this.hooks[key] || [];
	}
	convertToPromiseCallback(callback, payload) {
		return () => {
			return new Promise((resolve, reject) => {
				callback(resolve, reject, payload);
			});
		};
	}
};
//#endregion
//#region resources/js/components/Bard.js
var Bard = class {
	constructor(instance) {
		this.instance = instance;
		this.extensionCallbacks = [];
		this.extensionReplacementCallbacks = [];
		this.buttonCallbacks = [];
	}
	addExtension(callback) {
		this.extensionCallbacks.push(callback);
	}
	replaceExtension(name, callback) {
		this.extensionReplacementCallbacks.push({
			name,
			callback
		});
	}
	buttons(callback) {
		this.buttonCallbacks.push(callback);
	}
};
//#endregion
//#region resources/js/components/Reveal.js
var registry = /* @__PURE__ */ new WeakMap();
var Reveal = class {
	use(ref, callback) {
		onMounted(() => this.mount(ref.value, callback));
	}
	mount(el, callback) {
		registry.set(el, callback);
		onBeforeUnmount(() => registry.delete(el));
	}
	element(el) {
		let parent = el;
		while (parent) {
			const callback = registry.get(parent);
			if (callback) callback(parent);
			parent = parent.parentElement;
		}
		nextTick(() => {
			el.scrollIntoView({ block: "center" });
		});
	}
	invalid() {
		nextTick(() => {
			const el = document.querySelector("[data-ui-field-has-errors]:not(:has([data-ui-field-has-errors]))");
			if (!el) return;
			this.element(el);
		});
	}
};
//#endregion
//#region resources/js/components/Echo.js
var Echo = class {
	constructor() {
		this.configCallbacks = [];
		this.bootedCallbacks = [];
	}
	config(callback) {
		this.configCallbacks.push(callback);
	}
	booted(callback) {
		this.bootedCallbacks.push(callback);
	}
	async start() {
		const [{ default: LaravelEcho }, { default: Pusher }] = await Promise.all([__vitePreload(() => import("./echo-DR851lhm.js"), [], import.meta.url), __vitePreload(() => import("./pusher-KzJEM2gB.js").then((m) => /* @__PURE__ */ __toESM(m.default, 1)), __vite__mapDeps([0,1]), import.meta.url)]);
		window.Pusher = Pusher;
		let config = {
			...Statamic.$config.get("broadcasting.options"),
			csrfToken: Statamic.$config.get("csrfToken"),
			authEndpoint: Statamic.$config.get("broadcasting.endpoint")
		};
		this.configCallbacks.forEach((callback) => config = callback(config));
		this.echo = new LaravelEcho(config);
		this.bootedCallbacks.forEach((callback) => callback(this));
		this.bootedCallbacks = [];
	}
};
[
	"channel",
	"connect",
	"disconnect",
	"join",
	"leave",
	"leaveChannel",
	"listen",
	"private",
	"socketId",
	"registerInterceptors",
	"registerVueRequestInterceptor",
	"registerAxiosRequestInterceptor",
	"registerjQueryAjaxSetup"
].forEach((method) => {
	Echo.prototype[method] = function(...args) {
		return this.echo[method](...args);
	};
});
//#endregion
//#region resources/js/components/Permission.js
var Permission = class {
	all() {
		return Statamic.user?.permissions || [];
	}
	has(permission) {
		return this.all().includes(permission) || this.all().includes("super");
	}
};
//#endregion
//#region resources/js/components/FormattingLocale.js
var defaultLocale = null;
function normalizeLocale(locale) {
	return typeof locale === "string" ? locale.replace("_", "-") : locale;
}
function getLocale() {
	return defaultLocale ?? Intl.DateTimeFormat().resolvedOptions().locale;
}
function getDefaultLocale() {
	return defaultLocale;
}
function setDefaultLocale(locale) {
	defaultLocale = normalizeLocale(locale);
}
//#endregion
//#region resources/js/components/DateFormatter.js
var DateFormatter = class DateFormatter {
	#date;
	#options;
	#presets = {
		datetime: {
			year: "numeric",
			month: "numeric",
			day: "numeric",
			hour: "numeric",
			minute: "numeric"
		},
		date: {
			year: "numeric",
			month: "numeric",
			day: "numeric"
		},
		time: { timeStyle: "short" }
	};
	constructor(date, options) {
		this.#date = this.#normalizeDate(date);
		this.#options = this.#normalizeOptions(options);
	}
	date(value) {
		return new DateFormatter(value, this.#options);
	}
	options(options) {
		return new DateFormatter(this.#date, options);
	}
	toString() {
		try {
			if (this.#options.relative) return this.#formatRelative();
			return Intl.DateTimeFormat(this.locale, this.#options).format(this.#date);
		} catch (e) {
			return "Invalid Date";
		}
	}
	#formatRelative() {
		let specificity = this.#options.relative;
		specificity = !specificity || specificity === true ? "year" : specificity;
		const diff = /* @__PURE__ */ new Date() - this.#date;
		const seconds = Math.abs(Math.floor(diff / 1e3));
		const minutes = Math.abs(Math.floor(seconds / 60));
		const hours = Math.abs(Math.floor(minutes / 60));
		const days = Math.abs(Math.floor(hours / 24));
		const weeks = Math.abs(Math.floor(days / 7));
		const months = Math.abs(Math.floor(days / 30));
		const years = Math.abs(Math.floor(days / 365));
		const rtf = new Intl.RelativeTimeFormat(this.locale, { numeric: "auto" });
		if (seconds < 60) return rtf.format(-Math.sign(diff) * seconds, "second");
		if (minutes < 60 && [
			"minute",
			"hour",
			"day",
			"week",
			"month",
			"year"
		].includes(specificity)) return rtf.format(-Math.sign(diff) * minutes, "minute");
		if (hours < 24 && [
			"hour",
			"day",
			"week",
			"month",
			"year"
		].includes(specificity)) return rtf.format(-Math.sign(diff) * hours, "hour");
		if (days < 7 && [
			"day",
			"week",
			"month",
			"year"
		].includes(specificity)) return rtf.format(-Math.sign(diff) * days, "day");
		if (weeks < 4 && [
			"week",
			"month",
			"year"
		].includes(specificity)) return rtf.format(-Math.sign(diff) * weeks, "week");
		if (months < 12 && ["month", "year"].includes(specificity)) return rtf.format(-Math.sign(diff) * months, "month");
		if (specificity === "year") return rtf.format(-Math.sign(diff) * years, "year");
		return new DateFormatter(this.#date, this.#options.fallback || "datetime").toString();
	}
	static format(date, options) {
		return new DateFormatter(date, options).toString();
	}
	format(date, options) {
		return this.date(date).options(options).toString();
	}
	static get defaultLocale() {
		return getDefaultLocale();
	}
	static set defaultLocale(locale) {
		setDefaultLocale(locale);
	}
	withLocale(locale, callback) {
		const previousLocale = getDefaultLocale();
		setDefaultLocale(locale);
		try {
			return callback(this);
		} finally {
			setDefaultLocale(previousLocale);
		}
	}
	setDefaultLocale(locale) {
		setDefaultLocale(locale);
	}
	get locale() {
		return getLocale();
	}
	#normalizeDate(date) {
		if (!date || date === "now") return Date.now();
		if (date instanceof Date) return date;
		return new Date(date);
	}
	#normalizeOptions(options) {
		if (!options) options = "datetime";
		if (typeof options === "string") {
			if (!this.#presets[options]) throw new Error(`Invalid date format: ${options}`);
			return this.#presets[options];
		}
		if (options.preset) {
			const { preset, ...overrides } = options;
			if (!this.#presets[preset]) throw new Error(`Invalid date format: ${preset}`);
			return {
				...this.#presets[preset],
				...overrides
			};
		}
		return options;
	}
};
//#endregion
//#region resources/js/components/NumberFormatter.js
var NumberFormatter = class NumberFormatter {
	#number;
	#rangeEnd;
	#options;
	#presets = {
		decimal: { style: "decimal" },
		percent: { style: "percent" }
	};
	constructor(number, options) {
		if (Array.isArray(number)) {
			this.#number = this.#normalizeNumber(number[0]);
			this.#rangeEnd = this.#normalizeNumber(number[1]);
		} else this.#number = this.#normalizeNumber(number);
		this.#options = this.#normalizeOptions(options);
	}
	number(value) {
		return new NumberFormatter(value, this.#options);
	}
	options(options) {
		return new NumberFormatter(this.#rangeEnd !== void 0 ? [this.#number, this.#rangeEnd] : this.#number, options);
	}
	toString() {
		try {
			const formatter = Intl.NumberFormat(this.locale, this.#options);
			if (this.#rangeEnd !== void 0 && this.#rangeEnd !== this.#number) return formatter.formatRange(this.#number, this.#rangeEnd);
			return formatter.format(this.#number);
		} catch (e) {
			return "Invalid Number";
		}
	}
	static format(number, options) {
		return new NumberFormatter(number, options).toString();
	}
	format(number, options) {
		return this.number(number).options(options).toString();
	}
	formatRange(start, end, options) {
		return this.number([start, end]).options(options ?? this.#options).toString();
	}
	static formatRange(start, end, options) {
		return new NumberFormatter([start, end], options).toString();
	}
	static get defaultLocale() {
		return getDefaultLocale();
	}
	static set defaultLocale(locale) {
		setDefaultLocale(locale);
	}
	withLocale(locale, callback) {
		const previousLocale = getDefaultLocale();
		setDefaultLocale(locale);
		try {
			return callback(this);
		} finally {
			setDefaultLocale(previousLocale);
		}
	}
	setDefaultLocale(locale) {
		setDefaultLocale(locale);
	}
	get locale() {
		return getLocale();
	}
	#normalizeNumber(number) {
		if (number === null || number === void 0) return 0;
		const n = Number(number);
		if (Number.isNaN(n)) throw new Error("Invalid Number");
		return n;
	}
	#normalizeOptions(options) {
		if (!options) options = "decimal";
		if (typeof options === "string") {
			if (!this.#presets[options]) throw new Error(`Invalid number format: ${options}`);
			return this.#presets[options];
		}
		return options;
	}
};
//#endregion
//#region resources/js/components/command-palette/Constants.js
var CATEGORY = {
	Actions: "Actions",
	Miscellaneous: "Miscellaneous"
};
//#endregion
//#region resources/js/components/CommandPalette.js
var commands = ref({});
var Command = class {
	constructor(command) {
		this.key = nanoid();
		this.category = command.category ?? "Miscellaneous";
		this.icon = command.icon ?? "wand";
		this.when = command.when ?? (() => true);
		this.text = command.text;
		this.url = command.url;
		this.openNewTab = command.openNewTab ?? false;
		this.action = command.action;
		this.keys = command.keys;
		this.prioritize = command.prioritize ?? false;
		this.trackRecent = command.trackRecent ?? false;
		this.persist = command.persist ?? false;
		this.#validate();
	}
	remove() {
		delete commands.value[this.key];
	}
	#validate() {
		if (!(typeof this.text === "string" || Array.isArray(this.text))) console.error("You must provide a `text:` string in your command object");
		if (typeof this.url !== "string" && typeof this.action !== "function") console.error("You must provide a `url` string or `action` function to be run with your `" + this.text + "` command");
	}
};
var CommandPalette = class {
	#preventIfCallbacks = [];
	get category() {
		return CATEGORY;
	}
	preventIf(fn) {
		this.#preventIfCallbacks.push(fn);
	}
	shouldPreventOpening() {
		return this.#preventIfCallbacks.some((fn) => fn());
	}
	add(command) {
		command = new Command(command);
		commands.value[command.key] = command;
		return command;
	}
	clear() {
		commands.value = Object.fromEntries(Object.entries(commands.value).filter(([key, command]) => command.persist));
	}
	actions() {
		return Object.values(commands.value).filter((command) => command.category === "Actions");
	}
	misc() {
		return Object.values(commands.value).filter((command) => command.category === "Miscellaneous");
	}
};
//#endregion
//#region resources/js/components/ColorMode.js
var ColorMode = class {
	#preference;
	#mode = ref(null);
	initialize(preference) {
		this.#preference = ref(preference);
		this.#setMode(preference);
		this.#watchPreferences();
		this.#watchMode();
		this.#listenForColorSchemeChange();
		this.#registerCommands();
	}
	get mode() {
		return this.#mode;
	}
	get preference() {
		return this.#preference;
	}
	set preference(value) {
		this.#preference.value = value;
	}
	#watchPreferences() {
		watch(this.#preference, (preference) => {
			this.#setMode(preference);
			this.#savePreference(preference);
		});
	}
	#watchMode() {
		watch(this.#mode, (mode) => {
			document.documentElement.classList.toggle("dark", mode === "dark");
		}, { immediate: true });
	}
	#listenForColorSchemeChange() {
		window.matchMedia("(prefers-color-scheme: dark)").addEventListener("change", (e) => {
			if (this.#preference.value === "auto") this.#mode.value = e.matches ? "dark" : "light";
		});
		window.addEventListener("storage", (e) => {
			if (e.key === "statamic.color_mode") this.#mode.value = e.newValue;
		});
	}
	#setMode(preference) {
		this.#mode.value = preference === "dark" || preference === "auto" && window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
	}
	#savePreference(preference) {
		if (preference === "auto") {
			if (Statamic.$config.get("user") && Statamic.$preferences.has("color_mode")) Statamic.$preferences.remove("color_mode");
			localStorage.removeItem("statamic.color_mode");
		} else {
			if (Statamic.$config.get("user") && Statamic.$preferences.get("color_mode") !== preference) Statamic.$preferences.set("color_mode", preference);
			localStorage.setItem("statamic.color_mode", preference);
		}
	}
	#registerCommands() {
		Statamic.$commandPalette.add({
			text: [__("Toggle Color Mode"), __("Light")],
			icon: "sun",
			action: () => {
				this.preference = "light";
			},
			persist: true
		});
		Statamic.$commandPalette.add({
			text: [__("Toggle Color Mode"), __("Dark")],
			icon: "moon",
			action: () => {
				this.preference = "dark";
			},
			persist: true
		});
		Statamic.$commandPalette.add({
			text: [__("Toggle Color Mode"), __("System")],
			icon: "monitor",
			action: () => {
				this.preference = "auto";
			},
			persist: true
		});
	}
};
//#endregion
//#region resources/js/components/Contrast.js
var Contrast = class {
	#preference;
	#contrast = ref(null);
	initialize(preference) {
		this.#preference = ref(preference ? "increased" : "auto");
		this.#setContrast(this.#preference.value);
		this.#watchContrast();
		this.#listenForColorSchemeChange();
	}
	#watchContrast() {
		watch(this.#contrast, (contrast) => document.body.setAttribute("data-contrast", contrast), { immediate: true });
	}
	#setContrast(preference) {
		this.#contrast.value = preference === "increased" || preference === "auto" && window.matchMedia("(prefers-contrast: more)").matches ? "increased" : "default";
	}
	#listenForColorSchemeChange() {
		window.matchMedia("(prefers-contrast: more)").addEventListener("change", (e) => {
			if (this.#preference.value === "auto") this.#contrast.value = e.matches ? "increased" : "default";
		});
	}
};
//#endregion
//#region resources/js/components/Config.js
var Config = class {
	config = ref({});
	initialize(initialConfig) {
		this.config = ref(initialConfig);
	}
	all() {
		return this.config.value;
	}
	get(key, fallback) {
		return data_get$1(this.all(), key, fallback);
	}
	set(key, value) {
		this.config.value[key] = value;
	}
};
//#endregion
//#region resources/js/components/Preference.js
var Preference = class {
	#url;
	#preferences;
	#defaults;
	initialize(preferences, defaults) {
		this.#url = cp_url("preferences/js");
		this.#preferences = ref(preferences);
		this.#defaults = defaults;
	}
	all() {
		return this.#preferences.value;
	}
	get(key, fallback) {
		return data_get(this.all(), key, fallback);
	}
	set(key, value) {
		return this.commitOnSuccessAndReturnPromise(axios.post(this.#url, {
			key,
			value
		}));
	}
	append(key, value) {
		return this.commitOnSuccessAndReturnPromise(axios.post(this.#url, {
			key,
			value,
			append: true
		}));
	}
	has(key) {
		return this.all().hasOwnProperty(key);
	}
	remove(key, value = null, cleanup = true) {
		return this.commitOnSuccessAndReturnPromise(axios.delete(`${this.#url}/${key}`, { data: {
			value,
			cleanup
		} }));
	}
	removeValue(key, value) {
		return this.remove(key, value);
	}
	commitOnSuccessAndReturnPromise(promise) {
		promise.then((response) => {
			this.#preferences.value = response.data;
		});
		return promise;
	}
	defaults() {
		return this.#defaults;
	}
	getDefault(key, fallback) {
		return data_get(this.defaults(), key, fallback);
	}
	hasDefault(key) {
		return this.getDefault(key) !== null;
	}
};
//#endregion
//#region resources/js/components/Toasts.js
var Toasts = class {
	#app;
	#plugin;
	initialize(app) {
		this.#app = app;
	}
	registerInterceptor(axios) {
		axios.interceptors.response.use((response) => {
			const data = response?.data;
			if (!data) return response;
			(data instanceof Blob ? data.text().then((text) => JSON.parse(text)) : new Promise((resolve) => resolve(data))).then((json) => this.#displayToasts(json._toasts ?? []));
			return response;
		});
		router.on("success", (event) => {
			const toasts = event.detail.page.props._toasts;
			if (toasts && Array.isArray(toasts)) this.#displayToasts(toasts);
		});
	}
	displayInitialToasts() {
		this.#displayToasts(Statamic.$config.get("flash"));
		this.#displayToasts(Statamic.$config.get("toasts"));
	}
	#displayToasts(toasts) {
		toasts.forEach(({ type, message, duration }) => this[type](message, { duration }));
	}
	#normalizeToastOptions(opts) {
		if (!opts.duration) delete opts.duration;
		return opts;
	}
	async #toasted() {
		return new Promise(async (resolve) => {
			if (!this.#plugin) {
				const { default: Toasted, useToasted } = await __vitePreload(async () => {
					const { default: Toasted, useToasted } = await import("./vue-toasted-DssQX94s.js");
					return {
						default: Toasted,
						useToasted
					};
				}, [], import.meta.url);
				__vitePreload(() => Promise.resolve({}), __vite__mapDeps([2]), import.meta.url);
				this.#app.use(Toasted, {
					position: "bottom-left",
					duration: 3500,
					theme: "statamic",
					action: {
						text: "×",
						onClick: (e, toastObject) => {
							toastObject.goAway(0);
						}
					}
				});
				this.#plugin = useToasted();
			}
			resolve(this.#plugin);
		});
	}
	async success(message, opts) {
		(await this.#toasted()).success(message, this.#normalizeToastOptions({
			iconPack: "callback",
			icon: (el) => {
				el.innerHTML = "<svg viewBox=\"0 0 24 24\" height=\"12\" width=\"12\"><g transform=\"matrix(1,0,0,1,0,0)\"><path d=\"M23.146,5.4l-2.792-2.8c-0.195-0.196-0.512-0.196-0.707-0.001c0,0-0.001,0.001-0.001,0.001L7.854,14.4 c-0.195,0.196-0.512,0.196-0.707,0.001c0,0-0.001-0.001-0.001-0.001l-2.792-2.8c-0.195-0.196-0.512-0.196-0.707-0.001 c0,0-0.001,0.001-0.001,0.001l-2.792,2.8c-0.195,0.195-0.195,0.512,0,0.707L7.146,21.4c0.195,0.196,0.512,0.196,0.707,0.001 c0,0,0.001-0.001,0.001-0.001L23.146,6.1C23.337,5.906,23.337,5.594,23.146,5.4z\" stroke=\"none\" fill=\"currentColor\" stroke-width=\"0\" stroke-linecap=\"round\" stroke-linejoin=\"round\"></path></g></svg>";
				return el;
			},
			...opts
		}));
	}
	async info(message, opts) {
		(await this.#toasted()).show(message, this.#normalizeToastOptions({
			iconPack: "callback",
			icon: (el) => {
				el.innerHTML = "<svg viewBox=\"0 0 24 24\" width=\"24\" height=\"24\"><g transform=\"matrix(1,0,0,1,0,0)\"><path d=\"M 14.25,16.5H13.5c-0.828,0-1.5-0.672-1.5-1.5v-3.75c0-0.414-0.336-0.75-0.75-0.75H10.5 \" stroke=\"currentColor\" fill=\"none\" stroke-width=\"1.5\" stroke-linecap=\"round\" stroke-linejoin=\"round\"></path><path d=\"M 11.625,6.75 c-0.207,0-0.375,0.168-0.375,0.375S11.418,7.5,11.625,7.5S12,7.332,12,7.125S11.832,6.75,11.625,6.75L11.625,6.75 \" stroke=\"currentColor\" fill=\"none\" stroke-width=\"1.5\" stroke-linecap=\"round\" stroke-linejoin=\"round\"></path><path d=\"M 12,0.75 c6.213,0,11.25,5.037,11.25,11.25S18.213,23.25,12,23.25S0.75,18.213,0.75,12S5.787,0.75,12,0.75z\" stroke=\"currentColor\" fill=\"none\" stroke-width=\"1.5\" stroke-linecap=\"round\" stroke-linejoin=\"round\"></path></g></svg>";
				return el;
			},
			...opts
		}));
	}
	async error(message, opts) {
		(await this.#toasted()).error(message, this.#normalizeToastOptions({
			iconPack: "callback",
			icon: (el) => {
				el.innerHTML = "<svg viewBox=\"0 0 24 24\" height=\"18\" width=\"18\"><g transform=\"matrix(1,0,0,1,0,0)\"><path d=\"M11.983,0C8.777,0.052,5.72,1.365,3.473,3.653C1.202,5.914-0.052,9.002,0,12.207C-0.008,18.712,5.26,23.992,11.765,24 c0.012,0,0.023,0,0.035,0h0.214c6.678-0.069,12.04-5.531,11.986-12.209l0,0c0.015-6.498-5.24-11.778-11.738-11.794 C12.169-0.003,12.076-0.002,11.983,0z M10.5,16.542c-0.03-0.815,0.606-1.499,1.421-1.529c0.009,0,0.019-0.001,0.028-0.001h0.027 c0.82,0.002,1.492,0.651,1.523,1.47c0.03,0.814-0.605,1.499-1.419,1.529c-0.01,0-0.02,0.001-0.03,0.001h-0.027 C11.203,18.009,10.532,17.361,10.5,16.542z M11,12.5v-6c0-0.552,0.448-1,1-1s1,0.448,1,1v6c0,0.552-0.448,1-1,1S11,13.052,11,12.5z\" stroke=\"none\" fill=\"currentColor\" stroke-width=\"0\" stroke-linecap=\"round\" stroke-linejoin=\"round\"></path></g></svg>";
				return el;
			},
			...opts
		}));
	}
};
//#endregion
//#region resources/js/components/portals/Portal.js
var Portal = class {
	constructor(portals, name, data = {}) {
		this.portals = portals;
		this.id = `${name}-${nanoid()}`;
		this.data = data;
	}
	destroy() {
		this.portals.destroy(this.id);
	}
};
//#endregion
//#region resources/js/components/portals/Portals.js
var Portals = class {
	constructor() {
		this.portals = ref([]);
	}
	all() {
		return this.portals.value;
	}
	create(name, data = {}) {
		let portal = new Portal(this, name, data);
		this.portals.value.push(portal);
		return portal;
	}
	destroy(id) {
		const i = this.portals.value.findIndex((portal) => portal.id === id);
		if (i !== -1) this.portals.value.splice(i, 1);
	}
	stacks() {
		return this.portals.value.filter((portal) => portal.data?.type === "stack");
	}
};
//#endregion
//#region resources/js/components/ui/Stack/Stacks.js
var Stacks = class {
	constructor(portals) {
		this.$portals = portals;
	}
	count() {
		return this.stacks().length;
	}
	add(vm) {
		return this.$portals.create("stack", {
			type: "stack",
			vm
		});
	}
	stacks() {
		return this.$portals.stacks();
	}
};
//#endregion
//#region resources/js/components/Inertia.ts
var Inertia = class {
	components = {};
	register(name, component) {
		this.components[name] = component;
	}
	get(name) {
		return this.components[name];
	}
};
//#endregion
//#region resources/js/api.js
var keys = new Keys();
var components = new Components();
var events = useGlobalEventBus();
var progress = useProgressBar();
var fieldActions = new FieldActions();
var conditions = new FieldConditions();
var callbacks = new Callbacks();
var dirty = useDirtyState();
var slug = new Manager();
var hooks = new Hooks();
var bard = new Bard();
var reveal = new Reveal();
var echo = new Echo();
var permissions = new Permission();
var dateFormatter = new DateFormatter();
var numberFormatter = new NumberFormatter();
var commandPalette = new CommandPalette();
var colorMode = new ColorMode();
var contrast = new Contrast();
var config = new Config();
var preferences = new Preference();
var toast = new Toasts();
var portals = markRaw(new Portals());
var stacks = new Stacks(portals);
var inertia = new Inertia();
//#endregion
export { require_tiny_emitter as A, slug as C, DateFormatter as D, NumberFormatter as E, debounce_default as M, normalizeLocale as O, reveal as S, toast as T, numberFormatter as _, components as a, preferences as b, contrast as c, echo as d, events as f, keys as g, inertia as h, commandPalette as i, Component as j, setDefaultLocale as k, dateFormatter as l, hooks as m, callbacks as n, conditions as o, fieldActions as p, colorMode as r, config as s, bard as t, dirty as u, permissions as v, stacks as w, progress as x, portals as y };
