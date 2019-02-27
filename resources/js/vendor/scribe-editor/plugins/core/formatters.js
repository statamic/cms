define([
  './formatters/html/replace-nbsp-chars',
  './formatters/plain-text/escape-html-characters'
], function (
  replaceNbspCharsFormatter,
  escapeHtmlCharactersFormatter
) {
  'use strict';

  return {
    replaceNbspCharsFormatter: replaceNbspCharsFormatter,
    escapeHtmlCharactersFormatter: escapeHtmlCharactersFormatter
  };
});
