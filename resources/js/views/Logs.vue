<template>
    <v-container fluid>
        <h1 class="mb-3 d-flex justify-space-between align-center">{{ $t('log_activity') }}
            <v-menu location="start">
                <template v-slot:activator="{ props }">
                    <v-btn variant="outlined" v-bind="props">
                        <v-icon>mdi-dots-vertical</v-icon> Menu
                    </v-btn>
                </template>

                <v-list dense density="compact">
                    <v-list-item @click="downloadExcel" class="cursor-pointer">
                        <template #prepend>
                            <v-icon color="success">mdi-file-excel</v-icon>
                        </template>
                        <v-list-item-title>Export Excel</v-list-item-title>
                    </v-list-item>
                    <v-list-item @click="downloadPdf" class="cursor-pointer">
                        <template #prepend>
                            <v-icon color="error">mdi-file</v-icon>
                        </template>
                        <v-list-item-title>Export PDF</v-list-item-title>
                    </v-list-item>
                    <v-list-item @click="printLogs" prepend-icon="mdi-printer" class="cursor-pointer">
                        <v-list-item-title>Print</v-list-item-title>
                    </v-list-item>
                </v-list>
            </v-menu>
        </h1>

        <v-card>
            <v-card-title>
                <v-row class="mb-4" dense>
                    <v-col cols="12" sm="2">
                        <v-text-field v-model="filters.start_date" label="Tanggal Mulai" type="date"
                            hide-details="auto" />
                    </v-col>
                    <v-col cols="12" sm="2">
                        <v-text-field v-model="filters.end_date" label="Tanggal Akhir" type="date"
                            hide-details="auto" />
                    </v-col>
                    <v-col cols="12" sm="7">
                        <v-text-field v-model="filters.search" :label="t('search_module_desc')"
                            append-inner-icon="mdi-magnify" hide-details="auto" />
                    </v-col>
                    <v-col cols="12" sm="1" class="d-flex align-end">
                        <v-btn size="large" @click="fetchData" color="primary" class="me-2">Filter</v-btn>
                    </v-col>
                </v-row>
            </v-card-title>
            <v-data-table-server :headers="headers" :items="logs" :loading="loading" :items-length="totalItems"
                v-model:options="options" @update:options="fetchData">
                <template #item.user="{ item }">
                    {{ item.user?.name ?? '—' }}
                </template>
                <template #item.created_at="{ item }">
                    {{ $helpers.formatDate(item.created_at) }} </template>
            </v-data-table-server>
        </v-card>
    </v-container>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import api from '@/axios'
import { useI18n } from 'vue-i18n'
import debounce from 'lodash.debounce'

const { t } = useI18n()

const logs = ref([])
const totalItems = ref(0)
const loading = ref(false)

// Inisialisasi options untuk v-data-table-server
const options = ref({
    page: 1,
    itemsPerPage: 10,
    sortBy: [{ key: 'created_at', order: 'desc' }], // Ini adalah format yang diharapkan oleh v-data-table-server
});

const headers = [
    { title: 'Waktu', key: 'created_at', sortable: true },
    { title: 'User', key: 'user.name', sortable: false }, // Menggunakan 'user.name' untuk akses properti nested
    { title: 'Aksi', key: 'action' },
    { title: 'Modul', key: 'module' },
    { title: 'Keterangan', key: 'description' },
    { title: 'IP', key: 'ip_address' },
]

// Gabungkan search + date filter jadi 1 objek
const filters = ref({
    search: '',
    start_date: '',
    end_date: ''
})

// Fetch data dari server
function fetchData() {
    loading.value = true
    // Pastikan sortBy diambil dari array, sesuai format v-model:options
    const sortBy = options.value.sortBy.length > 0 ? options.value.sortBy[0].key : 'created_at';
    const sortOrder = options.value.sortBy.length > 0 ? options.value.sortBy[0].order : 'desc';

    api.get('/logs', {
        params: {
            page: options.value.page,
            itemsPerPage: options.value.itemsPerPage,
            sortBy: sortBy, // Kirim key kolom
            sortDesc: sortOrder === 'desc' ? 'true' : 'false', // Kirim 'true' atau 'false'
            search: filters.value.search,
            start_date: filters.value.start_date,
            end_date: filters.value.end_date,
        },
    }).then((res) => {
        logs.value = res.data?.data || []
        totalItems.value = res.data?.total || 0
    }).finally(() => {
        loading.value = false
    })
}

// Tidak perlu fungsi updateOptions terpisah jika kita langsung memanggil fetchData
// pada @update:options dan watch
// const updateOptions = (newOptions) => {
//   // options.value sudah diperbarui otomatis oleh v-model:options
//   // fetchData() akan dipicu oleh watcher atau bisa langsung dipanggil di sini
// };

function getFilenameFromHeader(headers, fallback = 'download') {
    const disposition = headers['content-disposition'] || ''
    const filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/
    const matches = filenameRegex.exec(disposition)
    return matches != null ? matches[1].replace(/['"]/g, '') : fallback
}

// Export Excel
function downloadExcel() {
    api.get('/logs/export', {
        params: {
            search: filters.value.search,
            start_date: filters.value.start_date,
            end_date: filters.value.end_date
        },
        responseType: 'blob'
    }).then(res => {
        const filename = getFilenameFromHeader(res.headers, 'activity_logs.xlsx')
        const blob = new Blob([res.data], {
            type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        })
        const url = URL.createObjectURL(blob)
        const link = document.createElement('a')
        link.href = url
        link.setAttribute('download', filename)
        document.body.appendChild(link)
        link.click()
        document.body.removeChild(link)
        URL.revokeObjectURL(url)
    })
}

// Export PDF
function downloadPdf() {
    const token = localStorage.getItem('token')

    const params = new URLSearchParams({
        start_date: filters.value.start_date,
        end_date: filters.value.end_date,
        search: filters.value.search
    })

    api.get('/logs/export-pdf?' + params.toString(), {
        headers: {
            Authorization: `Bearer ${token}`
        },
        responseType: 'blob'
    }).then(res => {
        const blob = new Blob([res.data], { type: 'application/pdf' })
        const url = URL.createObjectURL(blob)
        window.open(url, '_blank')
    }).catch(() => {
        alert('Gagal memuat PDF')
    })
}

function printLogs() {
    const token = localStorage.getItem('token')
    const params = new URLSearchParams({
        search: filters.value.search,
        start_date: filters.value.start_date,
        end_date: filters.value.end_date
    }).toString()

    const printWindow = window.open('', '_blank')
    api.get('/logs/print?' + params, {
        headers: {
            Authorization: `Bearer ${token}`
        },
        responseType: 'text'
    }).then(res => {
        printWindow.document.write(res.data)
        printWindow.document.close()
    }).catch(() => {
        printWindow.close()
        alert('Gagal memuat halaman cetak')
    })
}

// Watcher untuk options (page, itemsPerPage, sortBy)
// Ini akan memicu fetchData setiap kali properti di `options` berubah
watch(options, () => {
    fetchData();
}, { deep: true });

// Debounce pencarian agar tidak terlalu sering hit API
//watch(() => filters.value.search, debounce(() => {
    //options.value.page = 1 // reset ke halaman 1 saat cari
    //fetchData()
//}, 400))

// Watcher untuk filter tanggal (start_date, end_date)
//watch([() => filters.value.start_date, () => filters.value.end_date], () => {
    //options.value.page = 1; // Reset ke halaman 1 saat filter tanggal berubah
    //fetchData();
//});

onMounted(fetchData)
</script>