
    /*!
    * tiptap v1.13.0
    * (c) 2019 Scrumpy UG (limited liability)
    * @license MIT
    */
  
import { EditorState, Plugin, PluginKey } from 'prosemirror-state';
export { Plugin, PluginKey, TextSelection, NodeSelection } from 'prosemirror-state';
import { EditorView } from 'prosemirror-view';
import { Schema, DOMParser, DOMSerializer } from 'prosemirror-model';
import { dropCursor } from 'prosemirror-dropcursor';
import { gapCursor } from 'prosemirror-gapcursor';
import { keymap } from 'prosemirror-keymap';
import { selectParentNode, baseKeymap } from 'prosemirror-commands';
import { inputRules, undoInputRule } from 'prosemirror-inputrules';
import { markIsActive, getMarkAttrs, nodeIsActive } from 'tiptap-utils';
import Vue from 'vue';
import { setBlockType } from 'tiptap-commands';

function _typeof(obj) {
  if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") {
    _typeof = function (obj) {
      return typeof obj;
    };
  } else {
    _typeof = function (obj) {
      return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj;
    };
  }

  return _typeof(obj);
}

function _classCallCheck(instance, Constructor) {
  if (!(instance instanceof Constructor)) {
    throw new TypeError("Cannot call a class as a function");
  }
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
  return Constructor;
}

function _defineProperty(obj, key, value) {
  if (key in obj) {
    Object.defineProperty(obj, key, {
      value: value,
      enumerable: true,
      configurable: true,
      writable: true
    });
  } else {
    obj[key] = value;
  }

  return obj;
}

function _objectSpread(target) {
  for (var i = 1; i < arguments.length; i++) {
    var source = arguments[i] != null ? arguments[i] : {};
    var ownKeys = Object.keys(source);

    if (typeof Object.getOwnPropertySymbols === 'function') {
      ownKeys = ownKeys.concat(Object.getOwnPropertySymbols(source).filter(function (sym) {
        return Object.getOwnPropertyDescriptor(source, sym).enumerable;
      }));
    }

    ownKeys.forEach(function (key) {
      _defineProperty(target, key, source[key]);
    });
  }

  return target;
}

function _inherits(subClass, superClass) {
  if (typeof superClass !== "function" && superClass !== null) {
    throw new TypeError("Super expression must either be null or a function");
  }

  subClass.prototype = Object.create(superClass && superClass.prototype, {
    constructor: {
      value: subClass,
      writable: true,
      configurable: true
    }
  });
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

function _assertThisInitialized(self) {
  if (self === void 0) {
    throw new ReferenceError("this hasn't been initialised - super() hasn't been called");
  }

  return self;
}

function _possibleConstructorReturn(self, call) {
  if (call && (typeof call === "object" || typeof call === "function")) {
    return call;
  }

  return _assertThisInitialized(self);
}

function _slicedToArray(arr, i) {
  return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _nonIterableRest();
}

function _toConsumableArray(arr) {
  return _arrayWithoutHoles(arr) || _iterableToArray(arr) || _nonIterableSpread();
}

function _arrayWithoutHoles(arr) {
  if (Array.isArray(arr)) {
    for (var i = 0, arr2 = new Array(arr.length); i < arr.length; i++) arr2[i] = arr[i];

    return arr2;
  }
}

function _arrayWithHoles(arr) {
  if (Array.isArray(arr)) return arr;
}

function _iterableToArray(iter) {
  if (Symbol.iterator in Object(iter) || Object.prototype.toString.call(iter) === "[object Arguments]") return Array.from(iter);
}

function _iterableToArrayLimit(arr, i) {
  var _arr = [];
  var _n = true;
  var _d = false;
  var _e = undefined;

  try {
    for (var _i = arr[Symbol.iterator](), _s; !(_n = (_s = _i.next()).done); _n = true) {
      _arr.push(_s.value);

      if (i && _arr.length === i) break;
    }
  } catch (err) {
    _d = true;
    _e = err;
  } finally {
    try {
      if (!_n && _i["return"] != null) _i["return"]();
    } finally {
      if (_d) throw _e;
    }
  }

  return _arr;
}

function _nonIterableSpread() {
  throw new TypeError("Invalid attempt to spread non-iterable instance");
}

function _nonIterableRest() {
  throw new TypeError("Invalid attempt to destructure non-iterable instance");
}

var ComponentView =
/*#__PURE__*/
function () {
  function ComponentView(component, _ref) {
    var extension = _ref.extension,
        parent = _ref.parent,
        node = _ref.node,
        view = _ref.view,
        getPos = _ref.getPos,
        decorations = _ref.decorations,
        editable = _ref.editable;

    _classCallCheck(this, ComponentView);

    this.component = component;
    this.extension = extension;
    this.parent = parent;
    this.node = node;
    this.view = view;
    this.getPos = getPos;
    this.decorations = decorations;
    this.editable = editable;
    this.selected = false;
    this.dom = this.createDOM();
    this.contentDOM = this.vm.$refs.content;
  }

  _createClass(ComponentView, [{
    key: "createDOM",
    value: function createDOM() {
      var _this = this;

      var Component = Vue.extend(this.component);
      this.vm = new Component({
        parent: this.parent,
        propsData: {
          node: this.node,
          view: this.view,
          getPos: this.getPos,
          decorations: this.decorations,
          editable: this.editable,
          selected: false,
          options: this.extension.options,
          updateAttrs: function updateAttrs(attrs) {
            return _this.updateAttrs(attrs);
          },
          updateContent: function updateContent(content) {
            return _this.updateContent(content);
          }
        }
      }).$mount();
      return this.vm.$el;
    }
  }, {
    key: "update",
    value: function update(node, decorations) {
      if (node.type !== this.node.type) {
        return false;
      }

      if (node === this.node && this.decorations === decorations) {
        return true;
      }

      this.node = node;
      this.decorations = decorations;
      this.updateComponentProps({
        node: node,
        decorations: decorations
      });
      return true;
    }
  }, {
    key: "updateComponentProps",
    value: function updateComponentProps(props) {
      var _this2 = this;

      // Update props in component
      // TODO: Avoid mutating a prop directly.
      // Maybe there is a better way to do this?
      var originalSilent = Vue.config.silent;
      Vue.config.silent = true;
      Object.entries(props).forEach(function (_ref2) {
        var _ref3 = _slicedToArray(_ref2, 2),
            key = _ref3[0],
            value = _ref3[1];

        _this2.vm._props[key] = value;
      }); // this.vm._props.node = node
      // this.vm._props.decorations = decorations

      Vue.config.silent = originalSilent;
    }
  }, {
    key: "updateAttrs",
    value: function updateAttrs(attrs) {
      if (!this.editable) {
        return;
      }

      var transaction = this.view.state.tr.setNodeMarkup(this.getPos(), null, _objectSpread({}, this.node.attrs, attrs));
      this.view.dispatch(transaction);
    }
  }, {
    key: "updateContent",
    value: function updateContent(content) {
      if (!this.editable) {
        return;
      }

      var transaction = this.view.state.tr.setNodeMarkup(this.getPos(), this.node.type, {
        content: content
      });
      this.view.dispatch(transaction);
    } // prevent a full re-render of the vue component on update
    // we'll handle prop updates in `update()`

  }, {
    key: "ignoreMutation",
    value: function ignoreMutation() {
      return true;
    } // disable (almost) all prosemirror event listener for node views

  }, {
    key: "stopEvent",
    value: function stopEvent(event) {
      var isPaste = event.type === 'paste';
      var draggable = !!this.extension.schema.draggable;

      if (draggable || isPaste) {
        return false;
      }

      return true;
    }
  }, {
    key: "selectNode",
    value: function selectNode() {
      this.updateComponentProps({
        selected: true
      });
    }
  }, {
    key: "deselectNode",
    value: function deselectNode() {
      this.updateComponentProps({
        selected: false
      });
    }
  }, {
    key: "destroy",
    value: function destroy() {
      this.vm.$destroy();
    }
  }]);

  return ComponentView;
}();

var Extension =
/*#__PURE__*/
function () {
  function Extension() {
    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};

    _classCallCheck(this, Extension);

    this.options = _objectSpread({}, this.defaultOptions, options);
  }

  _createClass(Extension, [{
    key: "inputRules",
    value: function inputRules() {
      return [];
    }
  }, {
    key: "pasteRules",
    value: function pasteRules() {
      return [];
    }
  }, {
    key: "keys",
    value: function keys() {
      return {};
    }
  }, {
    key: "name",
    get: function get() {
      return null;
    }
  }, {
    key: "type",
    get: function get() {
      return 'extension';
    }
  }, {
    key: "update",
    get: function get() {
      return function () {};
    }
  }, {
    key: "defaultOptions",
    get: function get() {
      return {};
    }
  }, {
    key: "plugins",
    get: function get() {
      return [];
    }
  }]);

  return Extension;
}();

var ExtensionManager =
/*#__PURE__*/
function () {
  function ExtensionManager() {
    var extensions = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : [];

    _classCallCheck(this, ExtensionManager);

    this.extensions = extensions;
  }

  _createClass(ExtensionManager, [{
    key: "keymaps",
    value: function keymaps(_ref) {
      var schema = _ref.schema;
      var extensionKeymaps = this.extensions.filter(function (extension) {
        return ['extension'].includes(extension.type);
      }).filter(function (extension) {
        return extension.keys;
      }).map(function (extension) {
        return extension.keys({
          schema: schema
        });
      });
      var nodeMarkKeymaps = this.extensions.filter(function (extension) {
        return ['node', 'mark'].includes(extension.type);
      }).filter(function (extension) {
        return extension.keys;
      }).map(function (extension) {
        return extension.keys({
          type: schema["".concat(extension.type, "s")][extension.name],
          schema: schema
        });
      });
      return _toConsumableArray(extensionKeymaps).concat(_toConsumableArray(nodeMarkKeymaps)).map(function (keys) {
        return keymap(keys);
      });
    }
  }, {
    key: "inputRules",
    value: function inputRules(_ref2) {
      var schema = _ref2.schema;
      var extensionInputRules = this.extensions.filter(function (extension) {
        return ['extension'].includes(extension.type);
      }).filter(function (extension) {
        return extension.inputRules;
      }).map(function (extension) {
        return extension.inputRules({
          schema: schema
        });
      });
      var nodeMarkInputRules = this.extensions.filter(function (extension) {
        return ['node', 'mark'].includes(extension.type);
      }).filter(function (extension) {
        return extension.inputRules;
      }).map(function (extension) {
        return extension.inputRules({
          type: schema["".concat(extension.type, "s")][extension.name],
          schema: schema
        });
      });
      return _toConsumableArray(extensionInputRules).concat(_toConsumableArray(nodeMarkInputRules)).reduce(function (allInputRules, inputRules) {
        return _toConsumableArray(allInputRules).concat(_toConsumableArray(inputRules));
      }, []);
    }
  }, {
    key: "pasteRules",
    value: function pasteRules(_ref3) {
      var schema = _ref3.schema;
      var extensionPasteRules = this.extensions.filter(function (extension) {
        return ['extension'].includes(extension.type);
      }).filter(function (extension) {
        return extension.pasteRules;
      }).map(function (extension) {
        return extension.pasteRules({
          schema: schema
        });
      });
      var nodeMarkPasteRules = this.extensions.filter(function (extension) {
        return ['node', 'mark'].includes(extension.type);
      }).filter(function (extension) {
        return extension.pasteRules;
      }).map(function (extension) {
        return extension.pasteRules({
          type: schema["".concat(extension.type, "s")][extension.name],
          schema: schema
        });
      });
      return _toConsumableArray(extensionPasteRules).concat(_toConsumableArray(nodeMarkPasteRules)).reduce(function (allPasteRules, pasteRules) {
        return _toConsumableArray(allPasteRules).concat(_toConsumableArray(pasteRules));
      }, []);
    }
  }, {
    key: "commands",
    value: function commands(_ref4) {
      var schema = _ref4.schema,
          view = _ref4.view,
          editable = _ref4.editable;
      return this.extensions.filter(function (extension) {
        return extension.commands;
      }).reduce(function (allCommands, extension) {
        var name = extension.name,
            type = extension.type;
        var commands = {};
        var value = extension.commands(_objectSpread({
          schema: schema
        }, ['node', 'mark'].includes(type) ? {
          type: schema["".concat(type, "s")][name]
        } : {}));

        if (Array.isArray(value)) {
          commands[name] = function (attrs) {
            return value.forEach(function (callback) {
              if (!editable) {
                return false;
              }

              view.focus();
              return callback(attrs)(view.state, view.dispatch, view);
            });
          };
        } else if (typeof value === 'function') {
          commands[name] = function (attrs) {
            if (!editable) {
              return false;
            }

            view.focus();
            return value(attrs)(view.state, view.dispatch, view);
          };
        } else if (_typeof(value) === 'object') {
          Object.entries(value).forEach(function (_ref5) {
            var _ref6 = _slicedToArray(_ref5, 2),
                commandName = _ref6[0],
                commandValue = _ref6[1];

            if (Array.isArray(commandValue)) {
              commands[commandName] = function (attrs) {
                return commandValue.forEach(function (callback) {
                  if (!editable) {
                    return false;
                  }

                  view.focus();
                  return callback(attrs)(view.state, view.dispatch, view);
                });
              };
            } else {
              commands[commandName] = function (attrs) {
                if (!editable) {
                  return false;
                }

                view.focus();
                return commandValue(attrs)(view.state, view.dispatch, view);
              };
            }
          });
        }

        return _objectSpread({}, allCommands, commands);
      }, {});
    }
  }, {
    key: "nodes",
    get: function get() {
      return this.extensions.filter(function (extension) {
        return extension.type === 'node';
      }).reduce(function (nodes, _ref7) {
        var name = _ref7.name,
            schema = _ref7.schema;
        return _objectSpread({}, nodes, _defineProperty({}, name, schema));
      }, {});
    }
  }, {
    key: "options",
    get: function get() {
      var view = this.view;
      return this.extensions.reduce(function (nodes, extension) {
        return _objectSpread({}, nodes, _defineProperty({}, extension.name, new Proxy(extension.options, {
          set: function set(obj, prop, value) {
            var changed = obj[prop] !== value;
            Object.assign(obj, _defineProperty({}, prop, value));

            if (changed) {
              extension.update(view);
            }

            return true;
          }
        })));
      }, {});
    }
  }, {
    key: "marks",
    get: function get() {
      return this.extensions.filter(function (extension) {
        return extension.type === 'mark';
      }).reduce(function (marks, _ref8) {
        var name = _ref8.name,
            schema = _ref8.schema;
        return _objectSpread({}, marks, _defineProperty({}, name, schema));
      }, {});
    }
  }, {
    key: "plugins",
    get: function get() {
      return this.extensions.filter(function (extension) {
        return extension.plugins;
      }).reduce(function (allPlugins, _ref9) {
        var plugins = _ref9.plugins;
        return _toConsumableArray(allPlugins).concat(_toConsumableArray(plugins));
      }, []);
    }
  }]);

  return ExtensionManager;
}();

var Mark =
/*#__PURE__*/
function (_Extension) {
  _inherits(Mark, _Extension);

  function Mark() {
    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};

    _classCallCheck(this, Mark);

    return _possibleConstructorReturn(this, _getPrototypeOf(Mark).call(this, options));
  }

  _createClass(Mark, [{
    key: "command",
    value: function command() {
      return function () {};
    }
  }, {
    key: "type",
    get: function get() {
      return 'mark';
    }
  }, {
    key: "view",
    get: function get() {
      return null;
    }
  }, {
    key: "schema",
    get: function get() {
      return null;
    }
  }]);

  return Mark;
}(Extension);

var Node =
/*#__PURE__*/
function (_Extension) {
  _inherits(Node, _Extension);

  function Node() {
    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};

    _classCallCheck(this, Node);

    return _possibleConstructorReturn(this, _getPrototypeOf(Node).call(this, options));
  }

  _createClass(Node, [{
    key: "command",
    value: function command() {
      return function () {};
    }
  }, {
    key: "type",
    get: function get() {
      return 'node';
    }
  }, {
    key: "view",
    get: function get() {
      return null;
    }
  }, {
    key: "schema",
    get: function get() {
      return null;
    }
  }]);

  return Node;
}(Extension);

var Doc =
/*#__PURE__*/
function (_Node) {
  _inherits(Doc, _Node);

  function Doc() {
    _classCallCheck(this, Doc);

    return _possibleConstructorReturn(this, _getPrototypeOf(Doc).apply(this, arguments));
  }

  _createClass(Doc, [{
    key: "name",
    get: function get() {
      return 'doc';
    }
  }, {
    key: "schema",
    get: function get() {
      return {
        content: 'block+'
      };
    }
  }]);

  return Doc;
}(Node);

var Paragraph =
/*#__PURE__*/
function (_Node) {
  _inherits(Paragraph, _Node);

  function Paragraph() {
    _classCallCheck(this, Paragraph);

    return _possibleConstructorReturn(this, _getPrototypeOf(Paragraph).apply(this, arguments));
  }

  _createClass(Paragraph, [{
    key: "commands",
    value: function commands(_ref) {
      var type = _ref.type;
      return function () {
        return setBlockType(type);
      };
    }
  }, {
    key: "name",
    get: function get() {
      return 'paragraph';
    }
  }, {
    key: "schema",
    get: function get() {
      return {
        content: 'inline*',
        group: 'block',
        draggable: false,
        parseDOM: [{
          tag: 'p'
        }],
        toDOM: function toDOM() {
          return ['p', 0];
        }
      };
    }
  }]);

  return Paragraph;
}(Node);

var Text =
/*#__PURE__*/
function (_Node) {
  _inherits(Text, _Node);

  function Text() {
    _classCallCheck(this, Text);

    return _possibleConstructorReturn(this, _getPrototypeOf(Text).apply(this, arguments));
  }

  _createClass(Text, [{
    key: "name",
    get: function get() {
      return 'text';
    }
  }, {
    key: "schema",
    get: function get() {
      return {
        group: 'inline'
      };
    }
  }]);

  return Text;
}(Node);

var Editor =
/*#__PURE__*/
function () {
  function Editor() {
    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};

    _classCallCheck(this, Editor);

    this.defaultOptions = {
      editorProps: {},
      editable: true,
      autoFocus: false,
      extensions: [],
      content: '',
      emptyDocument: {
        type: 'doc',
        content: [{
          type: 'paragraph'
        }]
      },
      useBuiltInExtensions: true,
      dropCursor: {},
      onInit: function onInit() {},
      onUpdate: function onUpdate() {},
      onFocus: function onFocus() {},
      onBlur: function onBlur() {},
      onPaste: function onPaste() {},
      onDrop: function onDrop() {}
    };
    this.init(options);
  }

  _createClass(Editor, [{
    key: "init",
    value: function init() {
      var _this = this;

      var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
      this.setOptions(_objectSpread({}, this.defaultOptions, options));
      this.element = document.createElement('div');
      this.extensions = this.createExtensions();
      this.nodes = this.createNodes();
      this.marks = this.createMarks();
      this.schema = this.createSchema();
      this.plugins = this.createPlugins();
      this.keymaps = this.createKeymaps();
      this.inputRules = this.createInputRules();
      this.pasteRules = this.createPasteRules();
      this.state = this.createState();
      this.view = this.createView();
      this.commands = this.createCommands();
      this.setActiveNodesAndMarks();

      if (this.options.autoFocus) {
        setTimeout(function () {
          _this.focus();
        }, 10);
      }

      this.options.onInit({
        view: this.view,
        state: this.state
      }); // give extension manager access to our view

      this.extensions.view = this.view;
    }
  }, {
    key: "setOptions",
    value: function setOptions(options) {
      this.options = _objectSpread({}, this.options, options);

      if (this.view && this.state) {
        this.view.updateState(this.state);
      }
    }
  }, {
    key: "createExtensions",
    value: function createExtensions() {
      return new ExtensionManager(_toConsumableArray(this.builtInExtensions).concat(_toConsumableArray(this.options.extensions)));
    }
  }, {
    key: "createPlugins",
    value: function createPlugins() {
      return this.extensions.plugins;
    }
  }, {
    key: "createKeymaps",
    value: function createKeymaps() {
      return this.extensions.keymaps({
        schema: this.schema
      });
    }
  }, {
    key: "createInputRules",
    value: function createInputRules() {
      return this.extensions.inputRules({
        schema: this.schema
      });
    }
  }, {
    key: "createPasteRules",
    value: function createPasteRules() {
      return this.extensions.pasteRules({
        schema: this.schema
      });
    }
  }, {
    key: "createCommands",
    value: function createCommands() {
      return this.extensions.commands({
        schema: this.schema,
        view: this.view,
        editable: this.options.editable
      });
    }
  }, {
    key: "createNodes",
    value: function createNodes() {
      return this.extensions.nodes;
    }
  }, {
    key: "createMarks",
    value: function createMarks() {
      return this.extensions.marks;
    }
  }, {
    key: "createSchema",
    value: function createSchema() {
      return new Schema({
        nodes: this.nodes,
        marks: this.marks
      });
    }
  }, {
    key: "createState",
    value: function createState() {
      var _this2 = this;

      return EditorState.create({
        schema: this.schema,
        doc: this.createDocument(this.options.content),
        plugins: _toConsumableArray(this.plugins).concat([inputRules({
          rules: this.inputRules
        })], _toConsumableArray(this.pasteRules), _toConsumableArray(this.keymaps), [keymap({
          Backspace: undoInputRule,
          Escape: selectParentNode
        }), keymap(baseKeymap), dropCursor(this.options.dropCursor), gapCursor(), new Plugin({
          key: new PluginKey('editable'),
          props: {
            editable: function editable() {
              return _this2.options.editable;
            }
          }
        }), new Plugin({
          props: {
            attributes: {
              tabindex: 0
            }
          }
        }), new Plugin({
          props: this.options.editorProps
        })])
      });
    }
  }, {
    key: "createDocument",
    value: function createDocument(content) {
      if (content === null) {
        return this.schema.nodeFromJSON(this.options.emptyDocument);
      }

      if (_typeof(content) === 'object') {
        try {
          return this.schema.nodeFromJSON(content);
        } catch (error) {
          console.warn('[tiptap warn]: Invalid content.', 'Passed value:', content, 'Error:', error);
          return this.schema.nodeFromJSON(this.options.emptyDocument);
        }
      }

      if (typeof content === 'string') {
        var element = document.createElement('div');
        element.innerHTML = content.trim();
        return DOMParser.fromSchema(this.schema).parse(element);
      }

      return false;
    }
  }, {
    key: "createView",
    value: function createView() {
      var _this3 = this;

      var view = new EditorView(this.element, {
        state: this.state,
        handlePaste: this.options.onPaste,
        handleDrop: this.options.onDrop,
        dispatchTransaction: this.dispatchTransaction.bind(this)
      });
      view.dom.style.whiteSpace = 'pre-wrap';
      view.dom.addEventListener('focus', function (event) {
        return _this3.options.onFocus({
          event: event,
          state: _this3.state,
          view: _this3.view
        });
      });
      view.dom.addEventListener('blur', function (event) {
        return _this3.options.onBlur({
          event: event,
          state: _this3.state,
          view: _this3.view
        });
      });
      return view;
    }
  }, {
    key: "setParentComponent",
    value: function setParentComponent() {
      var component = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;

      if (!component) {
        return;
      }

      this.view.setProps({
        nodeViews: this.initNodeViews({
          parent: component,
          extensions: _toConsumableArray(this.builtInExtensions).concat(_toConsumableArray(this.options.extensions)),
          editable: this.options.editable
        })
      });
    }
  }, {
    key: "initNodeViews",
    value: function initNodeViews(_ref) {
      var parent = _ref.parent,
          extensions = _ref.extensions,
          editable = _ref.editable;
      return extensions.filter(function (extension) {
        return ['node', 'mark'].includes(extension.type);
      }).filter(function (extension) {
        return extension.view;
      }).reduce(function (nodeViews, extension) {
        var nodeView = function nodeView(node, view, getPos, decorations) {
          var component = extension.view;
          return new ComponentView(component, {
            extension: extension,
            parent: parent,
            node: node,
            view: view,
            getPos: getPos,
            decorations: decorations,
            editable: editable
          });
        };

        return _objectSpread({}, nodeViews, _defineProperty({}, extension.name, nodeView));
      }, {});
    }
  }, {
    key: "dispatchTransaction",
    value: function dispatchTransaction(transaction) {
      this.state = this.state.apply(transaction);
      this.view.updateState(this.state);
      this.setActiveNodesAndMarks();

      if (!transaction.docChanged) {
        return;
      }

      this.emitUpdate(transaction);
    }
  }, {
    key: "emitUpdate",
    value: function emitUpdate(transaction) {
      this.options.onUpdate({
        getHTML: this.getHTML.bind(this),
        getJSON: this.getJSON.bind(this),
        state: this.state,
        transaction: transaction
      });
    }
  }, {
    key: "focus",
    value: function focus() {
      this.view.focus();
    }
  }, {
    key: "blur",
    value: function blur() {
      this.view.dom.blur();
    }
  }, {
    key: "getHTML",
    value: function getHTML() {
      var div = document.createElement('div');
      var fragment = DOMSerializer.fromSchema(this.schema).serializeFragment(this.state.doc.content);
      div.appendChild(fragment);
      return div.innerHTML;
    }
  }, {
    key: "getJSON",
    value: function getJSON() {
      return this.state.doc.toJSON();
    }
  }, {
    key: "setContent",
    value: function setContent() {
      var content = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
      var emitUpdate = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
      this.state = EditorState.create({
        schema: this.state.schema,
        doc: this.createDocument(content),
        plugins: this.state.plugins
      });
      this.view.updateState(this.state);

      if (emitUpdate) {
        this.emitUpdate();
      }
    }
  }, {
    key: "clearContent",
    value: function clearContent() {
      var emitUpdate = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : false;
      this.setContent(this.options.emptyDocument, emitUpdate);
    }
  }, {
    key: "setActiveNodesAndMarks",
    value: function setActiveNodesAndMarks() {
      var _this4 = this;

      this.activeMarks = Object.entries(this.schema.marks).reduce(function (marks, _ref2) {
        var _ref3 = _slicedToArray(_ref2, 2),
            name = _ref3[0],
            mark = _ref3[1];

        return _objectSpread({}, marks, _defineProperty({}, name, function () {
          var attrs = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
          return markIsActive(_this4.state, mark, attrs);
        }));
      }, {});
      this.activeMarkAttrs = Object.entries(this.schema.marks).reduce(function (marks, _ref4) {
        var _ref5 = _slicedToArray(_ref4, 2),
            name = _ref5[0],
            mark = _ref5[1];

        return _objectSpread({}, marks, _defineProperty({}, name, getMarkAttrs(_this4.state, mark)));
      }, {});
      this.activeNodes = Object.entries(this.schema.nodes).reduce(function (nodes, _ref6) {
        var _ref7 = _slicedToArray(_ref6, 2),
            name = _ref7[0],
            node = _ref7[1];

        return _objectSpread({}, nodes, _defineProperty({}, name, function () {
          var attrs = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
          return nodeIsActive(_this4.state, node, attrs);
        }));
      }, {});
    }
  }, {
    key: "getMarkAttrs",
    value: function getMarkAttrs() {
      var type = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
      return this.activeMarkAttrs[type];
    }
  }, {
    key: "registerPlugin",
    value: function registerPlugin() {
      var plugin = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;

      if (!plugin) {
        return;
      }

      this.state = this.state.reconfigure({
        plugins: this.state.plugins.concat([plugin])
      });
      this.view.updateState(this.state);
    }
  }, {
    key: "destroy",
    value: function destroy() {
      if (!this.view) {
        return;
      }

      this.view.destroy();
    }
  }, {
    key: "builtInExtensions",
    get: function get() {
      if (!this.options.useBuiltInExtensions) {
        return [];
      }

      return [new Doc(), new Text(), new Paragraph()];
    }
  }, {
    key: "isActive",
    get: function get() {
      return Object.entries(_objectSpread({}, this.activeMarks, this.activeNodes)).reduce(function (types, _ref8) {
        var _ref9 = _slicedToArray(_ref8, 2),
            name = _ref9[0],
            value = _ref9[1];

        return _objectSpread({}, types, _defineProperty({}, name, function () {
          var attrs = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
          return value(attrs);
        }));
      }, {});
    }
  }]);

  return Editor;
}();

var EditorContent = {
  props: {
    editor: {
      default: null,
      type: Object
    }
  },
  watch: {
    editor: {
      immediate: true,
      handler: function handler(editor) {
        var _this = this;

        if (editor && editor.element) {
          this.$nextTick(function () {
            _this.$el.appendChild(editor.element.firstChild);

            editor.setParentComponent(_this);
          });
        }
      }
    }
  },
  render: function render(createElement) {
    return createElement('div');
  }
};

var EditorMenuBar = {
  props: {
    editor: {
      default: null,
      type: Object
    }
  },
  render: function render() {
    if (!this.editor) {
      return null;
    }

    return this.$scopedSlots.default({
      focused: this.editor.view.focused,
      focus: this.editor.focus,
      commands: this.editor.commands,
      isActive: this.editor.isActive,
      getMarkAttrs: this.editor.getMarkAttrs.bind(this.editor)
    });
  }
};

var Menu =
/*#__PURE__*/
function () {
  function Menu(_ref) {
    var options = _ref.options,
        editorView = _ref.editorView;

    _classCallCheck(this, Menu);

    this.options = _objectSpread({}, {
      element: null,
      onUpdate: function onUpdate() {
        return false;
      }
    }, options);
    this.editorView = editorView;
    this.isActive = false;
    this.left = 0;
    this.bottom = 0;
    this.editorView.dom.addEventListener('blur', this.hide.bind(this));
  }

  _createClass(Menu, [{
    key: "update",
    value: function update(view, lastState) {
      var state = view.state; // Don't do anything if the document/selection didn't change

      if (lastState && lastState.doc.eq(state.doc) && lastState.selection.eq(state.selection)) {
        return;
      } // Hide the tooltip if the selection is empty


      if (state.selection.empty) {
        this.hide();
        return;
      } // Otherwise, reposition it and update its content


      var _state$selection = state.selection,
          from = _state$selection.from,
          to = _state$selection.to; // These are in screen coordinates

      var start = view.coordsAtPos(from);
      var end = view.coordsAtPos(to); // The box in which the tooltip is positioned, to use as base

      var box = this.options.element.offsetParent.getBoundingClientRect(); // Find a center-ish x position from the selection endpoints (when
      // crossing lines, end may be more to the left)

      var left = Math.max((start.left + end.left) / 2, start.left + 3);
      this.isActive = true;
      this.left = parseInt(left - box.left, 10);
      this.bottom = parseInt(box.bottom - start.top, 10);
      this.sendUpdate();
    }
  }, {
    key: "sendUpdate",
    value: function sendUpdate() {
      this.options.onUpdate({
        isActive: this.isActive,
        left: this.left,
        bottom: this.bottom
      });
    }
  }, {
    key: "hide",
    value: function hide(event) {
      if (event && event.relatedTarget) {
        return;
      }

      this.isActive = false;
      this.sendUpdate();
    }
  }, {
    key: "destroy",
    value: function destroy() {
      this.editorView.dom.removeEventListener('blur', this.hide);
    }
  }]);

  return Menu;
}();

function MenuBubble (options) {
  return new Plugin({
    view: function view(editorView) {
      return new Menu({
        editorView: editorView,
        options: options
      });
    }
  });
}

var EditorMenuBubble = {
  props: {
    editor: {
      default: null,
      type: Object
    }
  },
  data: function data() {
    return {
      menu: {
        isActive: false,
        left: 0,
        bottom: 0
      }
    };
  },
  watch: {
    editor: {
      immediate: true,
      handler: function handler(editor) {
        var _this = this;

        if (editor) {
          this.$nextTick(function () {
            editor.registerPlugin(MenuBubble({
              element: _this.$el,
              onUpdate: function onUpdate(menu) {
                // the second check ensures event is fired only once
                if (menu.isActive && _this.menu.isActive === false) {
                  _this.$emit('show', menu);
                } else if (!menu.isActive && _this.menu.isActive === true) {
                  _this.$emit('hide', menu);
                }

                _this.menu = menu;
              }
            }));
          });
        }
      }
    }
  },
  render: function render() {
    if (!this.editor) {
      return null;
    }

    return this.$scopedSlots.default({
      focused: this.editor.view.focused,
      focus: this.editor.focus,
      commands: this.editor.commands,
      isActive: this.editor.isActive,
      getMarkAttrs: this.editor.getMarkAttrs.bind(this.editor),
      menu: this.menu
    });
  }
};

var Menu$1 =
/*#__PURE__*/
function () {
  function Menu(_ref) {
    var options = _ref.options,
        editorView = _ref.editorView;

    _classCallCheck(this, Menu);

    this.options = _objectSpread({}, {
      element: null,
      onUpdate: function onUpdate() {
        return false;
      }
    }, options);
    this.editorView = editorView;
    this.isActive = false;
    this.top = 0;
    this.editorView.dom.addEventListener('blur', this.hide.bind(this));
  }

  _createClass(Menu, [{
    key: "update",
    value: function update(view, lastState) {
      var state = view.state; // Don't do anything if the document/selection didn't change

      if (lastState && lastState.doc.eq(state.doc) && lastState.selection.eq(state.selection)) {
        return;
      }

      if (!state.selection.empty) {
        this.hide();
        return;
      }

      var currentDom = view.domAtPos(state.selection.$anchor.pos);
      var isActive = currentDom.node.innerHTML === '<br>' && currentDom.node.tagName === 'P' && currentDom.node.parentNode === view.dom;

      if (!isActive) {
        this.hide();
        return;
      }

      var editorBoundings = this.options.element.offsetParent.getBoundingClientRect();
      var cursorBoundings = view.coordsAtPos(state.selection.$anchor.pos);
      var top = cursorBoundings.top - editorBoundings.top;
      this.isActive = true;
      this.top = top;
      this.sendUpdate();
    }
  }, {
    key: "sendUpdate",
    value: function sendUpdate() {
      this.options.onUpdate({
        isActive: this.isActive,
        top: this.top
      });
    }
  }, {
    key: "hide",
    value: function hide(event) {
      if (event && event.relatedTarget) {
        return;
      }

      this.isActive = false;
      this.sendUpdate();
    }
  }, {
    key: "destroy",
    value: function destroy() {
      this.editorView.dom.removeEventListener('blur', this.hide);
    }
  }]);

  return Menu;
}();

function FloatingMenu (options) {
  return new Plugin({
    view: function view(editorView) {
      return new Menu$1({
        editorView: editorView,
        options: options
      });
    }
  });
}

var EditorFloatingMenu = {
  props: {
    editor: {
      default: null,
      type: Object
    }
  },
  data: function data() {
    return {
      menu: {
        isActive: false,
        left: 0,
        bottom: 0
      }
    };
  },
  watch: {
    editor: {
      immediate: true,
      handler: function handler(editor) {
        var _this = this;

        if (editor) {
          this.$nextTick(function () {
            editor.registerPlugin(FloatingMenu({
              element: _this.$el,
              onUpdate: function onUpdate(menu) {
                _this.menu = menu;
              }
            }));
          });
        }
      }
    }
  },
  render: function render() {
    if (!this.editor) {
      return null;
    }

    return this.$scopedSlots.default({
      focused: this.editor.view.focused,
      focus: this.editor.focus,
      commands: this.editor.commands,
      isActive: this.editor.isActive,
      getMarkAttrs: this.editor.getMarkAttrs.bind(this.editor),
      menu: this.menu
    });
  }
};

export { Editor, Extension, Node, Mark, Doc, Paragraph, Text, EditorContent, EditorMenuBar, EditorMenuBubble, EditorFloatingMenu };
