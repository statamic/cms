import template from './template.html'
import vueToast from '../toast'

import {isNumber} from '../utils.js'

const defaultOptions = {
  maxToasts: 6,
  position: 'left bottom'
}

export default {
  template: template,
  data() {
    return {
      toasts: [],
      options: defaultOptions
    }
  },
  computed: {
    classesOfPosition() {
      return this._updateClassesOfPosition(this.options.position)
    },
    directionOfJumping() {
      return this._updateDirectionOfJumping(this.options.position)
    }
  },
  methods: {
    // Public
    showToast(message, options) {
      this._addToast(message, options)
      this._moveToast()

      return this
    },
    setOptions(options) {
      this.options = Object.assign(this.options, options || {})

      return this
    },
    // Private
    _addToast(message, options = {}) {
      if (!message) {
        return
      }

      options.directionOfJumping = this.directionOfJumping

      this.toasts.unshift({
        message,
        options,
        isDestroyed: false
      })
    },
    _moveToast(toast) {
      const maxToasts = this.options.maxToasts > 0
        ? this.options.maxToasts
        : 9999

      // moving||removing old toasts
      this.toasts = this.toasts.reduceRight((prev, toast, i) => {
        if (toast.isDestroyed) {
          return prev
        }

        if (i + 1 >= maxToasts) {
          return prev
        }

        return [toast].concat(prev)
      }, [])
    },
    _updateClassesOfPosition(position) {
      return position.split(' ').reduce((prev, val) => {
        prev[`--${val.toLowerCase()}`] = true

        return prev
      }, {})
    },
    _updateDirectionOfJumping(position) {
      return position.match(/top/i) ? '+' : '-'
    }
  },
  components: {
    'vue-toast': vueToast
  }
}
