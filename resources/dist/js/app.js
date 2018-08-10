webpackJsonp([2],{

/***/ 698:
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(699);


/***/ }),

/***/ 699:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* WEBPACK VAR INJECTION */(function($) {/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_vue__ = __webpack_require__(3);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_vue___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_vue__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__mixins_Notifications_js__ = __webpack_require__(700);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2_axios__ = __webpack_require__(188);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2_axios___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_2_axios__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3_portal_vue__ = __webpack_require__(726);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3_portal_vue___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_3_portal_vue__);





__WEBPACK_IMPORTED_MODULE_2_axios___default.a.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
__WEBPACK_IMPORTED_MODULE_2_axios___default.a.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('#csrf-token').getAttribute('value');

__WEBPACK_IMPORTED_MODULE_0_vue___default.a.prototype.axios = __WEBPACK_IMPORTED_MODULE_2_axios___default.a;
__WEBPACK_IMPORTED_MODULE_0_vue___default.a.prototype.$mousetrap = __webpack_require__(12);
__WEBPACK_IMPORTED_MODULE_0_vue___default.a.prototype.$eventHub = new __WEBPACK_IMPORTED_MODULE_0_vue___default.a(); // Global event bus

__WEBPACK_IMPORTED_MODULE_0_vue___default.a.config.productionTip = false;

__WEBPACK_IMPORTED_MODULE_0_vue___default.a.use(__WEBPACK_IMPORTED_MODULE_3_portal_vue___default.a);

// Vue.http.interceptors.push({
//     response: function (response) {
//         if (response.status === 401) {
//             this.$root.showLoginModal = true;
//         }
//
//         return response;
//     }
// });

__webpack_require__(727);

var vm = new __WEBPACK_IMPORTED_MODULE_0_vue___default.a({
    el: '#statamic',

    mixins: [__WEBPACK_IMPORTED_MODULE_1__mixins_Notifications_js__["a" /* default */]],

    data: {
        isPublishPage: false,
        isPreviewing: false,
        showShortcuts: false,
        version: Statamic.version,
        draggingNonFile: false,
        sneakPeekViewport: null,
        sneakPeekFields: null,
        windowWidth: null,
        showLoginModal: false
    },

    methods: {
        preview: function preview() {
            var _this = this;

            var self = this;
            self.$broadcast('previewing');

            this.sneakPeekViewport = $('.sneak-peek-viewport')[0];
            this.sneakPeekFields = $('.page-wrapper')[0];

            $('.sneak-peek-wrapper').addClass('animating on');

            this.wait(200).then(function () {
                self.isPreviewing = true;
                var width = localStorage.getItem('statamic.sneakpeek.width') || 400;
                _this.sneakPeekViewport.style.left = width + 'px';
                _this.sneakPeekFields.style.width = width + 'px';
                $(_this.$el).addClass('sneak-peeking');
                _this.$emit('livepreview.opened');
                return _this.wait(200);
            }).then(function () {
                $('#sneak-peek-iframe').show();
                $(_this.$el).addClass('sneak-peek-editing sneak-peek-animating');
                return _this.wait(500);
            }).then(function () {
                $(_this.$el).removeClass('sneak-peek-animating');
            });
        },

        stopPreviewing: function stopPreviewing() {
            var _this2 = this;

            this.$broadcast('previewing.stopped');

            $('.sneak-peek-wrapper').addClass('animating');
            $(this.$el).addClass('sneak-peek-animating');
            $(this.$el).removeClass('sneak-peek-editing');
            $('#sneak-peek-iframe').fadeOut();
            $('.sneak-peek-wrapper .icon').hide();

            this.wait(500).then(function () {
                _this2.sneakPeekViewport.style.left = null;
                _this2.sneakPeekFields.style.width = null;
                $(_this2.$el).removeClass('sneak-peek-animating');
                $(_this2.$el).removeClass('sneak-peeking');
                return _this2.wait(200);
            }).then(function () {
                _this2.isPreviewing = false;
                _this2.$emit('livepreview.closed');
                $('.sneak-peek-wrapper').removeClass('on');
                return _this2.wait(200);
            }).then(function () {
                $('.sneak-peek-wrapper').removeClass('animating');
            });
        },

        /**
         * Returns a promise after specified milliseconds
         *
         * A nice alternative to nested setTimeouts.
         */
        wait: function wait(ms) {
            return new Promise(function (resolve) {
                setTimeout(resolve, ms);
            });
        },


        // toggleNav: function () {
        //     this.navVisible = !this.navVisible;
        // },

        /**
         * When the dragstart event is triggered.
         *
         * This event doesn't get triggered when dragging something from outside the browser,
         * so we can determine that something other than a file is being dragged.
         */
        dragStart: function dragStart() {
            this.draggingNonFile = true;
        },


        /**
         * When the dragend event is triggered.
         *
         * This event doesn't get triggered when dragging something from outside the browser,
         * so we can determine that something other than a file is being dragged.
         */
        dragEnd: function dragEnd() {
            this.draggingNonFile = false;
        },
        sneakPeekResizeStart: function sneakPeekResizeStart(e) {
            window.addEventListener('mousemove', this.sneakPeekResizing);
            window.addEventListener('mouseup', this.sneakPeekResizeEnd);
            $('.sneak-peek-iframe-wrap').css('pointer-events', 'none');
        },
        sneakPeekResizeEnd: function sneakPeekResizeEnd(e) {
            window.removeEventListener('mousemove', this.sneakPeekResizing, false);
            window.removeEventListener('mouseup', this.sneakPeekResizeEnd, false);
            $('.sneak-peek-iframe-wrap').css('pointer-events', 'auto');
        },
        sneakPeekResizing: function sneakPeekResizing(e) {
            e.preventDefault();

            var width = e.clientX;

            // Prevent the width being too narrow.
            width = width < 350 ? 350 : width;

            this.sneakPeekViewport.style.left = width + 'px';
            this.sneakPeekFields.style.width = width + 'px';

            localStorage.setItem('statamic.sneakpeek.width', width);

            this.$emit('livepreview.resizing', width);
        }
    },

    mounted: function mounted() {
        console.log('Hello from Vue2!');

        this.$mousetrap.bind('?', function (e) {
            this.showShortcuts = true;
        }.bind(this), 'keyup');

        // Mousetrap.bind('escape', function(e) {
        //     this.$broadcast('close-modal');
        //     this.$broadcast('close-editor');
        //     this.$broadcast('close-selector');
        //     this.$broadcast('close-dropdown', null);
        // }.bind(this), 'keyup');

        // Keep track of whether something other than a file is being dragged
        // so that components can tell when a file is being dragged.
        // window.addEventListener('dragstart', this.dragStart);
        // window.addEventListener('dragend', this.dragEnd);
        //
        // this.windowWidth = document.documentElement.clientWidth;
        // window.addEventListener('resize', () => this.windowWidth = document.documentElement.clientWidth);
    },


    events: {
        'changesMade': function changesMade(changed) {
            // If true, a confirmation dialog will be displayed when the user tries to
            // navigate away (or refresh, etc). If false, the dialog will no longer show.
            if (changed) {
                window.onbeforeunload = function () {
                    return '';
                };
            } else {
                window.onbeforeunload = null;
            }
        }
    }
});
/* WEBPACK VAR INJECTION */}.call(__webpack_exports__, __webpack_require__(2)))

/***/ }),

/***/ 700:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__components_toast_main_js__ = __webpack_require__(701);


/* harmony default export */ __webpack_exports__["a"] = ({
    components: { VueToast: __WEBPACK_IMPORTED_MODULE_0__components_toast_main_js__["a" /* default */] },

    data: {
        toast: null,
        flash: Statamic.flash
    },

    methods: {
        flashExistingMessages: function flashExistingMessages() {
            var _this = this;

            this.flash.forEach(function (_ref) {
                var type = _ref.type,
                    message = _ref.message;
                return _this.setFlashMessage(message, { theme: type });
            });
        },
        bindToastNotifications: function bindToastNotifications() {
            this.toast = this.$refs.toast;
            if (this.toast) {
                this.toast.setOptions({
                    position: 'bottom right'
                });
            }
        },
        setFlashMessage: function setFlashMessage(message, opts) {
            this.toast.showToast(message, {
                theme: opts.theme,
                timeLife: opts.timeout || 5000,
                closeBtn: opts.hasOwnProperty('dismissible') ? opts.dismissible : true
            });
        }
    },

    events: {
        setFlashSuccess: function setFlashSuccess(message, opts) {
            opts = opts || {};
            opts.theme = 'success';
            this.setFlashMessage(message, opts);
        },
        setFlashError: function setFlashError(message, opts) {
            opts = opts || {};
            opts.theme = 'danger';
            this.setFlashMessage(message, opts);
        }
    },

    mounted: function mounted() {
        this.bindToastNotifications();
        this.flashExistingMessages();
    }
});

/***/ }),

/***/ 701:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__polyfills_js__ = __webpack_require__(702);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__polyfills_js___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0__polyfills_js__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__manager__ = __webpack_require__(703);



/* harmony default export */ __webpack_exports__["a"] = (__WEBPACK_IMPORTED_MODULE_1__manager__["a" /* default */]);

/***/ }),

/***/ 702:
/***/ (function(module, exports) {

if (!Object.assign) {
  Object.defineProperty(Object, 'assign', {
    enumerable: false,
    configurable: true,
    writable: true,
    value: function value(target, firstSource) {
      'use strict';

      if (target === undefined || target === null) {
        throw new TypeError('Cannot convert first argument to object');
      }

      var to = Object(target);
      for (var i = 1; i < arguments.length; i++) {
        var nextSource = arguments[i];
        if (nextSource === undefined || nextSource === null) {
          continue;
        }

        var keysArray = Object.keys(Object(nextSource));
        for (var nextIndex = 0, len = keysArray.length; nextIndex < len; nextIndex++) {
          var nextKey = keysArray[nextIndex];
          var desc = Object.getOwnPropertyDescriptor(nextSource, nextKey);
          if (desc !== undefined && desc.enumerable) {
            to[nextKey] = nextSource[nextKey];
          }
        }
      }
      return to;
    }
  });
}

/***/ }),

/***/ 703:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__template_html__ = __webpack_require__(704);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__template_html___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0__template_html__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__toast__ = __webpack_require__(705);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__utils_js__ = __webpack_require__(707);





var defaultOptions = {
  maxToasts: 6,
  position: 'left bottom'
};

/* harmony default export */ __webpack_exports__["a"] = ({
  template: __WEBPACK_IMPORTED_MODULE_0__template_html___default.a,
  data: function data() {
    return {
      toasts: [],
      options: defaultOptions
    };
  },

  computed: {
    classesOfPosition: function classesOfPosition() {
      return this._updateClassesOfPosition(this.options.position);
    },
    directionOfJumping: function directionOfJumping() {
      return this._updateDirectionOfJumping(this.options.position);
    }
  },
  methods: {
    // Public
    showToast: function showToast(message, options) {
      this._addToast(message, options);
      this._moveToast();

      return this;
    },
    setOptions: function setOptions(options) {
      this.options = Object.assign(this.options, options || {});

      return this;
    },

    // Private
    _addToast: function _addToast(message) {
      var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};

      if (!message) {
        return;
      }

      options.directionOfJumping = this.directionOfJumping;

      this.toasts.unshift({
        message: message,
        options: options,
        isDestroyed: false
      });
    },
    _moveToast: function _moveToast(toast) {
      var maxToasts = this.options.maxToasts > 0 ? this.options.maxToasts : 9999;

      // moving||removing old toasts
      this.toasts = this.toasts.reduceRight(function (prev, toast, i) {
        if (toast.isDestroyed) {
          return prev;
        }

        if (i + 1 >= maxToasts) {
          return prev;
        }

        return [toast].concat(prev);
      }, []);
    },
    _updateClassesOfPosition: function _updateClassesOfPosition(position) {
      return position.split(' ').reduce(function (prev, val) {
        prev['--' + val.toLowerCase()] = true;

        return prev;
      }, {});
    },
    _updateDirectionOfJumping: function _updateDirectionOfJumping(position) {
      return position.match(/top/i) ? '+' : '-';
    }
  },
  components: {
    'vue-toast': __WEBPACK_IMPORTED_MODULE_1__toast__["a" /* default */]
  }
});

/***/ }),

/***/ 704:
/***/ (function(module, exports) {

module.exports = "<div class=\"vue-toast-manager_container\" :class=\"classesOfPosition\">\n  <vue-toast\n      v-for=\"(toast, index) in toasts\"\n      :key=\"index\"\n      :message=\"toast.message\"\n      :options=\"toast.options\"\n      :destroyed.sync=\"toast.isDestroyed\"\n      :position=\"$index\"\n    ></vue-toast>\n</div>\n";

/***/ }),

/***/ 705:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__template_html__ = __webpack_require__(706);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__template_html___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0__template_html__);


var defaultOptions = {
    theme: 'default', // info warning error success
    timeLife: 5000,
    closeBtn: false
};

/* harmony default export */ __webpack_exports__["a"] = ({
    template: __WEBPACK_IMPORTED_MODULE_0__template_html___default.a,
    props: {
        message: {
            required: true
        },
        position: {
            type: Number,
            required: true
        },
        destroyed: {
            twoWay: true,
            type: Boolean,
            required: true
        },
        options: {
            type: Object,
            coerce: function coerce(options) {
                return Object.assign({}, defaultOptions, options);
            }
        }
    },
    data: function data() {
        return {
            isShow: false
        };
    },

    computed: {
        styles: function styles() {
            return ['alert-' + this.options.theme, this.options.closeBtn ? 'alert-dismissible' : null];
        },
        style: function style() {
            return 'transform: translateY(' + this.options.directionOfJumping + this.position * 100 + '%)';
        }
    },
    mounted: function mounted() {
        var _this = this;

        setTimeout(function () {
            _this.isShow = true;
        }, 50);

        if (this.options.timeLife) {
            this._startLazyAutoDestroy();
        }
    },
    detached: function detached() {
        clearTimeout(this.timerDestroy);
    },

    methods: {
        // Public
        remove: function remove() {
            this._clearTimer();
            this.destroyed = true;
            this.$remove().$destroy();

            return this;
        },

        // Private
        _startLazyAutoDestroy: function _startLazyAutoDestroy() {
            var _this2 = this;

            this._clearTimer();
            this.timerDestroy = setTimeout(function () {
                _this2.$remove().$destroy();
            }, this.options.timeLife);
        },
        _clearTimer: function _clearTimer() {
            if (this.timerDestroy) {
                clearTimeout(this.timerDestroy);
            }
        },
        _startTimer: function _startTimer() {
            if (this.options.timeLife) {
                this._startLazyAutoDestroy();
            }
        },
        _stopTimer: function _stopTimer() {
            if (this.options.timeLife) {
                this._clearTimer();
            }
        }
    }
});

/***/ }),

/***/ 706:
/***/ (function(module, exports) {

module.exports = "<div class=\"vue-toast_container\"\n     @mouseover=\"_stopTimer\"\n     @mouseleave=\"_startTimer\"\n     :style=\"style\"\n     v-show=\"isShow\"\n     transition>\n    <div class=\"vue-toast_message alert\" :class=\"styles\">\n        <button type=\"button\"\n                class=\"close\"\n                aria-label=\"Close\"\n                v-if=\"options.closeBtn\" @click=\"remove\">\n            <span aria-hidden=\"true\">×</span>\n        </button>\n        <span v-html=\"message\"></span>\n    </div>\n</div>\n";

/***/ }),

/***/ 707:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* unused harmony export isNumber */
function isNumber(value) {
    return typeof value === "number" && isFinite(value);
}

/***/ }),

/***/ 726:
/***/ (function(module, exports, __webpack_require__) {

/*
    portal-vue
    Version: 1.3.0
    Licence: MIT
    (c) Thorsten Lünborg
  */
  
(function (global, factory) {
	 true ? module.exports = factory(__webpack_require__(3)) :
	typeof define === 'function' && define.amd ? define(['vue'], factory) :
	(global.PortalVue = factory(global.Vue));
}(this, (function (Vue) { 'use strict';

Vue = Vue && 'default' in Vue ? Vue['default'] : Vue;

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) {
  return typeof obj;
} : function (obj) {
  return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj;
};





















var _extends = Object.assign || function (target) {
  for (var i = 1; i < arguments.length; i++) {
    var source = arguments[i];

    for (var key in source) {
      if (Object.prototype.hasOwnProperty.call(source, key)) {
        target[key] = source[key];
      }
    }
  }

  return target;
};



































var toConsumableArray = function (arr) {
  if (Array.isArray(arr)) {
    for (var i = 0, arr2 = Array(arr.length); i < arr.length; i++) arr2[i] = arr[i];

    return arr2;
  } else {
    return Array.from(arr);
  }
};

function extractAttributes(el) {
  var map = el.hasAttributes() ? el.attributes : [];
  var attrs = {};
  for (var i = 0; i < map.length; i++) {
    var attr = map[i];
    if (attr.value) {
      attrs[attr.name] = attr.value === '' ? true : attr.value;
    }
  }
  var klass = void 0,
      style = void 0;
  if (attrs.class) {
    klass = attrs.class;
    delete attrs.class;
  }
  if (attrs.style) {
    style = attrs.style;
    delete attrs.style;
  }
  var data = {
    attrs: attrs,
    class: klass,
    style: style
  };
  return data;
}

function freeze(item) {
  if (Array.isArray(item) || (typeof item === 'undefined' ? 'undefined' : _typeof(item)) === 'object') {
    return Object.freeze(item);
  }
  return item;
}

function combinePassengers(transports) {
  var slotProps = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};

  return transports.reduce(function (passengers, transport) {
    var newPassengers = transport.passengers[0];
    newPassengers = typeof newPassengers === 'function' ? newPassengers(slotProps) : transport.passengers;
    return passengers.concat(newPassengers);
  }, []);
}

var transports = {};

var Wormhole = Vue.extend({
  data: function data() {
    return { transports: transports };
  },
  methods: {
    open: function open(transport) {
      var to = transport.to,
          from = transport.from,
          passengers = transport.passengers;

      if (!to || !from || !passengers) return;

      transport.passengers = freeze(passengers);
      var keys = Object.keys(this.transports);
      if (keys.indexOf(to) === -1) {
        Vue.set(this.transports, to, []);
      }

      var currentIndex = this.getTransportIndex(transport);
      // Copying the array here so that the PortalTarget change event will actually contain two distinct arrays
      var newTransports = this.transports[to].slice(0);
      if (currentIndex === -1) {
        newTransports.push(transport);
      } else {
        newTransports[currentIndex] = transport;
      }
      newTransports.sort(function (a, b) {
        return a.order - b.order;
      });

      this.transports[to] = newTransports;
    },
    close: function close(transport) {
      var force = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
      var to = transport.to,
          from = transport.from;

      if (!to || !from) return;
      if (!this.transports[to]) {
        return;
      }

      if (force) {
        this.transports[to] = [];
      } else {
        var index = this.getTransportIndex(transport);
        if (index >= 0) {
          // Copying the array here so that the PortalTarget change event will actually contain two distinct arrays
          var newTransports = this.transports[to].slice(0);
          newTransports.splice(index, 1);
          this.transports[to] = newTransports;
        }
      }
    },
    hasTarget: function hasTarget(to) {
      return this.transports.hasOwnProperty(to);
    },
    hasContentFor: function hasContentFor(to) {
      if (!this.transports[to]) {
        return false;
      }
      return this.getContentFor(to).length > 0;
    },
    getSourceFor: function getSourceFor(to) {
      return this.transports[to] && this.transports[to][0].from;
    },
    getContentFor: function getContentFor(to) {
      var transports = this.transports[to];
      if (!transports) {
        return undefined;
      }
      return combinePassengers(transports);
    },
    getTransportIndex: function getTransportIndex(_ref) {
      var to = _ref.to,
          from = _ref.from;

      for (var i in this.transports[to]) {
        if (this.transports[to][i].from === from) {
          return i;
        }
      }
      return -1;
    }
  }
});

var wormhole = new Wormhole(transports);

var nestRE = /^(attrs|props|on|nativeOn|class|style|hook)$/;

var babelHelperVueJsxMergeProps = function mergeJSXProps (objs) {
  return objs.reduce(function (a, b) {
    var aa, bb, key, nestedKey, temp;
    for (key in b) {
      aa = a[key];
      bb = b[key];
      if (aa && nestRE.test(key)) {
        // normalize class
        if (key === 'class') {
          if (typeof aa === 'string') {
            temp = aa;
            a[key] = aa = {};
            aa[temp] = true;
          }
          if (typeof bb === 'string') {
            temp = bb;
            b[key] = bb = {};
            bb[temp] = true;
          }
        }
        if (key === 'on' || key === 'nativeOn' || key === 'hook') {
          // merge functions
          for (nestedKey in bb) {
            aa[nestedKey] = mergeFn(aa[nestedKey], bb[nestedKey]);
          }
        } else if (Array.isArray(aa)) {
          a[key] = aa.concat(bb);
        } else if (Array.isArray(bb)) {
          a[key] = [aa].concat(bb);
        } else {
          for (nestedKey in bb) {
            aa[nestedKey] = bb[nestedKey];
          }
        }
      } else {
        a[key] = b[key];
      }
    }
    return a
  }, {})
};

function mergeFn (a, b) {
  return function () {
    a && a.apply(this, arguments);
    b && b.apply(this, arguments);
  }
}

// import { transports } from './wormhole'
var Target = {
  abstract: false,
  name: 'portalTarget',
  props: {
    attributes: { type: Object, default: function _default() {
        return {};
      } },
    multiple: { type: Boolean, default: false },
    name: { type: String, required: true },
    slim: { type: Boolean, default: false },
    slotProps: { type: Object, default: function _default() {
        return {};
      } },
    tag: { type: String, default: 'div' },
    transition: { type: [Boolean, String, Object], default: false },
    transitionEvents: { type: Object, default: function _default() {
        return {};
      } }
  },
  data: function data() {
    return {
      transports: wormhole.transports,
      firstRender: true
    };
  },
  created: function created() {
    if (!this.transports[this.name]) {
      this.$set(this.transports, this.name, []);
    }
  },
  mounted: function mounted() {
    var _this = this;

    this.unwatch = this.$watch('ownTransports', this.emitChange);
    this.$nextTick(function () {
      if (_this.transition) {
        // only when we have a transition, because it causes a re-render
        _this.firstRender = false;
      }
    });
    if (this.$options.abstract) {
      this.$options.abstract = false;
    }
  },
  updated: function updated() {
    if (this.$options.abstract) {
      this.$options.abstract = false;
    }
  },
  beforeDestroy: function beforeDestroy() {
    this.unwatch();
    this.$el.innerHTML = '';
  },


  methods: {
    emitChange: function emitChange(newTransports, oldTransports) {
      if (this.multiple) {
        this.$emit('change', [].concat(toConsumableArray(newTransports)), [].concat(toConsumableArray(oldTransports)));
      } else {
        var newTransport = newTransports.length === 0 ? undefined : newTransports[0];
        var oldTransport = oldTransports.length === 0 ? undefined : oldTransports[0];
        this.$emit('change', _extends({}, newTransport), _extends({}, oldTransport));
      }
    }
  },
  computed: {
    ownTransports: function ownTransports() {
      var transports$$1 = this.transports[this.name] || [];
      if (this.multiple) {
        return transports$$1;
      }
      return transports$$1.length === 0 ? [] : [transports$$1[transports$$1.length - 1]];
    },
    passengers: function passengers() {
      return combinePassengers(this.ownTransports, this.slotProps);
    },
    children: function children() {
      return this.passengers.length !== 0 ? this.passengers : this.$slots.default || [];
    },
    hasAttributes: function hasAttributes() {
      return Object.keys(this.attributes).length > 0;
    },
    noWrapper: function noWrapper() {
      var noWrapper = !this.hasAttributes && this.slim;
      if (noWrapper && this.children.length > 1) {
        console.warn('[portal-vue]: PortalTarget with `slim` option received more than one child element.');
      }
      return noWrapper;
    },
    withTransition: function withTransition() {
      return !!this.transition;
    },
    transitionData: function transitionData() {
      var t = this.transition;
      var data = {};

      // During first render, we render a dumb transition without any classes, events and a fake name
      // We have to do this to emulate the normal behaviour of transitions without `appear`
      // because in Portals, transitions can behave as if appear was defined under certain conditions.
      if (this.firstRender && _typeof(this.transition) === 'object' && !this.transition.appear) {
        data.props = { name: '__notranstition__portal-vue__' };
        return data;
      }

      if (typeof t === 'string') {
        data.props = { name: t };
      } else if ((typeof t === 'undefined' ? 'undefined' : _typeof(t)) === 'object') {
        data.props = t;
      }
      if (this.renderSlim) {
        data.props.tag = this.tag;
      }
      data.on = this.transitionEvents;

      return data;
    }
  },

  render: function render(h) {
    this.$options.abstract = true;
    var TransitionType = this.noWrapper ? 'transition' : 'transition-group';
    var Tag = this.tag;

    if (this.withTransition) {
      return h(
        TransitionType,
        babelHelperVueJsxMergeProps([this.transitionData, { 'class': 'vue-portal-target' }]),
        [this.children]
      );
    }

    // Solves a bug where Vue would sometimes duplicate elements upon changing multiple or disabled
    var wrapperKey = this.ownTransports.length;

    return this.noWrapper ? this.children[0] : h(
      Tag,
      babelHelperVueJsxMergeProps([{ 'class': 'vue-portal-target' }, this.attributes, { key: wrapperKey }]),
      [this.children]
    );
  }
};

var inBrowser = typeof window !== 'undefined';

var pid = 1;

var Portal = {
  abstract: false,
  name: 'portal',
  props: {
    /* global HTMLElement */
    disabled: { type: Boolean, default: false },
    name: { type: String, default: function _default() {
        return String(pid++);
      } },
    order: { type: Number, default: 0 },
    slim: { type: Boolean, default: false },
    slotProps: { type: Object, default: function _default() {
        return {};
      } },
    tag: { type: [String], default: 'DIV' },
    targetEl: { type: inBrowser ? [String, HTMLElement] : String },
    to: {
      type: String,
      default: function _default() {
        return String(Math.round(Math.random() * 10000000));
      }
    }
  },

  mounted: function mounted() {
    if (this.targetEl) {
      this.mountToTarget();
    }
    if (!this.disabled) {
      this.sendUpdate();
    }
    // Reset hack to make child components skip the portal when defining their $parent
    // was set to true during render when we render something locally.
    if (this.$options.abstract) {
      this.$options.abstract = false;
    }
  },
  updated: function updated() {
    if (this.disabled) {
      this.clear();
    } else {
      this.sendUpdate();
    }
    // Reset hack to make child components skip the portal when defining their $parent
    // was set to true during render when we render something locally.
    if (this.$options.abstract) {
      this.$options.abstract = false;
    }
  },
  beforeDestroy: function beforeDestroy() {
    this.clear();
    if (this.mountedComp) {
      this.mountedComp.$destroy();
    }
  },

  watch: {
    to: function to(newValue, oldValue) {
      oldValue && this.clear(oldValue);
      this.sendUpdate();
    },
    targetEl: function targetEl(newValue, oldValue) {
      if (newValue) {
        this.mountToTarget();
      }
    }
  },

  methods: {
    normalizedSlots: function normalizedSlots() {
      return this.$scopedSlots.default ? [this.$scopedSlots.default] : this.$slots.default;
    },
    sendUpdate: function sendUpdate() {
      var slotContent = this.normalizedSlots();
      if (slotContent) {
        wormhole.open({
          from: this.name,
          to: this.to,
          passengers: [].concat(toConsumableArray(slotContent)),
          order: this.order
        });
      } else {
        this.clear();
      }
    },
    clear: function clear(target) {
      wormhole.close({
        from: this.name,
        to: target || this.to
      });
    },
    mountToTarget: function mountToTarget() {
      var el = void 0;
      var target = this.targetEl;

      if (typeof target === 'string') {
        el = document.querySelector(target);
      } else if (target instanceof HTMLElement) {
        el = target;
      } else {
        console.warn('[vue-portal]: value of targetEl must be of type String or HTMLElement');
        return;
      }

      if (el) {
        var newTarget = new Vue(_extends({}, Target, {
          parent: this,
          propsData: {
            name: this.to,
            tag: el.tagName,
            attributes: extractAttributes(el)
          }
        }));
        newTarget.$mount(el);
        this.mountedComp = newTarget;
      } else {
        console.warn('[vue-portal]: The specified targetEl ' + target + ' was not found');
      }
    },
    normalizeChildren: function normalizeChildren(children) {
      return typeof children === 'function' ? children(this.slotProps) : children;
    }
  },

  render: function render(h) {
    var children = this.$slots.default || this.$scopedSlots.default || [];
    var Tag = this.tag;
    if (children.length && this.disabled) {
      // hack to make child components skip the portal when defining their $parent
      this.$options.abstract = true;
      return children.length <= 1 && this.slim ? children[0] : h(
        Tag,
        null,
        [this.normalizeChildren(children)]
      );
    } else {
      return h(
        Tag,
        {
          'class': 'v-portal',
          style: 'display: none',
          key: 'v-portal-placeholder'
        },
        []
      );
      // h(this.tag, { class: { 'v-portal': true }, style: { display: 'none' }, key: 'v-portal-placeholder' })
    }
  }
};

function install(Vue$$1) {
  var opts = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};

  Vue$$1.component(opts.portalName || 'portal', Portal);
  Vue$$1.component(opts.portalTargetName || 'portalTarget', Target);
}
if (typeof window !== 'undefined' && window.Vue) {
  window.Vue.use({ install: install });
}

var index = {
  install: install,
  Portal: Portal,
  PortalTarget: Target,
  Wormhole: wormhole
};

return index;

})));
//# sourceMappingURL=portal-vue.js.map


/***/ }),

/***/ 727:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_vue__ = __webpack_require__(3);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_vue___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_vue__);
var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }



var NotificationBus = function () {
    function NotificationBus(instance) {
        _classCallCheck(this, NotificationBus);

        this.instance = instance;
    }

    _createClass(NotificationBus, [{
        key: 'success',
        value: function success(message, opts) {
            this.instance.$dispatch('setFlashSuccess', message, opts);
        }
    }, {
        key: 'error',
        value: function error(message, opts) {
            this.instance.$dispatch('setFlashError', message, opts);
        }
    }]);

    return NotificationBus;
}();

Object.defineProperties(__WEBPACK_IMPORTED_MODULE_0_vue___default.a.prototype, {
    $notify: {
        get: function get() {
            return new NotificationBus(this);
        }
    }
});

/***/ })

},[698]);
//# sourceMappingURL=app.js.map