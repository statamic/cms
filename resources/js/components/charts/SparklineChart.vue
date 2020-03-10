<template>
      <div ref="chart" class="ct-chart flex" />
</template>

<script>
import Chartist from 'chartist'
import 'chartist/dist/chartist.min.css'

export default {
  props: [],

  data: () => ({ chartist: null }),

  methods: {
    renderChart() {
      this.chartist.update(this.field.data)
    },
  },

  mounted() {
    this.chartist = new Chartist.Line(this.$refs.chart,
        { series: [[5, 9, 7, 8, 5, 12, 5, 4, 1, 4, 2, 4, 11, 4, 0, 0, 0, 0, 1, 2, 1, 0, 0, 21, 33, 18, 5, 3, 2, 18, 1]] },
        {
            height: 60,
            showPoint: true,
            fullWidth: true,
            showArea: true,
            lineSmooth: false,
            chartPadding: { top: 4, right: 0, bottom: 0, left: 0 },
            axisX: { showGrid: false, showLabel: false, offset: 0 },
            axisY: { showGrid: false, showLabel: false, offset: 0 },
        }
    )
  }

}
</script>
