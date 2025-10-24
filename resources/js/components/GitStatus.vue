<template>
  <div class="rounded-lg overflow-hidden space-y-2">
    <div
      v-for="file in parsedFiles"
      :key="file.name"
      class="flex items-center justify-between py-2 px-3 rounded-lg border"
      :class="file.statusClass"
    >
      <div class="flex items-center space-x-3">
        <span class="font-mono text-sm">{{ file.name }}</span>
        <span class="text-xs opacity-75">{{ file.displayCode }}</span>
      </div>
      <span class="text-xs font-medium">{{ file.statusText }}</span>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    status: {
      type: String,
      required: true
    }
  },
  computed: {
    statusLines() {
      if (!this.status) return []
      return this.status.split('\n').filter(line => line.trim())
    },
    parsedFiles() {
      return this.statusLines
        .map(line => this.parseFileLine(line))
        .filter(Boolean)
    }
  },
  methods: {
    parseFileLine(line) {
      if (line.length < 3) return null

      const statusCode = line.substring(0, 2)
      const name = line.substring(3).trim()

      if (!name) return null

      return this.createFileObject(statusCode, name)
    },
    createFileObject(statusCode, name) {
      const statusInfo = this.getStatusInfo(statusCode)

      return {
        name,
        statusCode,
        statusClass: statusInfo.class,
        statusText: __(statusInfo.text),
        displayCode: statusInfo.displayCode,
      }
    },
    getStatusInfo(statusCode) {
      const normalizedCode = statusCode.trim()

      const statusMap = {
        'M': { class: 'text-yellow-700 bg-yellow-50 border-yellow-200', text: __('Modified'), displayCode: 'M' },
        'MM': { class: 'text-yellow-700 bg-yellow-50 border-yellow-200', text: __('Modified'), displayCode: 'M' },
        'A': { class: 'text-green-700 bg-green-50 border-green-200', text: __('Added'), displayCode: 'A' },
        '??': { class: 'text-green-700 bg-green-50 border-green-200', text: __('Added'), displayCode: 'A' },
        'D': { class: 'text-red-700 bg-red-50 border-red-200', text: __('Deleted'), displayCode: 'D' },
        'R': { class: 'text-blue-700 bg-blue-50 border-blue-200', text: __('Renamed'), displayCode: 'R' },
        'C': { class: 'text-purple-700 bg-purple-50 border-purple-200', text: __('Copied'), displayCode: 'C' }
      }

      return statusMap[normalizedCode] || {
        class: 'text-gray-700 bg-gray-50 border-gray-200',
        text: 'Unknown',
        displayCode: statusCode
      }
    }
  }
}
</script>
