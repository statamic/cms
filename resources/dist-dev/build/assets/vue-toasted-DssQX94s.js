//#region node_modules/@hoppscotch/vue-toasted/dist/vue-toasted.mjs
function rn(e) {
	return e && e.__esModule && Object.prototype.hasOwnProperty.call(e, "default") ? e.default : e;
}
var ir = { exports: {} };
/*! Hammer.JS - v2.0.7 - 2016-04-22
* http://hammerjs.github.io/
*
* Copyright (c) 2016 Jorik Tangelder;
* Licensed under the MIT license */
(function(e) {
	(function(t, n, a, o) {
		var l = [
			"",
			"webkit",
			"Moz",
			"MS",
			"ms",
			"o"
		], f = n.createElement("div"), v = "function", u = Math.round, E = Math.abs, g = Date.now;
		function m(r, i, s) {
			return setTimeout(I(r, s), i);
		}
		function C(r, i, s) {
			return Array.isArray(r) ? (_(r, s[i], s), !0) : !1;
		}
		function _(r, i, s) {
			var c;
			if (!!r) if (r.forEach) r.forEach(i, s);
			else if (r.length !== o) for (c = 0; c < r.length;) i.call(s, r[c], c, r), c++;
			else for (c in r) r.hasOwnProperty(c) && i.call(s, r[c], c, r);
		}
		function P(r, i, s) {
			var c = "DEPRECATED METHOD: " + i + `
` + s + ` AT 
`;
			return function() {
				var h = /* @__PURE__ */ new Error("get-stack-trace"), d = h && h.stack ? h.stack.replace(/^[^\(]+?[\n$]/gm, "").replace(/^\s+at\s+/gm, "").replace(/^Object.<anonymous>\s*\(/gm, "{anonymous}()@") : "Unknown Stack Trace", O = t.console && (t.console.warn || t.console.log);
				return O && O.call(t.console, c, d), r.apply(this, arguments);
			};
		}
		var y;
		typeof Object.assign != "function" ? y = function(i) {
			if (i === o || i === null) throw new TypeError("Cannot convert undefined or null to object");
			for (var s = Object(i), c = 1; c < arguments.length; c++) {
				var h = arguments[c];
				if (h !== o && h !== null) for (var d in h) h.hasOwnProperty(d) && (s[d] = h[d]);
			}
			return s;
		} : y = Object.assign;
		var M = P(function(i, s, c) {
			for (var h = Object.keys(s), d = 0; d < h.length;) (!c || c && i[h[d]] === o) && (i[h[d]] = s[h[d]]), d++;
			return i;
		}, "extend", "Use `assign`."), S = P(function(i, s) {
			return M(i, s, !0);
		}, "merge", "Use `assign`.");
		function p(r, i, s) {
			var c = i.prototype, h = r.prototype = Object.create(c);
			h.constructor = r, h._super = c, s && y(h, s);
		}
		function I(r, i) {
			return function() {
				return r.apply(i, arguments);
			};
		}
		function x(r, i) {
			return typeof r == v ? r.apply(i && i[0] || o, i) : r;
		}
		function j(r, i) {
			return r === o ? i : r;
		}
		function A(r, i, s) {
			_(N(i), function(c) {
				r.addEventListener(c, s, !1);
			});
		}
		function Y(r, i, s) {
			_(N(i), function(c) {
				r.removeEventListener(c, s, !1);
			});
		}
		function ie(r, i) {
			for (; r;) {
				if (r == i) return !0;
				r = r.parentNode;
			}
			return !1;
		}
		function U(r, i) {
			return r.indexOf(i) > -1;
		}
		function N(r) {
			return r.trim().split(/\s+/g);
		}
		function H(r, i, s) {
			if (r.indexOf && !s) return r.indexOf(i);
			for (var c = 0; c < r.length;) {
				if (s && r[c][s] == i || !s && r[c] === i) return c;
				c++;
			}
			return -1;
		}
		function K(r) {
			return Array.prototype.slice.call(r, 0);
		}
		function F(r, i, s) {
			for (var c = [], h = [], d = 0; d < r.length;) {
				var O = i ? r[d][i] : r[d];
				H(h, O) < 0 && c.push(r[d]), h[d] = O, d++;
			}
			return s && (i ? c = c.sort(function(D, k) {
				return D[i] > k[i];
			}) : c = c.sort()), c;
		}
		function ae(r, i) {
			for (var s, c, h = i[0].toUpperCase() + i.slice(1), d = 0; d < l.length;) {
				if (s = l[d], c = s ? s + h : i, c in r) return c;
				d++;
			}
			return o;
		}
		var Ee = 1;
		function et() {
			return Ee++;
		}
		function $(r) {
			var i = r.ownerDocument || r;
			return i.defaultView || i.parentWindow || t;
		}
		var fe = /mobile|tablet|ip(ad|hone|od)|android/i, se = "ontouchstart" in t, we = ae(t, "PointerEvent") !== o, De = se && fe.test(navigator.userAgent), oe = "touch", he = "pen", Te = "mouse", Ie = "kinect", tt = 25, R = 1, ve = 2, L = 4, V = 8, Re = 1, Ce = 2, Pe = 4, Oe = 8, _e = 16, z = Ce | Pe, de = Oe | _e, xt = z | de, Lt = ["x", "y"], ke = ["clientX", "clientY"];
		function W(r, i) {
			var s = this;
			this.manager = r, this.callback = i, this.element = r.element, this.target = r.options.inputTarget, this.domHandler = function(c) {
				x(r.options.enable, [r]) && s.handler(c);
			}, this.init();
		}
		W.prototype = {
			handler: function() {},
			init: function() {
				this.evEl && A(this.element, this.evEl, this.domHandler), this.evTarget && A(this.target, this.evTarget, this.domHandler), this.evWin && A($(this.element), this.evWin, this.domHandler);
			},
			destroy: function() {
				this.evEl && Y(this.element, this.evEl, this.domHandler), this.evTarget && Y(this.target, this.evTarget, this.domHandler), this.evWin && Y($(this.element), this.evWin, this.domHandler);
			}
		};
		function Mr(r) {
			var i, s = r.options.inputClass;
			return s ? i = s : we ? i = nt : De ? i = Ve : se ? i = it : i = Fe, new i(r, Sr);
		}
		function Sr(r, i, s) {
			var c = s.pointers.length, h = s.changedPointers.length, d = i & R && c - h === 0, O = i & (L | V) && c - h === 0;
			s.isFirst = !!d, s.isFinal = !!O, d && (r.session = {}), s.eventType = i, xr(r, s), r.emit("hammer.input", s), r.recognize(s), r.session.prevInput = s;
		}
		function xr(r, i) {
			var s = r.session, c = i.pointers, h = c.length;
			s.firstInput || (s.firstInput = wt(i)), h > 1 && !s.firstMultiple ? s.firstMultiple = wt(i) : h === 1 && (s.firstMultiple = !1);
			var d = s.firstInput, O = s.firstMultiple, w = O ? O.center : d.center, D = i.center = Dt(c);
			i.timeStamp = g(), i.deltaTime = i.timeStamp - d.timeStamp, i.angle = rt(w, D), i.distance = Ue(w, D), Lr(s, i), i.offsetDirection = kt(i.deltaX, i.deltaY);
			var k = Rt(i.deltaTime, i.deltaX, i.deltaY);
			i.overallVelocityX = k.x, i.overallVelocityY = k.y, i.overallVelocity = E(k.x) > E(k.y) ? k.x : k.y, i.scale = O ? Rr(O.pointers, c) : 1, i.rotation = O ? Dr(O.pointers, c) : 0, i.maxPointers = s.prevInput ? i.pointers.length > s.prevInput.maxPointers ? i.pointers.length : s.prevInput.maxPointers : i.pointers.length, wr(s, i);
			var Z = r.element;
			ie(i.srcEvent.target, Z) && (Z = i.srcEvent.target), i.target = Z;
		}
		function Lr(r, i) {
			var s = i.center, c = r.offsetDelta || {}, h = r.prevDelta || {}, d = r.prevInput || {};
			(i.eventType === R || d.eventType === L) && (h = r.prevDelta = {
				x: d.deltaX || 0,
				y: d.deltaY || 0
			}, c = r.offsetDelta = {
				x: s.x,
				y: s.y
			}), i.deltaX = h.x + (s.x - c.x), i.deltaY = h.y + (s.y - c.y);
		}
		function wr(r, i) {
			var s = r.lastInterval || i, c = i.timeStamp - s.timeStamp, h, d, O, w;
			if (i.eventType != V && (c > tt || s.velocity === o)) {
				var D = i.deltaX - s.deltaX, k = i.deltaY - s.deltaY, Z = Rt(c, D, k);
				d = Z.x, O = Z.y, h = E(Z.x) > E(Z.y) ? Z.x : Z.y, w = kt(D, k), r.lastInterval = i;
			} else h = s.velocity, d = s.velocityX, O = s.velocityY, w = s.direction;
			i.velocity = h, i.velocityX = d, i.velocityY = O, i.direction = w;
		}
		function wt(r) {
			for (var i = [], s = 0; s < r.pointers.length;) i[s] = {
				clientX: u(r.pointers[s].clientX),
				clientY: u(r.pointers[s].clientY)
			}, s++;
			return {
				timeStamp: g(),
				pointers: i,
				center: Dt(i),
				deltaX: r.deltaX,
				deltaY: r.deltaY
			};
		}
		function Dt(r) {
			var i = r.length;
			if (i === 1) return {
				x: u(r[0].clientX),
				y: u(r[0].clientY)
			};
			for (var s = 0, c = 0, h = 0; h < i;) s += r[h].clientX, c += r[h].clientY, h++;
			return {
				x: u(s / i),
				y: u(c / i)
			};
		}
		function Rt(r, i, s) {
			return {
				x: i / r || 0,
				y: s / r || 0
			};
		}
		function kt(r, i) {
			return r === i ? Re : E(r) >= E(i) ? r < 0 ? Ce : Pe : i < 0 ? Oe : _e;
		}
		function Ue(r, i, s) {
			s || (s = Lt);
			var c = i[s[0]] - r[s[0]], h = i[s[1]] - r[s[1]];
			return Math.sqrt(c * c + h * h);
		}
		function rt(r, i, s) {
			s || (s = Lt);
			var c = i[s[0]] - r[s[0]], h = i[s[1]] - r[s[1]];
			return Math.atan2(h, c) * 180 / Math.PI;
		}
		function Dr(r, i) {
			return rt(i[1], i[0], ke) + rt(r[1], r[0], ke);
		}
		function Rr(r, i) {
			return Ue(i[0], i[1], ke) / Ue(r[0], r[1], ke);
		}
		var kr = {
			mousedown: R,
			mousemove: ve,
			mouseup: L
		}, Ur = "mousedown", Fr = "mousemove mouseup";
		function Fe() {
			this.evEl = Ur, this.evWin = Fr, this.pressed = !1, W.apply(this, arguments);
		}
		p(Fe, W, { handler: function(i) {
			var s = kr[i.type];
			s & R && i.button === 0 && (this.pressed = !0), s & ve && i.which !== 1 && (s = L), this.pressed && (s & L && (this.pressed = !1), this.callback(this.manager, s, {
				pointers: [i],
				changedPointers: [i],
				pointerType: Te,
				srcEvent: i
			}));
		} });
		var Vr = {
			pointerdown: R,
			pointermove: ve,
			pointerup: L,
			pointercancel: V,
			pointerout: V
		}, Hr = {
			2: oe,
			3: he,
			4: Te,
			5: Ie
		}, Ut = "pointerdown", Ft = "pointermove pointerup pointercancel";
		t.MSPointerEvent && !t.PointerEvent && (Ut = "MSPointerDown", Ft = "MSPointerMove MSPointerUp MSPointerCancel");
		function nt() {
			this.evEl = Ut, this.evWin = Ft, W.apply(this, arguments), this.store = this.manager.session.pointerEvents = [];
		}
		p(nt, W, { handler: function(i) {
			var s = this.store, c = !1, d = Vr[i.type.toLowerCase().replace("ms", "")], O = Hr[i.pointerType] || i.pointerType, w = O == oe, D = H(s, i.pointerId, "pointerId");
			d & R && (i.button === 0 || w) ? D < 0 && (s.push(i), D = s.length - 1) : d & (L | V) && (c = !0), !(D < 0) && (s[D] = i, this.callback(this.manager, d, {
				pointers: s,
				changedPointers: [i],
				pointerType: O,
				srcEvent: i
			}), c && s.splice(D, 1));
		} });
		var Yr = {
			touchstart: R,
			touchmove: ve,
			touchend: L,
			touchcancel: V
		}, Wr = "touchstart", Br = "touchstart touchmove touchend touchcancel";
		function Vt() {
			this.evTarget = Wr, this.evWin = Br, this.started = !1, W.apply(this, arguments);
		}
		p(Vt, W, { handler: function(i) {
			var s = Yr[i.type];
			if (s === R && (this.started = !0), !!this.started) {
				var c = jr.call(this, i, s);
				s & (L | V) && c[0].length - c[1].length === 0 && (this.started = !1), this.callback(this.manager, s, {
					pointers: c[0],
					changedPointers: c[1],
					pointerType: oe,
					srcEvent: i
				});
			}
		} });
		function jr(r, i) {
			var s = K(r.touches), c = K(r.changedTouches);
			return i & (L | V) && (s = F(s.concat(c), "identifier", !0)), [s, c];
		}
		var qr = {
			touchstart: R,
			touchmove: ve,
			touchend: L,
			touchcancel: V
		}, Xr = "touchstart touchmove touchend touchcancel";
		function Ve() {
			this.evTarget = Xr, this.targetIds = {}, W.apply(this, arguments);
		}
		p(Ve, W, { handler: function(i) {
			var s = qr[i.type], c = zr.call(this, i, s);
			!c || this.callback(this.manager, s, {
				pointers: c[0],
				changedPointers: c[1],
				pointerType: oe,
				srcEvent: i
			});
		} });
		function zr(r, i) {
			var s = K(r.touches), c = this.targetIds;
			if (i & (R | ve) && s.length === 1) return c[s[0].identifier] = !0, [s, s];
			var h, d, O = K(r.changedTouches), w = [], D = this.target;
			if (d = s.filter(function(k) {
				return ie(k.target, D);
			}), i === R) for (h = 0; h < d.length;) c[d[h].identifier] = !0, h++;
			for (h = 0; h < O.length;) c[O[h].identifier] && w.push(O[h]), i & (L | V) && delete c[O[h].identifier], h++;
			if (!!w.length) return [F(d.concat(w), "identifier", !0), w];
		}
		var Gr = 2500, Ht = 25;
		function it() {
			W.apply(this, arguments);
			var r = I(this.handler, this);
			this.touch = new Ve(this.manager, r), this.mouse = new Fe(this.manager, r), this.primaryTouch = null, this.lastTouches = [];
		}
		p(it, W, {
			handler: function(i, s, c) {
				var h = c.pointerType == oe, d = c.pointerType == Te;
				if (!(d && c.sourceCapabilities && c.sourceCapabilities.firesTouchEvents)) {
					if (h) Zr.call(this, s, c);
					else if (d && Qr.call(this, c)) return;
					this.callback(i, s, c);
				}
			},
			destroy: function() {
				this.touch.destroy(), this.mouse.destroy();
			}
		});
		function Zr(r, i) {
			r & R ? (this.primaryTouch = i.changedPointers[0].identifier, Yt.call(this, i)) : r & (L | V) && Yt.call(this, i);
		}
		function Yt(r) {
			var i = r.changedPointers[0];
			if (i.identifier === this.primaryTouch) {
				var s = {
					x: i.clientX,
					y: i.clientY
				};
				this.lastTouches.push(s);
				var c = this.lastTouches, h = function() {
					var d = c.indexOf(s);
					d > -1 && c.splice(d, 1);
				};
				setTimeout(h, Gr);
			}
		}
		function Qr(r) {
			for (var i = r.srcEvent.clientX, s = r.srcEvent.clientY, c = 0; c < this.lastTouches.length; c++) {
				var h = this.lastTouches[c], d = Math.abs(i - h.x), O = Math.abs(s - h.y);
				if (d <= Ht && O <= Ht) return !0;
			}
			return !1;
		}
		var Wt = ae(f.style, "touchAction"), Bt = Wt !== o, jt = "compute", qt = "auto", at = "manipulation", ge = "none", be = "pan-x", Ae = "pan-y", He = Kr();
		function st(r, i) {
			this.manager = r, this.set(i);
		}
		st.prototype = {
			set: function(r) {
				r == jt && (r = this.compute()), Bt && this.manager.element.style && He[r] && (this.manager.element.style[Wt] = r), this.actions = r.toLowerCase().trim();
			},
			update: function() {
				this.set(this.manager.options.touchAction);
			},
			compute: function() {
				var r = [];
				return _(this.manager.recognizers, function(i) {
					x(i.options.enable, [i]) && (r = r.concat(i.getTouchAction()));
				}), Jr(r.join(" "));
			},
			preventDefaults: function(r) {
				var i = r.srcEvent, s = r.offsetDirection;
				if (this.manager.session.prevented) {
					i.preventDefault();
					return;
				}
				var c = this.actions, h = U(c, ge) && !He[ge], d = U(c, Ae) && !He[Ae], O = U(c, be) && !He[be];
				if (h) {
					var w = r.pointers.length === 1, D = r.distance < 2, k = r.deltaTime < 250;
					if (w && D && k) return;
				}
				if (!(O && d) && (h || d && s & z || O && s & de)) return this.preventSrc(i);
			},
			preventSrc: function(r) {
				this.manager.session.prevented = !0, r.preventDefault();
			}
		};
		function Jr(r) {
			if (U(r, ge)) return ge;
			var i = U(r, be), s = U(r, Ae);
			return i && s ? ge : i || s ? i ? be : Ae : U(r, at) ? at : qt;
		}
		function Kr() {
			if (!Bt) return !1;
			var r = {}, i = t.CSS && t.CSS.supports;
			return [
				"auto",
				"manipulation",
				"pan-y",
				"pan-x",
				"pan-x pan-y",
				"none"
			].forEach(function(s) {
				r[s] = i ? t.CSS.supports("touch-action", s) : !0;
			}), r;
		}
		var Ye = 1, B = 2, ye = 4, ue = 8, ee = ue, Ne = 16, G = 32;
		function te(r) {
			this.options = y({}, this.defaults, r || {}), this.id = et(), this.manager = null, this.options.enable = j(this.options.enable, !0), this.state = Ye, this.simultaneous = {}, this.requireFail = [];
		}
		te.prototype = {
			defaults: {},
			set: function(r) {
				return y(this.options, r), this.manager && this.manager.touchAction.update(), this;
			},
			recognizeWith: function(r) {
				if (C(r, "recognizeWith", this)) return this;
				var i = this.simultaneous;
				return r = We(r, this), i[r.id] || (i[r.id] = r, r.recognizeWith(this)), this;
			},
			dropRecognizeWith: function(r) {
				return C(r, "dropRecognizeWith", this) ? this : (r = We(r, this), delete this.simultaneous[r.id], this);
			},
			requireFailure: function(r) {
				if (C(r, "requireFailure", this)) return this;
				var i = this.requireFail;
				return r = We(r, this), H(i, r) === -1 && (i.push(r), r.requireFailure(this)), this;
			},
			dropRequireFailure: function(r) {
				if (C(r, "dropRequireFailure", this)) return this;
				r = We(r, this);
				var i = H(this.requireFail, r);
				return i > -1 && this.requireFail.splice(i, 1), this;
			},
			hasRequireFailures: function() {
				return this.requireFail.length > 0;
			},
			canRecognizeWith: function(r) {
				return !!this.simultaneous[r.id];
			},
			emit: function(r) {
				var i = this, s = this.state;
				function c(h) {
					i.manager.emit(h, r);
				}
				s < ue && c(i.options.event + Xt(s)), c(i.options.event), r.additionalEvent && c(r.additionalEvent), s >= ue && c(i.options.event + Xt(s));
			},
			tryEmit: function(r) {
				if (this.canEmit()) return this.emit(r);
				this.state = G;
			},
			canEmit: function() {
				for (var r = 0; r < this.requireFail.length;) {
					if (!(this.requireFail[r].state & (G | Ye))) return !1;
					r++;
				}
				return !0;
			},
			recognize: function(r) {
				var i = y({}, r);
				if (!x(this.options.enable, [this, i])) {
					this.reset(), this.state = G;
					return;
				}
				this.state & (ee | Ne | G) && (this.state = Ye), this.state = this.process(i), this.state & (B | ye | ue | Ne) && this.tryEmit(i);
			},
			process: function(r) {},
			getTouchAction: function() {},
			reset: function() {}
		};
		function Xt(r) {
			return r & Ne ? "cancel" : r & ue ? "end" : r & ye ? "move" : r & B ? "start" : "";
		}
		function zt(r) {
			return r == _e ? "down" : r == Oe ? "up" : r == Ce ? "left" : r == Pe ? "right" : "";
		}
		function We(r, i) {
			var s = i.manager;
			return s ? s.get(r) : r;
		}
		function q() {
			te.apply(this, arguments);
		}
		p(q, te, {
			defaults: { pointers: 1 },
			attrTest: function(r) {
				var i = this.options.pointers;
				return i === 0 || r.pointers.length === i;
			},
			process: function(r) {
				var i = this.state, s = r.eventType, c = i & (B | ye), h = this.attrTest(r);
				return c && (s & V || !h) ? i | Ne : c || h ? s & L ? i | ue : i & B ? i | ye : B : G;
			}
		});
		function Be() {
			q.apply(this, arguments), this.pX = null, this.pY = null;
		}
		p(Be, q, {
			defaults: {
				event: "pan",
				threshold: 10,
				pointers: 1,
				direction: xt
			},
			getTouchAction: function() {
				var r = this.options.direction, i = [];
				return r & z && i.push(Ae), r & de && i.push(be), i;
			},
			directionTest: function(r) {
				var i = this.options, s = !0, c = r.distance, h = r.direction, d = r.deltaX, O = r.deltaY;
				return h & i.direction || (i.direction & z ? (h = d === 0 ? Re : d < 0 ? Ce : Pe, s = d != this.pX, c = Math.abs(r.deltaX)) : (h = O === 0 ? Re : O < 0 ? Oe : _e, s = O != this.pY, c = Math.abs(r.deltaY))), r.direction = h, s && c > i.threshold && h & i.direction;
			},
			attrTest: function(r) {
				return q.prototype.attrTest.call(this, r) && (this.state & B || !(this.state & B) && this.directionTest(r));
			},
			emit: function(r) {
				this.pX = r.deltaX, this.pY = r.deltaY;
				var i = zt(r.direction);
				i && (r.additionalEvent = this.options.event + i), this._super.emit.call(this, r);
			}
		});
		function ot() {
			q.apply(this, arguments);
		}
		p(ot, q, {
			defaults: {
				event: "pinch",
				threshold: 0,
				pointers: 2
			},
			getTouchAction: function() {
				return [ge];
			},
			attrTest: function(r) {
				return this._super.attrTest.call(this, r) && (Math.abs(r.scale - 1) > this.options.threshold || this.state & B);
			},
			emit: function(r) {
				if (r.scale !== 1) {
					var i = r.scale < 1 ? "in" : "out";
					r.additionalEvent = this.options.event + i;
				}
				this._super.emit.call(this, r);
			}
		});
		function ut() {
			te.apply(this, arguments), this._timer = null, this._input = null;
		}
		p(ut, te, {
			defaults: {
				event: "press",
				pointers: 1,
				time: 251,
				threshold: 9
			},
			getTouchAction: function() {
				return [qt];
			},
			process: function(r) {
				var i = this.options, s = r.pointers.length === i.pointers, c = r.distance < i.threshold, h = r.deltaTime > i.time;
				if (this._input = r, !c || !s || r.eventType & (L | V) && !h) this.reset();
				else if (r.eventType & R) this.reset(), this._timer = m(function() {
					this.state = ee, this.tryEmit();
				}, i.time, this);
				else if (r.eventType & L) return ee;
				return G;
			},
			reset: function() {
				clearTimeout(this._timer);
			},
			emit: function(r) {
				this.state === ee && (r && r.eventType & L ? this.manager.emit(this.options.event + "up", r) : (this._input.timeStamp = g(), this.manager.emit(this.options.event, this._input)));
			}
		});
		function ct() {
			q.apply(this, arguments);
		}
		p(ct, q, {
			defaults: {
				event: "rotate",
				threshold: 0,
				pointers: 2
			},
			getTouchAction: function() {
				return [ge];
			},
			attrTest: function(r) {
				return this._super.attrTest.call(this, r) && (Math.abs(r.rotation) > this.options.threshold || this.state & B);
			}
		});
		function lt() {
			q.apply(this, arguments);
		}
		p(lt, q, {
			defaults: {
				event: "swipe",
				threshold: 10,
				velocity: .3,
				direction: z | de,
				pointers: 1
			},
			getTouchAction: function() {
				return Be.prototype.getTouchAction.call(this);
			},
			attrTest: function(r) {
				var i = this.options.direction, s;
				return i & (z | de) ? s = r.overallVelocity : i & z ? s = r.overallVelocityX : i & de && (s = r.overallVelocityY), this._super.attrTest.call(this, r) && i & r.offsetDirection && r.distance > this.options.threshold && r.maxPointers == this.options.pointers && E(s) > this.options.velocity && r.eventType & L;
			},
			emit: function(r) {
				var i = zt(r.offsetDirection);
				i && this.manager.emit(this.options.event + i, r), this.manager.emit(this.options.event, r);
			}
		});
		function je() {
			te.apply(this, arguments), this.pTime = !1, this.pCenter = !1, this._timer = null, this._input = null, this.count = 0;
		}
		p(je, te, {
			defaults: {
				event: "tap",
				pointers: 1,
				taps: 1,
				interval: 300,
				time: 250,
				threshold: 9,
				posThreshold: 10
			},
			getTouchAction: function() {
				return [at];
			},
			process: function(r) {
				var i = this.options, s = r.pointers.length === i.pointers, c = r.distance < i.threshold, h = r.deltaTime < i.time;
				if (this.reset(), r.eventType & R && this.count === 0) return this.failTimeout();
				if (c && h && s) {
					if (r.eventType != L) return this.failTimeout();
					var d = this.pTime ? r.timeStamp - this.pTime < i.interval : !0, O = !this.pCenter || Ue(this.pCenter, r.center) < i.posThreshold;
					this.pTime = r.timeStamp, this.pCenter = r.center, !O || !d ? this.count = 1 : this.count += 1, this._input = r;
					if (this.count % i.taps === 0) return this.hasRequireFailures() ? (this._timer = m(function() {
						this.state = ee, this.tryEmit();
					}, i.interval, this), B) : ee;
				}
				return G;
			},
			failTimeout: function() {
				return this._timer = m(function() {
					this.state = G;
				}, this.options.interval, this), G;
			},
			reset: function() {
				clearTimeout(this._timer);
			},
			emit: function() {
				this.state == ee && (this._input.tapCount = this.count, this.manager.emit(this.options.event, this._input));
			}
		});
		function re(r, i) {
			return i = i || {}, i.recognizers = j(i.recognizers, re.defaults.preset), new ft(r, i);
		}
		re.VERSION = "2.0.7", re.defaults = {
			domEvents: !1,
			touchAction: jt,
			enable: !0,
			inputTarget: null,
			inputClass: null,
			preset: [
				[ct, { enable: !1 }],
				[
					ot,
					{ enable: !1 },
					["rotate"]
				],
				[lt, { direction: z }],
				[
					Be,
					{ direction: z },
					["swipe"]
				],
				[je],
				[
					je,
					{
						event: "doubletap",
						taps: 2
					},
					["tap"]
				],
				[ut]
			],
			cssProps: {
				userSelect: "none",
				touchSelect: "none",
				touchCallout: "none",
				contentZooming: "none",
				userDrag: "none",
				tapHighlightColor: "rgba(0,0,0,0)"
			}
		};
		var $r = 1, Gt = 2;
		function ft(r, i) {
			this.options = y({}, re.defaults, i || {}), this.options.inputTarget = this.options.inputTarget || r, this.handlers = {}, this.session = {}, this.recognizers = [], this.oldCssProps = {}, this.element = r, this.input = Mr(this), this.touchAction = new st(this, this.options.touchAction), Zt(this, !0), _(this.options.recognizers, function(s) {
				var c = this.add(new s[0](s[1]));
				s[2] && c.recognizeWith(s[2]), s[3] && c.requireFailure(s[3]);
			}, this);
		}
		ft.prototype = {
			set: function(r) {
				return y(this.options, r), r.touchAction && this.touchAction.update(), r.inputTarget && (this.input.destroy(), this.input.target = r.inputTarget, this.input.init()), this;
			},
			stop: function(r) {
				this.session.stopped = r ? Gt : $r;
			},
			recognize: function(r) {
				var i = this.session;
				if (!i.stopped) {
					this.touchAction.preventDefaults(r);
					var s, c = this.recognizers, h = i.curRecognizer;
					(!h || h && h.state & ee) && (h = i.curRecognizer = null);
					for (var d = 0; d < c.length;) s = c[d], i.stopped !== Gt && (!h || s == h || s.canRecognizeWith(h)) ? s.recognize(r) : s.reset(), !h && s.state & (B | ye | ue) && (h = i.curRecognizer = s), d++;
				}
			},
			get: function(r) {
				if (r instanceof te) return r;
				for (var i = this.recognizers, s = 0; s < i.length; s++) if (i[s].options.event == r) return i[s];
				return null;
			},
			add: function(r) {
				if (C(r, "add", this)) return this;
				var i = this.get(r.options.event);
				return i && this.remove(i), this.recognizers.push(r), r.manager = this, this.touchAction.update(), r;
			},
			remove: function(r) {
				if (C(r, "remove", this)) return this;
				if (r = this.get(r), r) {
					var i = this.recognizers, s = H(i, r);
					s !== -1 && (i.splice(s, 1), this.touchAction.update());
				}
				return this;
			},
			on: function(r, i) {
				if (r !== o && i !== o) {
					var s = this.handlers;
					return _(N(r), function(c) {
						s[c] = s[c] || [], s[c].push(i);
					}), this;
				}
			},
			off: function(r, i) {
				if (r !== o) {
					var s = this.handlers;
					return _(N(r), function(c) {
						i ? s[c] && s[c].splice(H(s[c], i), 1) : delete s[c];
					}), this;
				}
			},
			emit: function(r, i) {
				this.options.domEvents && en(r, i);
				var s = this.handlers[r] && this.handlers[r].slice();
				if (!(!s || !s.length)) {
					i.type = r, i.preventDefault = function() {
						i.srcEvent.preventDefault();
					};
					for (var c = 0; c < s.length;) s[c](i), c++;
				}
			},
			destroy: function() {
				this.element && Zt(this, !1), this.handlers = {}, this.session = {}, this.input.destroy(), this.element = null;
			}
		};
		function Zt(r, i) {
			var s = r.element;
			if (!!s.style) {
				var c;
				_(r.options.cssProps, function(h, d) {
					c = ae(s.style, d), i ? (r.oldCssProps[c] = s.style[c], s.style[c] = h) : s.style[c] = r.oldCssProps[c] || "";
				}), i || (r.oldCssProps = {});
			}
		}
		function en(r, i) {
			var s = n.createEvent("Event");
			s.initEvent(r, !0, !0), s.gesture = i, i.target.dispatchEvent(s);
		}
		y(re, {
			INPUT_START: R,
			INPUT_MOVE: ve,
			INPUT_END: L,
			INPUT_CANCEL: V,
			STATE_POSSIBLE: Ye,
			STATE_BEGAN: B,
			STATE_CHANGED: ye,
			STATE_ENDED: ue,
			STATE_RECOGNIZED: ee,
			STATE_CANCELLED: Ne,
			STATE_FAILED: G,
			DIRECTION_NONE: Re,
			DIRECTION_LEFT: Ce,
			DIRECTION_RIGHT: Pe,
			DIRECTION_UP: Oe,
			DIRECTION_DOWN: _e,
			DIRECTION_HORIZONTAL: z,
			DIRECTION_VERTICAL: de,
			DIRECTION_ALL: xt,
			Manager: ft,
			Input: W,
			TouchAction: st,
			TouchInput: Ve,
			MouseInput: Fe,
			PointerEventInput: nt,
			TouchMouseInput: it,
			SingleTouchInput: Vt,
			Recognizer: te,
			AttrRecognizer: q,
			Tap: je,
			Pan: Be,
			Swipe: lt,
			Pinch: ot,
			Rotate: ct,
			Press: ut,
			on: A,
			off: Y,
			each: _,
			merge: S,
			extend: M,
			assign: y,
			inherit: p,
			bindFn: I,
			prefixed: ae
		});
		var tn = typeof t < "u" ? t : typeof self < "u" ? self : {};
		tn.Hammer = re, typeof o == "function" && o.amd ? o(function() {
			return re;
		}) : e.exports ? e.exports = re : t[a] = re;
	})(window, document, "Hammer");
})(ir);
var nn = ir.exports;
var ar = {
	update: null,
	begin: null,
	loopBegin: null,
	changeBegin: null,
	change: null,
	changeComplete: null,
	loopComplete: null,
	complete: null,
	loop: 1,
	direction: "normal",
	autoplay: !0,
	timelineOffset: 0
}, Et = {
	duration: 1e3,
	delay: 0,
	endDelay: 0,
	easing: "easeOutElastic(1, .5)",
	round: 0
}, an = [
	"translateX",
	"translateY",
	"translateZ",
	"rotate",
	"rotateX",
	"rotateY",
	"rotateZ",
	"scale",
	"scaleX",
	"scaleY",
	"scaleZ",
	"skew",
	"skewX",
	"skewY",
	"perspective",
	"matrix",
	"matrix3d"
], Qe = {
	CSS: {},
	springs: {}
};
function Q(e, t, n) {
	return Math.min(Math.max(e, t), n);
}
function Le(e, t) {
	return e.indexOf(t) > -1;
}
function ht(e, t) {
	return e.apply(null, t);
}
var T = {
	arr: function(e) {
		return Array.isArray(e);
	},
	obj: function(e) {
		return Le(Object.prototype.toString.call(e), "Object");
	},
	pth: function(e) {
		return T.obj(e) && e.hasOwnProperty("totalLength");
	},
	svg: function(e) {
		return e instanceof SVGElement;
	},
	inp: function(e) {
		return e instanceof HTMLInputElement;
	},
	dom: function(e) {
		return e.nodeType || T.svg(e);
	},
	str: function(e) {
		return typeof e == "string";
	},
	fnc: function(e) {
		return typeof e == "function";
	},
	und: function(e) {
		return typeof e > "u";
	},
	nil: function(e) {
		return T.und(e) || e === null;
	},
	hex: function(e) {
		return /(^#[0-9A-F]{6}$)|(^#[0-9A-F]{3}$)/i.test(e);
	},
	rgb: function(e) {
		return /^rgb/.test(e);
	},
	hsl: function(e) {
		return /^hsl/.test(e);
	},
	col: function(e) {
		return T.hex(e) || T.rgb(e) || T.hsl(e);
	},
	key: function(e) {
		return !ar.hasOwnProperty(e) && !Et.hasOwnProperty(e) && e !== "targets" && e !== "keyframes";
	}
};
function sr(e) {
	var t = /\(([^)]+)\)/.exec(e);
	return t ? t[1].split(",").map(function(n) {
		return parseFloat(n);
	}) : [];
}
function or(e, t) {
	var n = sr(e), a = Q(T.und(n[0]) ? 1 : n[0], .1, 100), o = Q(T.und(n[1]) ? 100 : n[1], .1, 100), l = Q(T.und(n[2]) ? 10 : n[2], .1, 100), f = Q(T.und(n[3]) ? 0 : n[3], .1, 100), v = Math.sqrt(o / a), u = l / (2 * Math.sqrt(o * a)), E = u < 1 ? v * Math.sqrt(1 - u * u) : 0, g = 1, m = u < 1 ? (u * v + -f) / E : -f + v;
	function C(P) {
		var y = t ? t * P / 1e3 : P;
		return u < 1 ? y = Math.exp(-y * u * v) * (g * Math.cos(E * y) + m * Math.sin(E * y)) : y = (g + m * y) * Math.exp(-y * v), P === 0 || P === 1 ? P : 1 - y;
	}
	function _() {
		var P = Qe.springs[e];
		if (P) return P;
		for (var y = 1 / 6, M = 0, S = 0;;) if (M += y, C(M) === 1) {
			if (S++, S >= 16) break;
		} else S = 0;
		var p = M * y * 1e3;
		return Qe.springs[e] = p, p;
	}
	return t ? C : _;
}
function sn(e) {
	return e === void 0 && (e = 10), function(t) {
		return Math.ceil(Q(t, 1e-6, 1) * e) * (1 / e);
	};
}
var on = function() {
	var e = 11, t = 1 / (e - 1);
	function n(g, m) {
		return 1 - 3 * m + 3 * g;
	}
	function a(g, m) {
		return 3 * m - 6 * g;
	}
	function o(g) {
		return 3 * g;
	}
	function l(g, m, C) {
		return ((n(m, C) * g + a(m, C)) * g + o(m)) * g;
	}
	function f(g, m, C) {
		return 3 * n(m, C) * g * g + 2 * a(m, C) * g + o(m);
	}
	function v(g, m, C, _, P) {
		var y, M, S = 0;
		do
			M = m + (C - m) / 2, y = l(M, _, P) - g, y > 0 ? C = M : m = M;
		while (Math.abs(y) > 1e-7 && ++S < 10);
		return M;
	}
	function u(g, m, C, _) {
		for (var P = 0; P < 4; ++P) {
			var y = f(m, C, _);
			if (y === 0) return m;
			var M = l(m, C, _) - g;
			m -= M / y;
		}
		return m;
	}
	function E(g, m, C, _) {
		if (!(0 <= g && g <= 1 && 0 <= C && C <= 1)) return;
		var P = new Float32Array(e);
		if (g !== m || C !== _) for (var y = 0; y < e; ++y) P[y] = l(y * t, g, C);
		function M(S) {
			for (var p = 0, I = 1, x = e - 1; I !== x && P[I] <= S; ++I) p += t;
			--I;
			var j = (S - P[I]) / (P[I + 1] - P[I]), A = p + j * t, Y = f(A, g, C);
			return Y >= .001 ? u(S, A, g, C) : Y === 0 ? A : v(S, p, p + t, g, C);
		}
		return function(S) {
			return g === m && C === _ || S === 0 || S === 1 ? S : l(M(S), m, _);
		};
	}
	return E;
}(), ur = function() {
	var e = { linear: function() {
		return function(a) {
			return a;
		};
	} }, t = {
		Sine: function() {
			return function(a) {
				return 1 - Math.cos(a * Math.PI / 2);
			};
		},
		Circ: function() {
			return function(a) {
				return 1 - Math.sqrt(1 - a * a);
			};
		},
		Back: function() {
			return function(a) {
				return a * a * (3 * a - 2);
			};
		},
		Bounce: function() {
			return function(a) {
				for (var o, l = 4; a < ((o = Math.pow(2, --l)) - 1) / 11;);
				return 1 / Math.pow(4, 3 - l) - 7.5625 * Math.pow((o * 3 - 2) / 22 - a, 2);
			};
		},
		Elastic: function(a, o) {
			a === void 0 && (a = 1), o === void 0 && (o = .5);
			var l = Q(a, 1, 10), f = Q(o, .1, 2);
			return function(v) {
				return v === 0 || v === 1 ? v : -l * Math.pow(2, 10 * (v - 1)) * Math.sin((v - 1 - f / (Math.PI * 2) * Math.asin(1 / l)) * (Math.PI * 2) / f);
			};
		}
	};
	return [
		"Quad",
		"Cubic",
		"Quart",
		"Quint",
		"Expo"
	].forEach(function(a, o) {
		t[a] = function() {
			return function(l) {
				return Math.pow(l, o + 2);
			};
		};
	}), Object.keys(t).forEach(function(a) {
		var o = t[a];
		e["easeIn" + a] = o, e["easeOut" + a] = function(l, f) {
			return function(v) {
				return 1 - o(l, f)(1 - v);
			};
		}, e["easeInOut" + a] = function(l, f) {
			return function(v) {
				return v < .5 ? o(l, f)(v * 2) / 2 : 1 - o(l, f)(v * -2 + 2) / 2;
			};
		}, e["easeOutIn" + a] = function(l, f) {
			return function(v) {
				return v < .5 ? (1 - o(l, f)(1 - v * 2)) / 2 : (o(l, f)(v * 2 - 1) + 1) / 2;
			};
		};
	}), e;
}();
function It(e, t) {
	if (T.fnc(e)) return e;
	var n = e.split("(")[0], a = ur[n], o = sr(e);
	switch (n) {
		case "spring": return or(e, t);
		case "cubicBezier": return ht(on, o);
		case "steps": return ht(sn, o);
		default: return ht(a, o);
	}
}
function cr(e) {
	try {
		return document.querySelectorAll(e);
	} catch {
		return;
	}
}
function Je(e, t) {
	for (var n = e.length, a = arguments.length >= 2 ? arguments[1] : void 0, o = [], l = 0; l < n; l++) if (l in e) {
		var f = e[l];
		t.call(a, f, l, e) && o.push(f);
	}
	return o;
}
function Ke(e) {
	return e.reduce(function(t, n) {
		return t.concat(T.arr(n) ? Ke(n) : n);
	}, []);
}
function Qt(e) {
	return T.arr(e) ? e : (T.str(e) && (e = cr(e) || e), e instanceof NodeList || e instanceof HTMLCollection ? [].slice.call(e) : [e]);
}
function Ct(e, t) {
	return e.some(function(n) {
		return n === t;
	});
}
function Pt(e) {
	var t = {};
	for (var n in e) t[n] = e[n];
	return t;
}
function dt(e, t) {
	var n = Pt(e);
	for (var a in e) n[a] = t.hasOwnProperty(a) ? t[a] : e[a];
	return n;
}
function $e(e, t) {
	var n = Pt(e);
	for (var a in t) n[a] = T.und(e[a]) ? t[a] : e[a];
	return n;
}
function un(e) {
	var t = /rgb\((\d+,\s*[\d]+,\s*[\d]+)\)/g.exec(e);
	return t ? "rgba(" + t[1] + ",1)" : e;
}
function cn(e) {
	var n = e.replace(/^#?([a-f\d])([a-f\d])([a-f\d])$/i, function(v, u, E, g) {
		return u + u + E + E + g + g;
	}), a = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(n), o = parseInt(a[1], 16), l = parseInt(a[2], 16), f = parseInt(a[3], 16);
	return "rgba(" + o + "," + l + "," + f + ",1)";
}
function ln(e) {
	var t = /hsl\((\d+),\s*([\d.]+)%,\s*([\d.]+)%\)/g.exec(e) || /hsla\((\d+),\s*([\d.]+)%,\s*([\d.]+)%,\s*([\d.]+)\)/g.exec(e), n = parseInt(t[1], 10) / 360, a = parseInt(t[2], 10) / 100, o = parseInt(t[3], 10) / 100, l = t[4] || 1;
	function f(C, _, P) {
		return P < 0 && (P += 1), P > 1 && (P -= 1), P < 1 / 6 ? C + (_ - C) * 6 * P : P < 1 / 2 ? _ : P < 2 / 3 ? C + (_ - C) * (2 / 3 - P) * 6 : C;
	}
	var v, u, E;
	if (a == 0) v = u = E = o;
	else {
		var g = o < .5 ? o * (1 + a) : o + a - o * a, m = 2 * o - g;
		v = f(m, g, n + 1 / 3), u = f(m, g, n), E = f(m, g, n - 1 / 3);
	}
	return "rgba(" + v * 255 + "," + u * 255 + "," + E * 255 + "," + l + ")";
}
function fn(e) {
	if (T.rgb(e)) return un(e);
	if (T.hex(e)) return cn(e);
	if (T.hsl(e)) return ln(e);
}
function ne(e) {
	var t = /[+-]?\d*\.?\d+(?:\.\d+)?(?:[eE][+-]?\d+)?(%|px|pt|em|rem|in|cm|mm|ex|ch|pc|vw|vh|vmin|vmax|deg|rad|turn)?$/.exec(e);
	if (t) return t[1];
}
function hn(e) {
	if (Le(e, "translate") || e === "perspective") return "px";
	if (Le(e, "rotate") || Le(e, "skew")) return "deg";
}
function gt(e, t) {
	return T.fnc(e) ? e(t.target, t.id, t.total) : e;
}
function J(e, t) {
	return e.getAttribute(t);
}
function Ot(e, t, n) {
	var a = ne(t);
	if (Ct([
		n,
		"deg",
		"rad",
		"turn"
	], a)) return t;
	var o = Qe.CSS[t + n];
	if (!T.und(o)) return o;
	var l = 100, f = document.createElement(e.tagName), v = e.parentNode && e.parentNode !== document ? e.parentNode : document.body;
	v.appendChild(f), f.style.position = "absolute", f.style.width = l + n;
	var u = l / f.offsetWidth;
	v.removeChild(f);
	var E = u * parseFloat(t);
	return Qe.CSS[t + n] = E, E;
}
function lr(e, t, n) {
	if (t in e.style) {
		var a = t.replace(/([a-z])([A-Z])/g, "$1-$2").toLowerCase(), o = e.style[t] || getComputedStyle(e).getPropertyValue(a) || "0";
		return n ? Ot(e, o, n) : o;
	}
}
function _t(e, t) {
	if (T.dom(e) && !T.inp(e) && (!T.nil(J(e, t)) || T.svg(e) && e[t])) return "attribute";
	if (T.dom(e) && Ct(an, t)) return "transform";
	if (T.dom(e) && t !== "transform" && lr(e, t)) return "css";
	if (e[t] != null) return "object";
}
function fr(e) {
	if (!!T.dom(e)) {
		for (var t = e.style.transform || "", n = /(\w+)\(([^)]*)\)/g, a = /* @__PURE__ */ new Map(), o; o = n.exec(t);) a.set(o[1], o[2]);
		return a;
	}
}
function vn(e, t, n, a) {
	var o = Le(t, "scale") ? 1 : 0 + hn(t), l = fr(e).get(t) || o;
	return n && (n.transforms.list.set(t, l), n.transforms.last = t), a ? Ot(e, l, a) : l;
}
function bt(e, t, n, a) {
	switch (_t(e, t)) {
		case "transform": return vn(e, t, a, n);
		case "css": return lr(e, t, n);
		case "attribute": return J(e, t);
		default: return e[t] || 0;
	}
}
function At(e, t) {
	var n = /^(\*=|\+=|-=)/.exec(e);
	if (!n) return e;
	var a = ne(e) || 0, o = parseFloat(t), l = parseFloat(e.replace(n[0], ""));
	switch (n[0][0]) {
		case "+": return o + l + a;
		case "-": return o - l + a;
		case "*": return o * l + a;
	}
}
function hr(e, t) {
	if (T.col(e)) return fn(e);
	if (/\s/g.test(e)) return e;
	var n = ne(e), a = n ? e.substr(0, e.length - n.length) : e;
	return t ? a + t : a;
}
function Nt(e, t) {
	return Math.sqrt(Math.pow(t.x - e.x, 2) + Math.pow(t.y - e.y, 2));
}
function dn(e) {
	return Math.PI * 2 * J(e, "r");
}
function gn(e) {
	return J(e, "width") * 2 + J(e, "height") * 2;
}
function pn(e) {
	return Nt({
		x: J(e, "x1"),
		y: J(e, "y1")
	}, {
		x: J(e, "x2"),
		y: J(e, "y2")
	});
}
function vr(e) {
	for (var t = e.points, n = 0, a, o = 0; o < t.numberOfItems; o++) {
		var l = t.getItem(o);
		o > 0 && (n += Nt(a, l)), a = l;
	}
	return n;
}
function mn(e) {
	var t = e.points;
	return vr(e) + Nt(t.getItem(t.numberOfItems - 1), t.getItem(0));
}
function dr(e) {
	if (e.getTotalLength) return e.getTotalLength();
	switch (e.tagName.toLowerCase()) {
		case "circle": return dn(e);
		case "rect": return gn(e);
		case "line": return pn(e);
		case "polyline": return vr(e);
		case "polygon": return mn(e);
	}
}
function Tn(e) {
	var t = dr(e);
	return e.setAttribute("stroke-dasharray", t), t;
}
function yn(e) {
	for (var t = e.parentNode; T.svg(t) && T.svg(t.parentNode);) t = t.parentNode;
	return t;
}
function gr(e, t) {
	var n = t || {}, a = n.el || yn(e), o = a.getBoundingClientRect(), l = J(a, "viewBox"), f = o.width, v = o.height, u = n.viewBox || (l ? l.split(" ") : [
		0,
		0,
		f,
		v
	]);
	return {
		el: a,
		viewBox: u,
		x: u[0] / 1,
		y: u[1] / 1,
		w: f,
		h: v,
		vW: u[2],
		vH: u[3]
	};
}
function En(e, t) {
	var n = T.str(e) ? cr(e)[0] : e, a = t || 100;
	return function(o) {
		return {
			property: o,
			el: n,
			svg: gr(n),
			totalLength: dr(n) * (a / 100)
		};
	};
}
function In(e, t, n) {
	function a(g) {
		g === void 0 && (g = 0);
		var m = t + g >= 1 ? t + g : 0;
		return e.el.getPointAtLength(m);
	}
	var o = gr(e.el, e.svg), l = a(), f = a(-1), v = a(1), u = n ? 1 : o.w / o.vW, E = n ? 1 : o.h / o.vH;
	switch (e.property) {
		case "x": return (l.x - o.x) * u;
		case "y": return (l.y - o.y) * E;
		case "angle": return Math.atan2(v.y - f.y, v.x - f.x) * 180 / Math.PI;
	}
}
function Jt(e, t) {
	var n = /[+-]?\d*\.?\d+(?:\.\d+)?(?:[eE][+-]?\d+)?/g, a = hr(T.pth(e) ? e.totalLength : e, t) + "";
	return {
		original: a,
		numbers: a.match(n) ? a.match(n).map(Number) : [0],
		strings: T.str(e) || t ? a.split(n) : []
	};
}
function Mt(e) {
	return Je(e ? Ke(T.arr(e) ? e.map(Qt) : Qt(e)) : [], function(n, a, o) {
		return o.indexOf(n) === a;
	});
}
function pr(e) {
	var t = Mt(e);
	return t.map(function(n, a) {
		return {
			target: n,
			id: a,
			total: t.length,
			transforms: { list: fr(n) }
		};
	});
}
function Cn(e, t) {
	var n = Pt(t);
	if (/^spring/.test(n.easing) && (n.duration = or(n.easing)), T.arr(e)) {
		var a = e.length;
		a === 2 && !T.obj(e[0]) ? e = { value: e } : T.fnc(t.duration) || (n.duration = t.duration / a);
	}
	var l = T.arr(e) ? e : [e];
	return l.map(function(f, v) {
		var u = T.obj(f) && !T.pth(f) ? f : { value: f };
		return T.und(u.delay) && (u.delay = v ? 0 : t.delay), T.und(u.endDelay) && (u.endDelay = v === l.length - 1 ? t.endDelay : 0), u;
	}).map(function(f) {
		return $e(f, n);
	});
}
function Pn(e) {
	for (var t = Je(Ke(e.map(function(l) {
		return Object.keys(l);
	})), function(l) {
		return T.key(l);
	}).reduce(function(l, f) {
		return l.indexOf(f) < 0 && l.push(f), l;
	}, []), n = {}, a = function(l) {
		var f = t[l];
		n[f] = e.map(function(v) {
			var u = {};
			for (var E in v) T.key(E) ? E == f && (u.value = v[E]) : u[E] = v[E];
			return u;
		});
	}, o = 0; o < t.length; o++) a(o);
	return n;
}
function On(e, t) {
	var n = [], a = t.keyframes;
	a && (t = $e(Pn(a), t));
	for (var o in t) T.key(o) && n.push({
		name: o,
		tweens: Cn(t[o], e)
	});
	return n;
}
function _n(e, t) {
	var n = {};
	for (var a in e) {
		var o = gt(e[a], t);
		T.arr(o) && (o = o.map(function(l) {
			return gt(l, t);
		}), o.length === 1 && (o = o[0])), n[a] = o;
	}
	return n.duration = parseFloat(n.duration), n.delay = parseFloat(n.delay), n;
}
function bn(e, t) {
	var n;
	return e.tweens.map(function(a) {
		var o = _n(a, t), l = o.value, f = T.arr(l) ? l[1] : l, v = ne(f), u = bt(t.target, e.name, v, t), E = n ? n.to.original : u, g = T.arr(l) ? l[0] : E, m = ne(g) || ne(u), C = v || m;
		return T.und(f) && (f = E), o.from = Jt(g, C), o.to = Jt(At(f, g), C), o.start = n ? n.end : 0, o.end = o.start + o.delay + o.duration + o.endDelay, o.easing = It(o.easing, o.duration), o.isPath = T.pth(l), o.isPathTargetInsideSVG = o.isPath && T.svg(t.target), o.isColor = T.col(o.from.original), o.isColor && (o.round = 1), n = o, o;
	});
}
var mr = {
	css: function(e, t, n) {
		return e.style[t] = n;
	},
	attribute: function(e, t, n) {
		return e.setAttribute(t, n);
	},
	object: function(e, t, n) {
		return e[t] = n;
	},
	transform: function(e, t, n, a, o) {
		if (a.list.set(t, n), t === a.last || o) {
			var l = "";
			a.list.forEach(function(f, v) {
				l += v + "(" + f + ") ";
			}), e.style.transform = l;
		}
	}
};
function Tr(e, t) {
	pr(e).forEach(function(a) {
		for (var o in t) {
			var l = gt(t[o], a), f = a.target, v = ne(l), u = bt(f, o, v, a), g = At(hr(l, v || ne(u)), u);
			mr[_t(f, o)](f, o, g, a.transforms, !0);
		}
	});
}
function An(e, t) {
	var n = _t(e.target, t.name);
	if (n) {
		var a = bn(t, e), o = a[a.length - 1];
		return {
			type: n,
			property: t.name,
			animatable: e,
			tweens: a,
			duration: o.end,
			delay: a[0].delay,
			endDelay: o.endDelay
		};
	}
}
function Nn(e, t) {
	return Je(Ke(e.map(function(n) {
		return t.map(function(a) {
			return An(n, a);
		});
	})), function(n) {
		return !T.und(n);
	});
}
function yr(e, t) {
	var n = e.length, a = function(l) {
		return l.timelineOffset ? l.timelineOffset : 0;
	}, o = {};
	return o.duration = n ? Math.max.apply(Math, e.map(function(l) {
		return a(l) + l.duration;
	})) : t.duration, o.delay = n ? Math.min.apply(Math, e.map(function(l) {
		return a(l) + l.delay;
	})) : t.delay, o.endDelay = n ? o.duration - Math.max.apply(Math, e.map(function(l) {
		return a(l) + l.duration - l.endDelay;
	})) : t.endDelay, o;
}
var Kt = 0;
function Mn(e) {
	var t = dt(ar, e), n = dt(Et, e), a = On(n, e), o = pr(e.targets), l = Nn(o, a), f = yr(l, n), v = Kt;
	return Kt++, $e(t, {
		id: v,
		children: [],
		animatables: o,
		animations: l,
		duration: f.duration,
		delay: f.delay,
		endDelay: f.endDelay
	});
}
var X = [], Er = function() {
	var e;
	function t() {
		!e && (!$t() || !b.suspendWhenDocumentHidden) && X.length > 0 && (e = requestAnimationFrame(n));
	}
	function n(o) {
		for (var l = X.length, f = 0; f < l;) {
			var v = X[f];
			v.paused ? (X.splice(f, 1), l--) : (v.tick(o), f++);
		}
		e = f > 0 ? requestAnimationFrame(n) : void 0;
	}
	function a() {
		!b.suspendWhenDocumentHidden || ($t() ? e = cancelAnimationFrame(e) : (X.forEach(function(o) {
			return o._onDocumentVisibility();
		}), Er()));
	}
	return typeof document < "u" && document.addEventListener("visibilitychange", a), t;
}();
function $t() {
	return !!document && document.hidden;
}
function b(e) {
	e === void 0 && (e = {});
	var t = 0, n = 0, a = 0, o, l = 0, f = null;
	function v(p) {
		var I = window.Promise && new Promise(function(x) {
			return f = x;
		});
		return p.finished = I, I;
	}
	var u = Mn(e);
	v(u);
	function E() {
		var p = u.direction;
		p !== "alternate" && (u.direction = p !== "normal" ? "normal" : "reverse"), u.reversed = !u.reversed, o.forEach(function(I) {
			return I.reversed = u.reversed;
		});
	}
	function g(p) {
		return u.reversed ? u.duration - p : p;
	}
	function m() {
		t = 0, n = g(u.currentTime) * (1 / b.speed);
	}
	function C(p, I) {
		I && I.seek(p - I.timelineOffset);
	}
	function _(p) {
		if (u.reversePlayback) for (var x = l; x--;) C(p, o[x]);
		else for (var I = 0; I < l; I++) C(p, o[I]);
	}
	function P(p) {
		for (var I = 0, x = u.animations, j = x.length; I < j;) {
			var A = x[I], Y = A.animatable, ie = A.tweens, U = ie.length - 1, N = ie[U];
			U && (N = Je(ie, function(tt) {
				return p < tt.end;
			})[0] || N);
			for (var H = Q(p - N.start - N.delay, 0, N.duration) / N.duration, K = isNaN(H) ? 1 : N.easing(H), F = N.to.strings, ae = N.round, Ee = [], et = N.to.numbers.length, $ = void 0, fe = 0; fe < et; fe++) {
				var se = void 0, we = N.to.numbers[fe], De = N.from.numbers[fe] || 0;
				N.isPath ? se = In(N.value, K * we, N.isPathTargetInsideSVG) : se = De + K * (we - De), ae && (N.isColor && fe > 2 || (se = Math.round(se * ae) / ae)), Ee.push(se);
			}
			var oe = F.length;
			if (!oe) $ = Ee[0];
			else {
				$ = F[0];
				for (var he = 0; he < oe; he++) {
					F[he];
					var Te = F[he + 1], Ie = Ee[he];
					isNaN(Ie) || (Te ? $ += Ie + Te : $ += Ie + " ");
				}
			}
			mr[A.type](Y.target, A.property, $, Y.transforms), A.currentValue = $, I++;
		}
	}
	function y(p) {
		u[p] && !u.passThrough && u[p](u);
	}
	function M() {
		u.remaining && u.remaining !== !0 && u.remaining--;
	}
	function S(p) {
		var I = u.duration, x = u.delay, j = I - u.endDelay, A = g(p);
		u.progress = Q(A / I * 100, 0, 100), u.reversePlayback = A < u.currentTime, o && _(A), !u.began && u.currentTime > 0 && (u.began = !0, y("begin")), !u.loopBegan && u.currentTime > 0 && (u.loopBegan = !0, y("loopBegin")), A <= x && u.currentTime !== 0 && P(0), (A >= j && u.currentTime !== I || !I) && P(I), A > x && A < j ? (u.changeBegan || (u.changeBegan = !0, u.changeCompleted = !1, y("changeBegin")), y("change"), P(A)) : u.changeBegan && (u.changeCompleted = !0, u.changeBegan = !1, y("changeComplete")), u.currentTime = Q(A, 0, I), u.began && y("update"), p >= I && (n = 0, M(), u.remaining ? (t = a, y("loopComplete"), u.loopBegan = !1, u.direction === "alternate" && E()) : (u.paused = !0, u.completed || (u.completed = !0, y("loopComplete"), y("complete"), !u.passThrough && "Promise" in window && (f(), v(u)))));
	}
	return u.reset = function() {
		var p = u.direction;
		u.passThrough = !1, u.currentTime = 0, u.progress = 0, u.paused = !0, u.began = !1, u.loopBegan = !1, u.changeBegan = !1, u.completed = !1, u.changeCompleted = !1, u.reversePlayback = !1, u.reversed = p === "reverse", u.remaining = u.loop, o = u.children, l = o.length;
		for (var I = l; I--;) u.children[I].reset();
		(u.reversed && u.loop !== !0 || p === "alternate" && u.loop === 1) && u.remaining++, P(u.reversed ? u.duration : 0);
	}, u._onDocumentVisibility = m, u.set = function(p, I) {
		return Tr(p, I), u;
	}, u.tick = function(p) {
		a = p, t || (t = a), S((a + (n - t)) * b.speed);
	}, u.seek = function(p) {
		S(g(p));
	}, u.pause = function() {
		u.paused = !0, m();
	}, u.play = function() {
		!u.paused || (u.completed && u.reset(), u.paused = !1, X.push(u), m(), Er());
	}, u.reverse = function() {
		E(), u.completed = !u.reversed, m();
	}, u.restart = function() {
		u.reset(), u.play();
	}, u.remove = function(p) {
		Ir(Mt(p), u);
	}, u.reset(), u.autoplay && u.play(), u;
}
function er(e, t) {
	for (var n = t.length; n--;) Ct(e, t[n].animatable.target) && t.splice(n, 1);
}
function Ir(e, t) {
	var n = t.animations, a = t.children;
	er(e, n);
	for (var o = a.length; o--;) {
		var l = a[o], f = l.animations;
		er(e, f), !f.length && !l.children.length && a.splice(o, 1);
	}
	!n.length && !a.length && t.pause();
}
function Sn(e) {
	for (var t = Mt(e), n = X.length; n--;) {
		var a = X[n];
		Ir(t, a);
	}
}
function xn(e, t) {
	t === void 0 && (t = {});
	var n = t.direction || "normal", a = t.easing ? It(t.easing) : null, o = t.grid, l = t.axis, f = t.from || 0, v = f === "first", u = f === "center", E = f === "last", g = T.arr(e), m = parseFloat(g ? e[0] : e), C = g ? parseFloat(e[1]) : 0, _ = ne(g ? e[1] : e) || 0, P = t.start || 0 + (g ? m : 0), y = [], M = 0;
	return function(S, p, I) {
		if (v && (f = 0), u && (f = (I - 1) / 2), E && (f = I - 1), !y.length) {
			for (var x = 0; x < I; x++) {
				if (!o) y.push(Math.abs(f - x));
				else {
					var j = u ? (o[0] - 1) / 2 : f % o[0], A = u ? (o[1] - 1) / 2 : Math.floor(f / o[0]), Y = x % o[0], ie = Math.floor(x / o[0]), U = j - Y, N = A - ie, H = Math.sqrt(U * U + N * N);
					l === "x" && (H = -U), l === "y" && (H = -N), y.push(H);
				}
				M = Math.max.apply(Math, y);
			}
			a && (y = y.map(function(F) {
				return a(F / M) * M;
			})), n === "reverse" && (y = y.map(function(F) {
				return l ? F < 0 ? F * -1 : -F : Math.abs(M - F);
			}));
		}
		return P + (g ? (C - m) / M : m) * (Math.round(y[p] * 100) / 100) + _;
	};
}
function Ln(e) {
	e === void 0 && (e = {});
	var t = b(e);
	return t.duration = 0, t.add = function(n, a) {
		var o = X.indexOf(t), l = t.children;
		o > -1 && X.splice(o, 1);
		function f(C) {
			C.passThrough = !0;
		}
		for (var v = 0; v < l.length; v++) f(l[v]);
		var u = $e(n, dt(Et, e));
		u.targets = u.targets || e.targets;
		var E = t.duration;
		u.autoplay = !1, u.direction = t.direction, u.timelineOffset = T.und(a) ? E : At(a, E), f(t), t.seek(u.timelineOffset);
		var g = b(u);
		f(g), l.push(g);
		var m = yr(l, e);
		return t.delay = m.delay, t.endDelay = m.endDelay, t.duration = m.duration, t.seek(0), t.reset(), t.autoplay && t.play(), t;
	}, t;
}
b.version = "3.2.1";
b.speed = 1;
b.suspendWhenDocumentHidden = !0;
b.running = X;
b.remove = Sn;
b.get = bt;
b.set = Tr;
b.convertPx = Ot;
b.path = En;
b.setDashoffset = Tn;
b.stagger = xn;
b.timeline = Ln;
b.easing = It;
b.penner = ur;
b.random = function(e, t) {
	return Math.floor(Math.random() * (t - e + 1)) + e;
};
var Me = 300;
var le = {
	animateIn: (e) => {
		b({
			targets: e,
			translateY: "-35px",
			opacity: 1,
			duration: Me,
			easing: "easeOutCubic"
		});
	},
	animateOut: (e, t) => {
		b({
			targets: e,
			opacity: 0,
			marginTop: "-40px",
			duration: Me,
			easing: "easeOutExpo",
			complete: t
		});
	},
	animateOutBottom: (e, t) => {
		b({
			targets: e,
			opacity: 0,
			marginBottom: "-40px",
			duration: Me,
			easing: "easeOutExpo",
			complete: t
		});
	},
	animateReset: (e) => {
		b({
			targets: e,
			left: 0,
			opacity: 1,
			duration: Me,
			easing: "easeOutExpo"
		});
	},
	animatePanning: (e, t, n) => {
		b({
			targets: e,
			duration: 10,
			easing: "easeOutQuad",
			left: t,
			opacity: n
		});
	},
	animatePanEnd: (e, t) => {
		b({
			targets: e,
			opacity: 0,
			duration: Me,
			easing: "easeOutExpo",
			complete: t
		});
	},
	clearAnimation: (e) => {
		let t = b.timeline();
		e.forEach((n) => {
			t.add({
				targets: n.el,
				opacity: 0,
				right: "-40px",
				duration: 300,
				offset: "-=150",
				easing: "easeOutExpo",
				complete: () => {
					n.remove();
				}
			});
		});
	}
};
var Cr = { exports: {} }, Pr = { exports: {} }, ze = 1;
function wn() {
	return ze = (ze * 9301 + 49297) % 233280, ze / 233280;
}
function Dn(e) {
	ze = e;
}
var pt = {
	nextValue: wn,
	seed: Dn
}, pe = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_-", ce, tr, Se;
function mt() {
	Se = !1;
}
function Or(e) {
	if (!e) {
		ce !== pe && (ce = pe, mt());
		return;
	}
	if (e !== ce) {
		if (e.length !== pe.length) throw new Error("Custom alphabet for shortid must be " + pe.length + " unique characters. You submitted " + e.length + " characters: " + e);
		var t = e.split("").filter(function(n, a, o) {
			return a !== o.lastIndexOf(n);
		});
		if (t.length) throw new Error("Custom alphabet for shortid must be " + pe.length + " unique characters. These characters were not unique: " + t.join(", "));
		ce = e, mt();
	}
}
function kn(e) {
	return Or(e), ce;
}
function Un(e) {
	pt.seed(e), tr !== e && (mt(), tr = e);
}
function Fn() {
	ce || Or(pe);
	for (var e = ce.split(""), t = [], n = pt.nextValue(), a; e.length > 0;) n = pt.nextValue(), a = Math.floor(n * e.length), t.push(e.splice(a, 1)[0]);
	return t.join("");
}
function _r() {
	return Se || (Se = Fn(), Se);
}
function Vn(e) {
	return _r()[e];
}
function Hn() {
	return ce || pe;
}
var St = {
	get: Hn,
	characters: kn,
	seed: Un,
	lookup: Vn,
	shuffled: _r
}, vt = typeof window == "object" && (window.crypto || window.msCrypto), Tt;
!vt || !vt.getRandomValues ? Tt = function(e) {
	for (var t = [], n = 0; n < e; n++) t.push(Math.floor(Math.random() * 256));
	return t;
} : Tt = function(e) {
	return vt.getRandomValues(new Uint8Array(e));
};
var Yn = Tt, Wn = function(e, t, n) {
	for (var a = (2 << Math.log(t.length - 1) / Math.LN2) - 1, o = -~(1.6 * a * n / t.length), l = "";;) for (var f = e(o), v = o; v--;) if (l += t[f[v] & a] || "", l.length === +n) return l;
}, Bn = St, jn = Yn, qn = Wn;
function Xn(e) {
	for (var t = 0, n, a = ""; !n;) a = a + qn(jn, Bn.get(), 1), n = e < Math.pow(16, t + 1), t++;
	return a;
}
var qe = Xn, Gn = 1567752802062, Zn = 7, Xe, rr;
function Qn(e) {
	var t = "", n = Math.floor((Date.now() - Gn) * .001);
	return n === rr ? Xe++ : (Xe = 0, rr = n), t = t + qe(Zn), t = t + qe(e), Xe > 0 && (t = t + qe(Xe)), t = t + qe(n), t;
}
var Jn = Qn, Kn = St;
function $n(e) {
	if (!e || typeof e != "string" || e.length < 6) return !1;
	return !new RegExp("[^" + Kn.get().replace(/[|\\{}()[\]^$+*?.-]/g, "\\$&") + "]").test(e);
}
var ei = $n;
(function(e) {
	var t = St, n = Jn, a = ei, o = 0;
	function l(E) {
		return t.seed(E), e.exports;
	}
	function f(E) {
		return o = E, e.exports;
	}
	function v(E) {
		return E !== void 0 && t.characters(E), t.shuffled();
	}
	function u() {
		return n(o);
	}
	e.exports = u, e.exports.generate = u, e.exports.seed = l, e.exports.worker = f, e.exports.characters = v, e.exports.isValid = a;
})(Pr);
(function(e) {
	e.exports = Pr.exports;
})(Cr);
var br = /* @__PURE__ */ rn(Cr.exports), ti = (e, t, n) => (setTimeout(function() {
	if (n.cached_options.position && n.cached_options.position.includes("bottom")) {
		le.animateOutBottom(e, () => {
			n.remove(e);
		});
		return;
	}
	le.animateOut(e, () => {
		n.remove(e);
	});
}, t), !0), ri = (e, t) => ((typeof HTMLElement == "object" ? t instanceof HTMLElement : t && typeof t == "object" && t !== null && t.nodeType === 1 && typeof t.nodeName == "string") ? e.appendChild(t) : e.innerHTML = t, globalThis), yt = function(e, t) {
	let n = !1;
	return {
		el: e,
		text: function(a) {
			return ri(e, a), this;
		},
		goAway: function(a = 800) {
			return n = !0, ti(e, a, t);
		},
		remove: function() {
			t.remove(e);
		},
		disposed: function() {
			return n;
		}
	};
};
var Ge = {}, me = null;
var ni = function(e) {
	return e.className = e.className || null, e.onComplete = e.onComplete || null, e.position = e.position || "top-right", e.duration = e.duration || null, e.keepOnHover = e.keepOnHover || !1, e.theme = e.theme || "toasted-primary", e.type = e.type || "default", e.containerClass = e.containerClass || null, e.fullWidth = e.fullWidth || !1, e.icon = e.icon || null, e.action = e.action || null, e.fitToScreen = e.fitToScreen || null, e.closeOnSwipe = typeof e.closeOnSwipe < "u" ? e.closeOnSwipe : !0, e.iconPack = e.iconPack || "material", e.className && typeof e.className == "string" && (e.className = e.className.split(" ")), e.className || (e.className = []), e.theme && e.className.push(e.theme.trim()), e.type && e.className.push(e.type), e.containerClass && typeof e.containerClass == "string" && (e.containerClass = e.containerClass.split(" ")), e.containerClass || (e.containerClass = []), e.position && e.containerClass.push(e.position.trim()), e.fullWidth && e.containerClass.push("full-width"), e.fitToScreen && e.containerClass.push("fit-to-screen"), Ge = e, e;
}, ii = function(e, t) {
	let n = document.createElement("div");
	if (n.classList.add("toasted"), n.hash = br.generate(), t.className && t.className.forEach((a) => {
		n.classList.add(a);
	}), (typeof HTMLElement == "object" ? e instanceof HTMLElement : e && typeof e == "object" && e !== null && e.nodeType === 1 && typeof e.nodeName == "string") ? n.appendChild(e) : n.innerHTML = e, ai(t, n), t.closeOnSwipe) {
		let a = new nn(n, { prevent_default: !1 });
		a.on("pan", function(o) {
			let l = o.deltaX, f = 80;
			n.classList.contains("panning") || n.classList.add("panning");
			let v = 1 - Math.abs(l / f);
			v < 0 && (v = 0), le.animatePanning(n, l, v);
		}), a.on("panend", function(o) {
			let l = o.deltaX;
			Math.abs(l) > 80 ? le.animatePanEnd(n, function() {
				typeof t.onComplete == "function" && t.onComplete(), n.parentNode && me.remove(n);
			}) : (n.classList.remove("panning"), le.animateReset(n));
		});
	}
	if (Array.isArray(t.action)) t.action.forEach((a) => {
		let o = nr(a, yt(n, me));
		o && n.appendChild(o);
	});
	else if (typeof t.action == "object") {
		let a = nr(t.action, yt(n, me));
		a && n.appendChild(a);
	}
	return n;
}, ai = (e, t) => {
	if (e.icon) {
		let n = document.createElement("i");
		switch (n.setAttribute("aria-hidden", "true"), e.iconPack) {
			case "fontawesome":
				n.classList.add("fa");
				let a = e.icon.name ? e.icon.name : e.icon;
				a.includes("fa-") ? n.classList.add(a.trim()) : n.classList.add("fa-" + a.trim());
				break;
			case "mdi":
				n.classList.add("mdi");
				let o = e.icon.name ? e.icon.name : e.icon;
				o.includes("mdi-") ? n.classList.add(o.trim()) : n.classList.add("mdi-" + o.trim());
				break;
			case "custom-class":
				let l = e.icon.name ? e.icon.name : e.icon;
				typeof l == "string" ? l.split(" ").forEach((v) => {
					n.classList.add(v);
				}) : Array.isArray(l) && l.forEach((v) => {
					n.classList.add(v.trim());
				});
				break;
			case "callback":
				let f = e.icon && e.icon instanceof Function ? e.icon : null;
				f && (n = f(n));
				break;
			default: n.classList.add("material-icons"), n.textContent = e.icon.name ? e.icon.name : e.icon;
		}
		e.icon.after && n.classList.add("after"), si(e, n, t);
	}
}, si = (e, t, n) => {
	e.icon && (e.icon.after && e.icon.name ? n.appendChild(t) : (e.icon.name, n.insertBefore(t, n.firstChild)));
}, nr = (e, t) => {
	if (!e) return null;
	let n;
	if (e.href ? n = document.createElement("a") : n = document.createElement("button"), n.classList.add("action"), n.classList.add("ripple"), e.text && (n.textContent = e.text), e.href && (n.href = e.href), e.target && (n.target = e.target), e.icon) {
		n.classList.add("icon");
		let a = document.createElement("i");
		switch (Ge.iconPack) {
			case "fontawesome":
				a.classList.add("fa"), e.icon.includes("fa-") ? a.classList.add(e.icon.trim()) : a.classList.add("fa-" + e.icon.trim());
				break;
			case "mdi":
				a.classList.add("mdi"), e.icon.includes("mdi-") ? a.classList.add(e.icon.trim()) : a.classList.add("mdi-" + e.icon.trim());
				break;
			case "custom-class":
				typeof e.icon == "string" ? e.icon.split(" ").forEach((o) => {
					n.classList.add(o);
				}) : Array.isArray(e.icon) && e.icon.forEach((o) => {
					n.classList.add(o.trim());
				});
				break;
			default: a.classList.add("material-icons"), a.textContent = e.icon;
		}
		n.appendChild(a);
	}
	return e.class && (typeof e.class == "string" ? e.class.split(" ").forEach((a) => {
		n.classList.add(a);
	}) : Array.isArray(e.class) && e.class.forEach((a) => {
		n.classList.add(a.trim());
	})), e.push && n.addEventListener("click", (a) => {
		if (a.preventDefault(), !Ge.router) {
			console.warn("[vue-toasted] : Vue Router instance is not attached. please check the docs");
			return;
		}
		Ge.router.push(e.push), e.push.dontClose || t.goAway(0);
	}), e.onClick && typeof e.onClick == "function" && n.addEventListener("click", (a) => {
		e.onClick && (a.preventDefault(), e.onClick(a, t));
	}), n;
};
function oi(e, t, n) {
	me = e, n = ni(n);
	const a = me.container;
	n.containerClass.unshift("toasted-container"), a.className !== n.containerClass.join(" ") && (a.className = "", n.containerClass.forEach((v) => {
		a.classList.add(v);
	}));
	let o = ii(t, n);
	t && a.appendChild(o), o.style.opacity = 0, le.animateIn(o);
	let l = n.duration, f;
	if (l !== null) {
		const v = () => setInterval(function() {
			o.parentNode === null && window.clearInterval(f), o.classList.contains("panning") || (l -= 20), l <= 0 && (le.animateOut(o, function() {
				typeof n.onComplete == "function" && n.onComplete(), o.parentNode && me.remove(o);
			}), window.clearInterval(f));
		}, 20);
		f = v(), n.keepOnHover && (o.addEventListener("mouseover", () => {
			window.clearInterval(f);
		}), o.addEventListener("mouseout", () => {
			f = v();
		}));
	}
	return yt(o, me);
}
var Ar = function(e) {
	return this.id = br.generate(), this.options = e, this.cached_options = {}, this.global = {}, this.groups = [], this.toasts = [], this.container = null, ui(this), Nr(this), this.group = (t) => {
		t || (t = {}), t.globalToasts || (t.globalToasts = {}), Object.assign(t.globalToasts, this.global);
		let n = new Ar(t);
		return this.groups.push(n), n;
	}, this.register = (t, n, a) => (a = a || {}, ci(this, t, n, a)), this.show = (t, n) => xe(this, t, n), this.success = (t, n) => (n = n || {}, n.type = "success", xe(this, t, n)), this.info = (t, n) => (n = n || {}, n.type = "info", xe(this, t, n)), this.error = (t, n) => (n = n || {}, n.type = "error", xe(this, t, n)), this.remove = (t) => {
		this.toasts = this.toasts.filter((n) => n.el.hash !== t.hash), t.parentNode && t.parentNode.removeChild(t);
	}, this.clear = (t) => (le.clearAnimation(this.toasts, () => {
		t && t();
	}), this.toasts = [], !0), this;
}, xe = function(e, t, n) {
	n = n || {};
	let a = null;
	if (typeof n != "object") return console.error("Options should be a type of object. given : " + n), null;
	e.options.singleton && e.toasts.length > 0 && (e.cached_options = n, e.toasts[e.toasts.length - 1].goAway(0));
	let o = Object.assign({}, e.options);
	return Object.assign(o, n), a = oi(e, t, o), e.toasts.push(a), a;
}, Nr = function(e) {
	let t = e.options.globalToasts, n = (a, o) => typeof o == "string" && e[o] ? e[o].apply(e, [a, {}]) : xe(e, a, o);
	t && (e.global = {}, Object.keys(t).forEach((a) => {
		e.global[a] = (o = {}) => t[a].apply(null, [o, n]);
	}));
}, ui = function(e) {
	const t = document.createElement("div");
	t.id = e.id, t.setAttribute("role", "status"), t.setAttribute("aria-live", "polite"), t.setAttribute("aria-atomic", "false"), document.body.appendChild(t), e.container = t;
}, ci = function(e, t, n, a) {
	e.options.globalToasts || (e.options.globalToasts = {}), e.options.globalToasts[t] = function(o, l) {
		let f = null;
		return typeof n == "string" && (f = n), typeof n == "function" && (f = n(o)), l(f, a);
	}, Nr(e);
};
var li = {};
var Ze = null;
var fi = { install(e, t) {
	Ze = new Ar(t != null ? t : {}), e.component("toasted", li), e.config.globalProperties.toasted = Ze, e.config.globalProperties.$toasted = Ze;
} }, hi = () => Ze;
//#endregion
export { fi as default, hi as useToasted };
