//#region node_modules/laravel-echo/dist/echo.js
function _typeof(obj) {
	"@babel/helpers - typeof";
	return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function(obj) {
		return typeof obj;
	} : function(obj) {
		return obj && "function" == typeof Symbol && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj;
	}, _typeof(obj);
}
function _classCallCheck(instance, Constructor) {
	if (!(instance instanceof Constructor)) throw new TypeError("Cannot call a class as a function");
}
function _defineProperties(target, props) {
	for (var i = 0; i < props.length; i++) {
		var descriptor = props[i];
		descriptor.enumerable = descriptor.enumerable || false;
		descriptor.configurable = true;
		if ("value" in descriptor) descriptor.writable = true;
		Object.defineProperty(target, descriptor.key, descriptor);
	}
}
function _createClass(Constructor, protoProps, staticProps) {
	if (protoProps) _defineProperties(Constructor.prototype, protoProps);
	if (staticProps) _defineProperties(Constructor, staticProps);
	Object.defineProperty(Constructor, "prototype", { writable: false });
	return Constructor;
}
function _extends() {
	_extends = Object.assign || function(target) {
		for (var i = 1; i < arguments.length; i++) {
			var source = arguments[i];
			for (var key in source) if (Object.prototype.hasOwnProperty.call(source, key)) target[key] = source[key];
		}
		return target;
	};
	return _extends.apply(this, arguments);
}
function _inherits(subClass, superClass) {
	if (typeof superClass !== "function" && superClass !== null) throw new TypeError("Super expression must either be null or a function");
	subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: {
		value: subClass,
		writable: true,
		configurable: true
	} });
	Object.defineProperty(subClass, "prototype", { writable: false });
	if (superClass) _setPrototypeOf(subClass, superClass);
}
function _getPrototypeOf(o) {
	_getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) {
		return o.__proto__ || Object.getPrototypeOf(o);
	};
	return _getPrototypeOf(o);
}
function _setPrototypeOf(o, p) {
	_setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) {
		o.__proto__ = p;
		return o;
	};
	return _setPrototypeOf(o, p);
}
function _isNativeReflectConstruct() {
	if (typeof Reflect === "undefined" || !Reflect.construct) return false;
	if (Reflect.construct.sham) return false;
	if (typeof Proxy === "function") return true;
	try {
		Boolean.prototype.valueOf.call(Reflect.construct(Boolean, [], function() {}));
		return true;
	} catch (e) {
		return false;
	}
}
function _assertThisInitialized(self) {
	if (self === void 0) throw new ReferenceError("this hasn't been initialised - super() hasn't been called");
	return self;
}
function _possibleConstructorReturn(self, call) {
	if (call && (typeof call === "object" || typeof call === "function")) return call;
	else if (call !== void 0) throw new TypeError("Derived constructors may only return object or undefined");
	return _assertThisInitialized(self);
}
function _createSuper(Derived) {
	var hasNativeReflectConstruct = _isNativeReflectConstruct();
	return function _createSuperInternal() {
		var Super = _getPrototypeOf(Derived), result;
		if (hasNativeReflectConstruct) {
			var NewTarget = _getPrototypeOf(this).constructor;
			result = Reflect.construct(Super, arguments, NewTarget);
		} else result = Super.apply(this, arguments);
		return _possibleConstructorReturn(this, result);
	};
}
/**
* This class represents a basic channel.
*/
var Channel = /*#__PURE__*/ function() {
	function Channel() {
		_classCallCheck(this, Channel);
	}
	_createClass(Channel, [
		{
			key: "listenForWhisper",
			value: function listenForWhisper(event, callback) {
				return this.listen(".client-" + event, callback);
			}
		},
		{
			key: "notification",
			value: function notification(callback) {
				return this.listen(".Illuminate\\Notifications\\Events\\BroadcastNotificationCreated", callback);
			}
		},
		{
			key: "stopListeningForWhisper",
			value: function stopListeningForWhisper(event, callback) {
				return this.stopListening(".client-" + event, callback);
			}
		}
	]);
	return Channel;
}();
/**
* Event name formatter
*/
var EventFormatter = /*#__PURE__*/ function() {
	/**
	* Create a new class instance.
	*/
	function EventFormatter(namespace) {
		_classCallCheck(this, EventFormatter);
		this.namespace = namespace;
	}
	/**
	* Format the given event name.
	*/
	_createClass(EventFormatter, [{
		key: "format",
		value: function format(event) {
			if ([".", "\\"].includes(event.charAt(0))) return event.substring(1);
			else if (this.namespace) event = this.namespace + "." + event;
			return event.replace(/\./g, "\\");
		}
	}, {
		key: "setNamespace",
		value: function setNamespace(value) {
			this.namespace = value;
		}
	}]);
	return EventFormatter;
}();
function isConstructor(obj) {
	try {
		new obj();
	} catch (err) {
		if (err.message.includes("is not a constructor")) return false;
	}
	return true;
}
/**
* This class represents a Pusher channel.
*/
var PusherChannel = /*#__PURE__*/ function(_Channel) {
	_inherits(PusherChannel, _Channel);
	var _super = _createSuper(PusherChannel);
	/**
	* Create a new class instance.
	*/
	function PusherChannel(pusher, name, options) {
		var _this;
		_classCallCheck(this, PusherChannel);
		_this = _super.call(this);
		_this.name = name;
		_this.pusher = pusher;
		_this.options = options;
		_this.eventFormatter = new EventFormatter(_this.options.namespace);
		_this.subscribe();
		return _this;
	}
	/**
	* Subscribe to a Pusher channel.
	*/
	_createClass(PusherChannel, [
		{
			key: "subscribe",
			value: function subscribe() {
				this.subscription = this.pusher.subscribe(this.name);
			}
		},
		{
			key: "unsubscribe",
			value: function unsubscribe() {
				this.pusher.unsubscribe(this.name);
			}
		},
		{
			key: "listen",
			value: function listen(event, callback) {
				this.on(this.eventFormatter.format(event), callback);
				return this;
			}
		},
		{
			key: "listenToAll",
			value: function listenToAll(callback) {
				var _this2 = this;
				this.subscription.bind_global(function(event, data) {
					if (event.startsWith("pusher:")) return;
					var namespace = _this2.options.namespace.replace(/\./g, "\\");
					callback(event.startsWith(namespace) ? event.substring(namespace.length + 1) : "." + event, data);
				});
				return this;
			}
		},
		{
			key: "stopListening",
			value: function stopListening(event, callback) {
				if (callback) this.subscription.unbind(this.eventFormatter.format(event), callback);
				else this.subscription.unbind(this.eventFormatter.format(event));
				return this;
			}
		},
		{
			key: "stopListeningToAll",
			value: function stopListeningToAll(callback) {
				if (callback) this.subscription.unbind_global(callback);
				else this.subscription.unbind_global();
				return this;
			}
		},
		{
			key: "subscribed",
			value: function subscribed(callback) {
				this.on("pusher:subscription_succeeded", function() {
					callback();
				});
				return this;
			}
		},
		{
			key: "error",
			value: function error(callback) {
				this.on("pusher:subscription_error", function(status) {
					callback(status);
				});
				return this;
			}
		},
		{
			key: "on",
			value: function on(event, callback) {
				this.subscription.bind(event, callback);
				return this;
			}
		}
	]);
	return PusherChannel;
}(Channel);
/**
* This class represents a Pusher private channel.
*/
var PusherPrivateChannel = /*#__PURE__*/ function(_PusherChannel) {
	_inherits(PusherPrivateChannel, _PusherChannel);
	var _super = _createSuper(PusherPrivateChannel);
	function PusherPrivateChannel() {
		_classCallCheck(this, PusherPrivateChannel);
		return _super.apply(this, arguments);
	}
	_createClass(PusherPrivateChannel, [{
		key: "whisper",
		value: function whisper(eventName, data) {
			this.pusher.channels.channels[this.name].trigger("client-".concat(eventName), data);
			return this;
		}
	}]);
	return PusherPrivateChannel;
}(PusherChannel);
/**
* This class represents a Pusher private channel.
*/
var PusherEncryptedPrivateChannel = /*#__PURE__*/ function(_PusherChannel) {
	_inherits(PusherEncryptedPrivateChannel, _PusherChannel);
	var _super = _createSuper(PusherEncryptedPrivateChannel);
	function PusherEncryptedPrivateChannel() {
		_classCallCheck(this, PusherEncryptedPrivateChannel);
		return _super.apply(this, arguments);
	}
	_createClass(PusherEncryptedPrivateChannel, [{
		key: "whisper",
		value: function whisper(eventName, data) {
			this.pusher.channels.channels[this.name].trigger("client-".concat(eventName), data);
			return this;
		}
	}]);
	return PusherEncryptedPrivateChannel;
}(PusherChannel);
/**
* This class represents a Pusher presence channel.
*/
var PusherPresenceChannel = /*#__PURE__*/ function(_PusherPrivateChannel) {
	_inherits(PusherPresenceChannel, _PusherPrivateChannel);
	var _super = _createSuper(PusherPresenceChannel);
	function PusherPresenceChannel() {
		_classCallCheck(this, PusherPresenceChannel);
		return _super.apply(this, arguments);
	}
	_createClass(PusherPresenceChannel, [
		{
			key: "here",
			value: function here(callback) {
				this.on("pusher:subscription_succeeded", function(data) {
					callback(Object.keys(data.members).map(function(k) {
						return data.members[k];
					}));
				});
				return this;
			}
		},
		{
			key: "joining",
			value: function joining(callback) {
				this.on("pusher:member_added", function(member) {
					callback(member.info);
				});
				return this;
			}
		},
		{
			key: "whisper",
			value: function whisper(eventName, data) {
				this.pusher.channels.channels[this.name].trigger("client-".concat(eventName), data);
				return this;
			}
		},
		{
			key: "leaving",
			value: function leaving(callback) {
				this.on("pusher:member_removed", function(member) {
					callback(member.info);
				});
				return this;
			}
		}
	]);
	return PusherPresenceChannel;
}(PusherPrivateChannel);
/**
* This class represents a Socket.io channel.
*/
var SocketIoChannel = /*#__PURE__*/ function(_Channel) {
	_inherits(SocketIoChannel, _Channel);
	var _super = _createSuper(SocketIoChannel);
	/**
	* Create a new class instance.
	*/
	function SocketIoChannel(socket, name, options) {
		var _this;
		_classCallCheck(this, SocketIoChannel);
		_this = _super.call(this);
		/**
		* The event callbacks applied to the socket.
		*/
		_this.events = {};
		/**
		* User supplied callbacks for events on this channel.
		*/
		_this.listeners = {};
		_this.name = name;
		_this.socket = socket;
		_this.options = options;
		_this.eventFormatter = new EventFormatter(_this.options.namespace);
		_this.subscribe();
		return _this;
	}
	/**
	* Subscribe to a Socket.io channel.
	*/
	_createClass(SocketIoChannel, [
		{
			key: "subscribe",
			value: function subscribe() {
				this.socket.emit("subscribe", {
					channel: this.name,
					auth: this.options.auth || {}
				});
			}
		},
		{
			key: "unsubscribe",
			value: function unsubscribe() {
				this.unbind();
				this.socket.emit("unsubscribe", {
					channel: this.name,
					auth: this.options.auth || {}
				});
			}
		},
		{
			key: "listen",
			value: function listen(event, callback) {
				this.on(this.eventFormatter.format(event), callback);
				return this;
			}
		},
		{
			key: "stopListening",
			value: function stopListening(event, callback) {
				this.unbindEvent(this.eventFormatter.format(event), callback);
				return this;
			}
		},
		{
			key: "subscribed",
			value: function subscribed(callback) {
				this.on("connect", function(socket) {
					callback(socket);
				});
				return this;
			}
		},
		{
			key: "error",
			value: function error(callback) {
				return this;
			}
		},
		{
			key: "on",
			value: function on(event, callback) {
				var _this2 = this;
				this.listeners[event] = this.listeners[event] || [];
				if (!this.events[event]) {
					this.events[event] = function(channel, data) {
						if (_this2.name === channel && _this2.listeners[event]) _this2.listeners[event].forEach(function(cb) {
							return cb(data);
						});
					};
					this.socket.on(event, this.events[event]);
				}
				this.listeners[event].push(callback);
				return this;
			}
		},
		{
			key: "unbind",
			value: function unbind() {
				var _this3 = this;
				Object.keys(this.events).forEach(function(event) {
					_this3.unbindEvent(event);
				});
			}
		},
		{
			key: "unbindEvent",
			value: function unbindEvent(event, callback) {
				this.listeners[event] = this.listeners[event] || [];
				if (callback) this.listeners[event] = this.listeners[event].filter(function(cb) {
					return cb !== callback;
				});
				if (!callback || this.listeners[event].length === 0) {
					if (this.events[event]) {
						this.socket.removeListener(event, this.events[event]);
						delete this.events[event];
					}
					delete this.listeners[event];
				}
			}
		}
	]);
	return SocketIoChannel;
}(Channel);
/**
* This class represents a Socket.io private channel.
*/
var SocketIoPrivateChannel = /*#__PURE__*/ function(_SocketIoChannel) {
	_inherits(SocketIoPrivateChannel, _SocketIoChannel);
	var _super = _createSuper(SocketIoPrivateChannel);
	function SocketIoPrivateChannel() {
		_classCallCheck(this, SocketIoPrivateChannel);
		return _super.apply(this, arguments);
	}
	_createClass(SocketIoPrivateChannel, [{
		key: "whisper",
		value: function whisper(eventName, data) {
			this.socket.emit("client event", {
				channel: this.name,
				event: "client-".concat(eventName),
				data
			});
			return this;
		}
	}]);
	return SocketIoPrivateChannel;
}(SocketIoChannel);
/**
* This class represents a Socket.io presence channel.
*/
var SocketIoPresenceChannel = /*#__PURE__*/ function(_SocketIoPrivateChann) {
	_inherits(SocketIoPresenceChannel, _SocketIoPrivateChann);
	var _super = _createSuper(SocketIoPresenceChannel);
	function SocketIoPresenceChannel() {
		_classCallCheck(this, SocketIoPresenceChannel);
		return _super.apply(this, arguments);
	}
	_createClass(SocketIoPresenceChannel, [
		{
			key: "here",
			value: function here(callback) {
				this.on("presence:subscribed", function(members) {
					callback(members.map(function(m) {
						return m.user_info;
					}));
				});
				return this;
			}
		},
		{
			key: "joining",
			value: function joining(callback) {
				this.on("presence:joining", function(member) {
					return callback(member.user_info);
				});
				return this;
			}
		},
		{
			key: "whisper",
			value: function whisper(eventName, data) {
				this.socket.emit("client event", {
					channel: this.name,
					event: "client-".concat(eventName),
					data
				});
				return this;
			}
		},
		{
			key: "leaving",
			value: function leaving(callback) {
				this.on("presence:leaving", function(member) {
					return callback(member.user_info);
				});
				return this;
			}
		}
	]);
	return SocketIoPresenceChannel;
}(SocketIoPrivateChannel);
/**
* This class represents a null channel.
*/
var NullChannel = /*#__PURE__*/ function(_Channel) {
	_inherits(NullChannel, _Channel);
	var _super = _createSuper(NullChannel);
	function NullChannel() {
		_classCallCheck(this, NullChannel);
		return _super.apply(this, arguments);
	}
	_createClass(NullChannel, [
		{
			key: "subscribe",
			value: function subscribe() {}
		},
		{
			key: "unsubscribe",
			value: function unsubscribe() {}
		},
		{
			key: "listen",
			value: function listen(event, callback) {
				return this;
			}
		},
		{
			key: "listenToAll",
			value: function listenToAll(callback) {
				return this;
			}
		},
		{
			key: "stopListening",
			value: function stopListening(event, callback) {
				return this;
			}
		},
		{
			key: "subscribed",
			value: function subscribed(callback) {
				return this;
			}
		},
		{
			key: "error",
			value: function error(callback) {
				return this;
			}
		},
		{
			key: "on",
			value: function on(event, callback) {
				return this;
			}
		}
	]);
	return NullChannel;
}(Channel);
/**
* This class represents a null private channel.
*/
var NullPrivateChannel = /*#__PURE__*/ function(_NullChannel) {
	_inherits(NullPrivateChannel, _NullChannel);
	var _super = _createSuper(NullPrivateChannel);
	function NullPrivateChannel() {
		_classCallCheck(this, NullPrivateChannel);
		return _super.apply(this, arguments);
	}
	_createClass(NullPrivateChannel, [{
		key: "whisper",
		value: function whisper(eventName, data) {
			return this;
		}
	}]);
	return NullPrivateChannel;
}(NullChannel);
/**
* This class represents a null private channel.
*/
var NullEncryptedPrivateChannel = /*#__PURE__*/ function(_NullChannel) {
	_inherits(NullEncryptedPrivateChannel, _NullChannel);
	var _super = _createSuper(NullEncryptedPrivateChannel);
	function NullEncryptedPrivateChannel() {
		_classCallCheck(this, NullEncryptedPrivateChannel);
		return _super.apply(this, arguments);
	}
	_createClass(NullEncryptedPrivateChannel, [{
		key: "whisper",
		value: function whisper(eventName, data) {
			return this;
		}
	}]);
	return NullEncryptedPrivateChannel;
}(NullChannel);
/**
* This class represents a null presence channel.
*/
var NullPresenceChannel = /*#__PURE__*/ function(_NullPrivateChannel) {
	_inherits(NullPresenceChannel, _NullPrivateChannel);
	var _super = _createSuper(NullPresenceChannel);
	function NullPresenceChannel() {
		_classCallCheck(this, NullPresenceChannel);
		return _super.apply(this, arguments);
	}
	_createClass(NullPresenceChannel, [
		{
			key: "here",
			value: function here(callback) {
				return this;
			}
		},
		{
			key: "joining",
			value: function joining(callback) {
				return this;
			}
		},
		{
			key: "whisper",
			value: function whisper(eventName, data) {
				return this;
			}
		},
		{
			key: "leaving",
			value: function leaving(callback) {
				return this;
			}
		}
	]);
	return NullPresenceChannel;
}(NullPrivateChannel);
var Connector = /*#__PURE__*/ function() {
	/**
	* Create a new class instance.
	*/
	function Connector(options) {
		_classCallCheck(this, Connector);
		/**
		* Default connector options.
		*/
		this._defaultOptions = {
			auth: { headers: {} },
			authEndpoint: "/broadcasting/auth",
			userAuthentication: {
				endpoint: "/broadcasting/user-auth",
				headers: {}
			},
			broadcaster: "pusher",
			csrfToken: null,
			bearerToken: null,
			host: null,
			key: null,
			namespace: "App.Events"
		};
		this.setOptions(options);
		this.connect();
	}
	/**
	* Merge the custom options with the defaults.
	*/
	_createClass(Connector, [{
		key: "setOptions",
		value: function setOptions(options) {
			this.options = _extends(this._defaultOptions, options);
			var token = this.csrfToken();
			if (token) {
				this.options.auth.headers["X-CSRF-TOKEN"] = token;
				this.options.userAuthentication.headers["X-CSRF-TOKEN"] = token;
			}
			token = this.options.bearerToken;
			if (token) {
				this.options.auth.headers["Authorization"] = "Bearer " + token;
				this.options.userAuthentication.headers["Authorization"] = "Bearer " + token;
			}
			return options;
		}
	}, {
		key: "csrfToken",
		value: function csrfToken() {
			var selector;
			if (typeof window !== "undefined" && window["Laravel"] && window["Laravel"].csrfToken) return window["Laravel"].csrfToken;
			else if (this.options.csrfToken) return this.options.csrfToken;
			else if (typeof document !== "undefined" && typeof document.querySelector === "function" && (selector = document.querySelector("meta[name=\"csrf-token\"]"))) return selector.getAttribute("content");
			return null;
		}
	}]);
	return Connector;
}();
/**
* This class creates a connector to Pusher.
*/
var PusherConnector = /*#__PURE__*/ function(_Connector) {
	_inherits(PusherConnector, _Connector);
	var _super = _createSuper(PusherConnector);
	function PusherConnector() {
		var _this;
		_classCallCheck(this, PusherConnector);
		_this = _super.apply(this, arguments);
		/**
		* All of the subscribed channel names.
		*/
		_this.channels = {};
		return _this;
	}
	/**
	* Create a fresh Pusher connection.
	*/
	_createClass(PusherConnector, [
		{
			key: "connect",
			value: function connect() {
				if (typeof this.options.client !== "undefined") this.pusher = this.options.client;
				else if (this.options.Pusher) this.pusher = new this.options.Pusher(this.options.key, this.options);
				else this.pusher = new Pusher(this.options.key, this.options);
			}
		},
		{
			key: "signin",
			value: function signin() {
				this.pusher.signin();
			}
		},
		{
			key: "listen",
			value: function listen(name, event, callback) {
				return this.channel(name).listen(event, callback);
			}
		},
		{
			key: "channel",
			value: function channel(name) {
				if (!this.channels[name]) this.channels[name] = new PusherChannel(this.pusher, name, this.options);
				return this.channels[name];
			}
		},
		{
			key: "privateChannel",
			value: function privateChannel(name) {
				if (!this.channels["private-" + name]) this.channels["private-" + name] = new PusherPrivateChannel(this.pusher, "private-" + name, this.options);
				return this.channels["private-" + name];
			}
		},
		{
			key: "encryptedPrivateChannel",
			value: function encryptedPrivateChannel(name) {
				if (!this.channels["private-encrypted-" + name]) this.channels["private-encrypted-" + name] = new PusherEncryptedPrivateChannel(this.pusher, "private-encrypted-" + name, this.options);
				return this.channels["private-encrypted-" + name];
			}
		},
		{
			key: "presenceChannel",
			value: function presenceChannel(name) {
				if (!this.channels["presence-" + name]) this.channels["presence-" + name] = new PusherPresenceChannel(this.pusher, "presence-" + name, this.options);
				return this.channels["presence-" + name];
			}
		},
		{
			key: "leave",
			value: function leave(name) {
				var _this2 = this;
				[
					name,
					"private-" + name,
					"private-encrypted-" + name,
					"presence-" + name
				].forEach(function(name, index) {
					_this2.leaveChannel(name);
				});
			}
		},
		{
			key: "leaveChannel",
			value: function leaveChannel(name) {
				if (this.channels[name]) {
					this.channels[name].unsubscribe();
					delete this.channels[name];
				}
			}
		},
		{
			key: "socketId",
			value: function socketId() {
				return this.pusher.connection.socket_id;
			}
		},
		{
			key: "disconnect",
			value: function disconnect() {
				this.pusher.disconnect();
			}
		}
	]);
	return PusherConnector;
}(Connector);
/**
* This class creates a connnector to a Socket.io server.
*/
var SocketIoConnector = /*#__PURE__*/ function(_Connector) {
	_inherits(SocketIoConnector, _Connector);
	var _super = _createSuper(SocketIoConnector);
	function SocketIoConnector() {
		var _this;
		_classCallCheck(this, SocketIoConnector);
		_this = _super.apply(this, arguments);
		/**
		* All of the subscribed channel names.
		*/
		_this.channels = {};
		return _this;
	}
	/**
	* Create a fresh Socket.io connection.
	*/
	_createClass(SocketIoConnector, [
		{
			key: "connect",
			value: function connect() {
				var _this2 = this;
				var io = this.getSocketIO();
				this.socket = io(this.options.host, this.options);
				this.socket.on("reconnect", function() {
					Object.values(_this2.channels).forEach(function(channel) {
						channel.subscribe();
					});
				});
				return this.socket;
			}
		},
		{
			key: "getSocketIO",
			value: function getSocketIO() {
				if (typeof this.options.client !== "undefined") return this.options.client;
				if (typeof io !== "undefined") return io;
				throw new Error("Socket.io client not found. Should be globally available or passed via options.client");
			}
		},
		{
			key: "listen",
			value: function listen(name, event, callback) {
				return this.channel(name).listen(event, callback);
			}
		},
		{
			key: "channel",
			value: function channel(name) {
				if (!this.channels[name]) this.channels[name] = new SocketIoChannel(this.socket, name, this.options);
				return this.channels[name];
			}
		},
		{
			key: "privateChannel",
			value: function privateChannel(name) {
				if (!this.channels["private-" + name]) this.channels["private-" + name] = new SocketIoPrivateChannel(this.socket, "private-" + name, this.options);
				return this.channels["private-" + name];
			}
		},
		{
			key: "presenceChannel",
			value: function presenceChannel(name) {
				if (!this.channels["presence-" + name]) this.channels["presence-" + name] = new SocketIoPresenceChannel(this.socket, "presence-" + name, this.options);
				return this.channels["presence-" + name];
			}
		},
		{
			key: "leave",
			value: function leave(name) {
				var _this3 = this;
				[
					name,
					"private-" + name,
					"presence-" + name
				].forEach(function(name) {
					_this3.leaveChannel(name);
				});
			}
		},
		{
			key: "leaveChannel",
			value: function leaveChannel(name) {
				if (this.channels[name]) {
					this.channels[name].unsubscribe();
					delete this.channels[name];
				}
			}
		},
		{
			key: "socketId",
			value: function socketId() {
				return this.socket.id;
			}
		},
		{
			key: "disconnect",
			value: function disconnect() {
				this.socket.disconnect();
			}
		}
	]);
	return SocketIoConnector;
}(Connector);
/**
* This class creates a null connector.
*/
var NullConnector = /*#__PURE__*/ function(_Connector) {
	_inherits(NullConnector, _Connector);
	var _super = _createSuper(NullConnector);
	function NullConnector() {
		var _this;
		_classCallCheck(this, NullConnector);
		_this = _super.apply(this, arguments);
		/**
		* All of the subscribed channel names.
		*/
		_this.channels = {};
		return _this;
	}
	/**
	* Create a fresh connection.
	*/
	_createClass(NullConnector, [
		{
			key: "connect",
			value: function connect() {}
		},
		{
			key: "listen",
			value: function listen(name, event, callback) {
				return new NullChannel();
			}
		},
		{
			key: "channel",
			value: function channel(name) {
				return new NullChannel();
			}
		},
		{
			key: "privateChannel",
			value: function privateChannel(name) {
				return new NullPrivateChannel();
			}
		},
		{
			key: "encryptedPrivateChannel",
			value: function encryptedPrivateChannel(name) {
				return new NullEncryptedPrivateChannel();
			}
		},
		{
			key: "presenceChannel",
			value: function presenceChannel(name) {
				return new NullPresenceChannel();
			}
		},
		{
			key: "leave",
			value: function leave(name) {}
		},
		{
			key: "leaveChannel",
			value: function leaveChannel(name) {}
		},
		{
			key: "socketId",
			value: function socketId() {
				return "fake-socket-id";
			}
		},
		{
			key: "disconnect",
			value: function disconnect() {}
		}
	]);
	return NullConnector;
}(Connector);
/**
* This class is the primary API for interacting with broadcasting.
*/
var Echo = /*#__PURE__*/ function() {
	/**
	* Create a new class instance.
	*/
	function Echo(options) {
		_classCallCheck(this, Echo);
		this.options = options;
		this.connect();
		if (!this.options.withoutInterceptors) this.registerInterceptors();
	}
	/**
	* Get a channel instance by name.
	*/
	_createClass(Echo, [
		{
			key: "channel",
			value: function channel(_channel) {
				return this.connector.channel(_channel);
			}
		},
		{
			key: "connect",
			value: function connect() {
				if (this.options.broadcaster == "reverb") this.connector = new PusherConnector(_extends(_extends({}, this.options), { cluster: "" }));
				else if (this.options.broadcaster == "pusher") this.connector = new PusherConnector(this.options);
				else if (this.options.broadcaster == "socket.io") this.connector = new SocketIoConnector(this.options);
				else if (this.options.broadcaster == "null") this.connector = new NullConnector(this.options);
				else if (typeof this.options.broadcaster == "function" && isConstructor(this.options.broadcaster)) this.connector = new this.options.broadcaster(this.options);
				else throw new Error("Broadcaster ".concat(_typeof(this.options.broadcaster), " ").concat(this.options.broadcaster, " is not supported."));
			}
		},
		{
			key: "disconnect",
			value: function disconnect() {
				this.connector.disconnect();
			}
		},
		{
			key: "join",
			value: function join(channel) {
				return this.connector.presenceChannel(channel);
			}
		},
		{
			key: "leave",
			value: function leave(channel) {
				this.connector.leave(channel);
			}
		},
		{
			key: "leaveChannel",
			value: function leaveChannel(channel) {
				this.connector.leaveChannel(channel);
			}
		},
		{
			key: "leaveAllChannels",
			value: function leaveAllChannels() {
				for (var channel in this.connector.channels) this.leaveChannel(channel);
			}
		},
		{
			key: "listen",
			value: function listen(channel, event, callback) {
				return this.connector.listen(channel, event, callback);
			}
		},
		{
			key: "private",
			value: function _private(channel) {
				return this.connector.privateChannel(channel);
			}
		},
		{
			key: "encryptedPrivate",
			value: function encryptedPrivate(channel) {
				if (this.connector instanceof SocketIoConnector) throw new Error("Broadcaster ".concat(_typeof(this.options.broadcaster), " ").concat(this.options.broadcaster, " does not support encrypted private channels."));
				return this.connector.encryptedPrivateChannel(channel);
			}
		},
		{
			key: "socketId",
			value: function socketId() {
				return this.connector.socketId();
			}
		},
		{
			key: "registerInterceptors",
			value: function registerInterceptors() {
				if (typeof Vue === "function" && Vue.http) this.registerVueRequestInterceptor();
				if (typeof axios === "function") this.registerAxiosRequestInterceptor();
				if (typeof jQuery === "function") this.registerjQueryAjaxSetup();
				if ((typeof Turbo === "undefined" ? "undefined" : _typeof(Turbo)) === "object") this.registerTurboRequestInterceptor();
			}
		},
		{
			key: "registerVueRequestInterceptor",
			value: function registerVueRequestInterceptor() {
				var _this = this;
				Vue.http.interceptors.push(function(request, next) {
					if (_this.socketId()) request.headers.set("X-Socket-ID", _this.socketId());
					next();
				});
			}
		},
		{
			key: "registerAxiosRequestInterceptor",
			value: function registerAxiosRequestInterceptor() {
				var _this2 = this;
				axios.interceptors.request.use(function(config) {
					if (_this2.socketId()) config.headers["X-Socket-Id"] = _this2.socketId();
					return config;
				});
			}
		},
		{
			key: "registerjQueryAjaxSetup",
			value: function registerjQueryAjaxSetup() {
				var _this3 = this;
				if (typeof jQuery.ajax != "undefined") jQuery.ajaxPrefilter(function(options, originalOptions, xhr) {
					if (_this3.socketId()) xhr.setRequestHeader("X-Socket-Id", _this3.socketId());
				});
			}
		},
		{
			key: "registerTurboRequestInterceptor",
			value: function registerTurboRequestInterceptor() {
				var _this4 = this;
				document.addEventListener("turbo:before-fetch-request", function(event) {
					event.detail.fetchOptions.headers["X-Socket-Id"] = _this4.socketId();
				});
			}
		}
	]);
	return Echo;
}();
//#endregion
export { Channel, Connector, EventFormatter, Echo as default };
