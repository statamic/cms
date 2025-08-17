// dist/types/index.js
// This file exists only to satisfy module resolution
// The actual implementation is provided by window.Statamic at runtime
module.exports = typeof window !== 'undefined' && window.Statamic ? window.Statamic : {};
