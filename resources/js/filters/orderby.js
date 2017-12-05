module.exports = function (arr, sortKey, reverse) {
  if (!sortKey) {
    return arr
  }
  var order = (reverse && reverse < 0) ? -1 : 1
  // sort on a copy to avoid mutating original array
  return arr.slice().sort(function (a, b) {
    if (sortKey !== '$key') {
      if (Vue.util.isObject(a) && '$value' in a) a = a.$value
      if (Vue.util.isObject(b) && '$value' in b) b = b.$value
    }
    a = Vue.util.isObject(a) ? Vue.parsers.path.getPath(a, sortKey) : a
    b = Vue.util.isObject(b) ? Vue.parsers.path.getPath(b, sortKey) : b

    a = (typeof a === 'string') ? a.toLowerCase() : a
    b = (typeof b === 'string') ? b.toLowerCase() : b

    return a === b ? 0 : a > b ? order : -order
  })
};
