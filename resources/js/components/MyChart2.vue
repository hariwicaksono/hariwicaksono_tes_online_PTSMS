<template>
  <div>
    <canvas ref="chartCanvas"></canvas>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import {
  Chart,
  BarElement,
  BarController,
  CategoryScale,
  LinearScale,
  Title,
  Tooltip,
  Legend
} from 'chart.js'

// Register komponen Chart.js yang dibutuhkan
Chart.register(
  BarElement,
  BarController,
  CategoryScale,
  LinearScale,
  Title,
  Tooltip,
  Legend
)

const chartCanvas = ref(null)
let chartInstance = null

onMounted(() => {
  chartInstance = new Chart(chartCanvas.value, {
    type: 'bar',
    data: {
      labels: ['Januari', 'Februari', 'Maret', 'April', 'Mei'],
      datasets: [
        {
          label: 'Jumlah Penjualan',
          data: [120, 200, 150, 80, 250],
          backgroundColor: [
            'rgba(75, 192, 192, 0.6)',
            'rgba(255, 99, 132, 0.6)',
            'rgba(255, 206, 86, 0.6)',
            'rgba(54, 162, 235, 0.6)',
            'rgba(153, 102, 255, 0.6)'
          ],
          borderColor: 'rgba(0, 0, 0, 0.1)',
          borderWidth: 1
        }
      ]
    },
    options: {
      responsive: true,
      plugins: {
        legend: {
          position: 'top'
        },
        title: {
          display: true,
          text: 'Bar Chart Penjualan Bulanan'
        }
      },
      scales: {
        y: {
          beginAtZero: true
        }
      }
    }
  })
})

onBeforeUnmount(() => {
  if (chartInstance) chartInstance.destroy()
})
</script>

<style scoped>
canvas {
  max-width: 100%;
  height: 400px;
}
</style>
