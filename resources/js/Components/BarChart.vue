<template>
  <Bar
    id="my-chart-id"
    :options="chartOptions"
    :data="chartData"
  />
</template>

<script setup>
import { Bar } from 'vue-chartjs'
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js'
import { computed } from 'vue'

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale)

const props = defineProps({
  data: Object
})

const chartData = computed(() => ({
  labels: props.data?.labels ?? [],
  datasets: [{
    label: '売上',
    backgroundColor: '#f87979',
    data: props.data?.totals ?? []
  }]
}))

const chartOptions = {
  responsive: true
}
</script>
