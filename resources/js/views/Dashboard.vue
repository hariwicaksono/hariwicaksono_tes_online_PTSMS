<template>
  <v-container fluid>

  </v-container>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/axios'
import { can } from '@/utils/auth'
import MyChart from '@/components/MyChart.vue'
import MyChart2 from '@/components/MyChart2.vue'

const stats = ref({})
const loading = ref(false)
const loading1 = ref(false)
const version = ref('')
const expanded = ref(false) // default tertutup, bisa juga true jika mau expanded awalnya
const backupToday = ref(false)
const doingBackup = ref(false)

const fetchDashboard = async () => {
  loading.value = true
  try {
    const res = await api.get('/dashboard')
    stats.value = res.data
  } catch (err) {
    console.error('Failed to load data dashboard: ', err)
  } finally {
    loading.value = false
  }
}

const fetchVersion = async () => {
  try {
    const res = await api.get('/laravel-version')
    version.value = res.data
  } catch (err) {
    console.error('Failed to load version: ', err)
  }
}

const checkBackup = async () => {
  loading1.value = true
  try {
    const res = await api.get('/backups/check-today')
    backupToday.value = res.data.status === true
  } catch (error) {
    console.error('Failed to check backup: ', error)
  } finally {
    loading1.value = false
  }
}

const doBackup = async () => {
  doingBackup.value = true
  try {
    const res = await api.post('/backups')
    if (res.data.success) {
      backupToday.value = true
    }
    checkBackup()
  } catch (error) {
    console.error('Failed to Backup: ', error)
  } finally {
    doingBackup.value = false
  }
}

onMounted(async () => {
  fetchDashboard()
  fetchVersion()
  await checkBackup()
})
</script>
