define([
  './constants/inline-element-names',
  './constants/block-element-names',
  'immutable'
], function (inlineElementNames, blockElementNames, Immutable) {

  'use strict';

  function isBlockElement(node) {
    return blockElementNames.includes(node.nodeName);
  }

  function isInlineElement(node) {
    return inlineElementNames.includes(node.nodeName);
  }

  function hasContent(node) {

    if(node && node.children && node.children.length > 0) {
      return true;
    }

    if(node && node.nodeName === 'BR') {
      return true;
    }
    return false;
  }

  // return true if nested inline tags ultimately just contain <br> or ""
  function isEmptyInlineElement(node) {
    if( node.children.length > 1 ) return false;
    if( node.children.length === 1 && node.textContent.trim() !== '' ) return false;
    if( node.children.length === 0 ) return node.textContent.trim() === '';
    return isEmptyInlineElement(node.children[0]);
  }

  function isText(node) {
    return node.nodeType === Node.TEXT_NODE;
  }

  function isEmptyTextNode(node) {
    return isText(node) && node.data === '';
  }

  function isFragment(node) {
    return node.nodeType === Node.DOCUMENT_FRAGMENT_NODE;
  }

  function isBefore(node1, node2) {
    return node1.compareDocumentPosition(node2) & Node.DOCUMENT_POSITION_FOLLOWING;
  }

  function elementHasClass(Node, className) {
    return function(node) {
      return (node.nodeType === Node.ELEMENT_NODE && node.className === className)
    }
  }

  function isSelectionMarkerNode(node) {
    return elementHasClass(Node, 'scribe-marker')(node);
  }

  function isCaretPositionNode(node) {
    return elementHasClass(Node, 'caret-position')(node);
  }

  function isWhitespaceOnlyTextNode(Node, node) {
    if(node.nodeType === Node.TEXT_NODE
      && /^\s*$/.test(node.nodeValue)) {
      return true;
    }

    return false;

  }

  function isTextNodeWithContent(Node, node) {
    return node.nodeType === Node.TEXT_NODE && !isWhitespaceOnlyTextNode(Node, node);
  }

  function firstDeepestChild(node) {
    var fs = node.firstChild;
    return !fs || fs.nodeName === 'BR' ?
      node :
      firstDeepestChild(fs);
  }

  function insertAfter(newNode, referenceNode) {
    return referenceNode.parentNode.insertBefore(newNode, referenceNode.nextSibling);
  }

  function removeNode(node) {
    return node.parentNode.removeChild(node);
  }

  function getAncestor(node, rootElement, nodeFilter) {
    function isTopContainerElement (element) {
      return rootElement === element;
    }
    // TODO: should this happen here?
    if (isTopContainerElement(node)) {
      return;
    }

    var currentNode = node.parentNode;

    // If it's a `contenteditable` then it's likely going to be the Scribe
    // instance, so stop traversing there.
    while (currentNode && ! isTopContainerElement(currentNode)) {
      if (nodeFilter(currentNode)) {
        return currentNode;
      }
      currentNode = currentNode.parentNode;
    }
  }

  function nextSiblings(node) {
    var all = Immutable.List();
    while (node = node.nextSibling) {
      all = all.push(node);
    }
    return all;
  }

  function wrap(nodes, parentNode) {
    nodes[0].parentNode.insertBefore(parentNode, nodes[0]);
    nodes.forEach(function (node) {
      parentNode.appendChild(node);
    });
    return parentNode;
  }

  function unwrap(node, childNode) {
    while (childNode.childNodes.length > 0) {
      node.insertBefore(childNode.childNodes[0], childNode);
    }
    node.removeChild(childNode);
  }

  /**
   * Chrome: If a parent node has a CSS `line-height` when we apply the
   * insertHTML command, Chrome appends a SPAN to plain content with
   * inline styling replicating that `line-height`, and adjusts the
   * `line-height` on inline elements.
   *
   * As per: http://jsbin.com/ilEmudi/4/edit?css,js,output
   * More from the web: http://stackoverflow.com/q/15015019/40352
   */
  function removeChromeArtifacts(parentElement) {
    function isInlineWithStyle(parentStyle, element) {
      return window.getComputedStyle(element).lineHeight === parentStyle.lineHeight;
    }

    var nodes = Immutable.List(parentElement.querySelectorAll(inlineElementNames
      .map(function(elName) { return elName + '[style*="line-height"]' })
      .join(',')
      ));
    nodes = nodes.filter(isInlineWithStyle.bind(null, window.getComputedStyle(parentElement)));

    var emptySpans = Immutable.List();

    nodes.forEach(function(node) {
      node.style.lineHeight = null;
      if (node.getAttribute('style') === '') {
        node.removeAttribute('style');
      }
      if (node.nodeName === 'SPAN' && node.attributes.length === 0) {
        emptySpans = emptySpans.push(node);
      }
    });

    emptySpans.forEach(function(node) {
      unwrap(node.parentNode, node);
    });
  }

  return {
    isInlineElement: isInlineElement,
    isBlockElement: isBlockElement,
    isEmptyInlineElement: isEmptyInlineElement,
    isText: isText,
    isEmptyTextNode: isEmptyTextNode,
    isWhitespaceOnlyTextNode: isWhitespaceOnlyTextNode,
    isTextNodeWithContent: isTextNodeWithContent,
    isFragment: isFragment,
    isBefore: isBefore,
    isSelectionMarkerNode: isSelectionMarkerNode,
    isCaretPositionNode: isCaretPositionNode,
    firstDeepestChild: firstDeepestChild,
    insertAfter: insertAfter,
    removeNode: removeNode,
    getAncestor: getAncestor,
    nextSiblings: nextSiblings,
    wrap: wrap,
    unwrap: unwrap,
    removeChromeArtifacts: removeChromeArtifacts,
    elementHasClass: elementHasClass,
    hasContent: hasContent
  };

});
