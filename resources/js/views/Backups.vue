<template>
    <v-container fluid>
        <h1 class="mb-3">Backup Database</h1>
        <v-card>
            <v-card-title>
                <v-btn color="primary" @click="runBackup" class="mb-3" :loading="loading1">
                    <v-icon>mdi-plus</v-icon> {{ $t('add') }}
                </v-btn>
            </v-card-title>
            <v-data-table-server :headers="headers" :items="backups" :loading="loading" :items-length="totalItems"
                v-model:options="options" @update:options="fetchData">
                <template #item.size="{ item }">
                    {{ formatSize(item.size) }}
                </template>
                <template #item.created_at="{ item }">
                    {{ $helpers.formatDate(item.created_at) }}
                </template>
                <template #item.actions="{ item }">
                    <v-btn icon variant="text" @click="download(item.filename)" class="mr-2">
                        <v-icon color="primary">mdi-download</v-icon>
                    </v-btn>
                    <v-btn icon variant="text" @click="confirmDelete(item)">
                        <v-icon color="red">mdi-delete</v-icon>
                    </v-btn>
                </template>
            </v-data-table-server>
        </v-card>
    </v-container>

    <v-dialog v-model="dialogDelete" max-width="400">
        <v-card>
            <v-card-title class="text-h5">{{ $t('confirmation') }} {{ $t('delete') }}</v-card-title>
            <v-card-text>
                {{ $t('confirm_delete') }} <strong>{{ selectedBackup?.filename }}</strong>?
            </v-card-text>
            <v-card-actions>
                <v-spacer />
                <v-btn variant="text" @click="dialogDelete = false">{{ $t('cancel') }}</v-btn>
                <v-btn color="red" variant="flat" :loading="loading2" @click="deleteBackup">
                    <v-icon>mdi-delete</v-icon>
                    {{ $t('delete') }}
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import api from '@/axios'
import { useSnackbar } from '@/stores/snackbar'

const snackbar = useSnackbar()
const backups = ref([])
const loading = ref(false)
const loading1 = ref(false)
const loading2 = ref(false)
const totalItems = ref(0)
const selectedBackup = ref(null)
const dialogDelete = ref(false)

const options = ref({
    page: 1,
    itemsPerPage: 10,
    sortBy: [{ key: 'created_at', order: 'desc' }],
});

const headers = [
    { title: 'Nama File', key: 'filename' },
    { title: 'Ukuran', key: 'size' },
    { title: 'Tanggal', key: 'created_at' },
    { title: 'Aksi', key: 'actions', sortable: false }
];

// Fungsi utama untuk mengambil data dari backend
function fetchData() {
    loading.value = true;
    const sortBy = options.value.sortBy.length > 0 ? options.value.sortBy[0].key : 'created_at';
    const sortOrder = options.value.sortBy.length > 0 ? options.value.sortBy[0].order : 'desc';

    api.get('/backups', {
        params: {
            page: options.value.page,
            itemsPerPage: options.value.itemsPerPage,
            sortBy: sortBy,
            sortDesc: sortOrder === 'desc' ? 'true' : 'false', // Kirim sebagai string 'true'/'false'
        },
    }).then((res) => {
        // Pastikan struktur respons sesuai dengan yang diharapkan
        // data.data berisi array item
        // data.total berisi total keseluruhan item
        backups.value = res.data?.data || [];
        totalItems.value = res.data?.total || 0;
    }).catch(error => {
        console.error("Error fetching data: ", error);
        // Tambahkan penanganan error yang lebih baik di sini, misalnya menampilkan pesan ke pengguna
    }).finally(() => {
        loading.value = false;
    });
}

// Tidak perlu fungsi updateOptions terpisah jika kita langsung memanggil fetchData
// pada @update:options dan watch
// const updateOptions = (newOptions) => {
//   // options.value sudah diperbarui otomatis oleh v-model:options
//   // fetchData() akan dipicu oleh watcher atau bisa langsung dipanggil di sini
// };

const runBackup = async () => {
    loading1.value = true;
    try {
        const res = await api.post('/backups');
        snackbar.showSnackbar(res.data.message)
        fetchData(); // Refresh data setelah backup
    } catch (error) {
        console.error("Error running backup: ", error);
    } finally {
        loading1.value = false;
    }
};

const download = async (filename) => {
    try {
        const response = await api.get(`/backups/download/${filename}`, {
            responseType: 'blob'
        });

        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', filename);
        document.body.appendChild(link);
        link.click();
        link.remove();
        snackbar.showSnackbar('Download successful')
    } catch (error) {
        console.error("Error downloading file: ", error);
    }
};

function confirmDelete(item) {
    selectedBackup.value = item
    dialogDelete.value = true
}

const deleteBackup = async () => {
    if (!selectedBackup.value) return

    loading2.value = true
    try {
        const res = await api.delete(`/backups/${selectedBackup.value.id}`);
        snackbar.showSnackbar(res.data.message)
        fetchData(); // Refresh data setelah penghapusan
    } catch (error) {
        console.error("Error deleting backup: ", error);
    } finally {
        loading2.value = false
        dialogDelete.value = false
    }
};

const formatSize = (bytes) => {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

// Pemicu fetchData saat options berubah
// Watcher ini akan berjalan setiap kali `page`, `itemsPerPage`, atau `sortBy` dalam `options` berubah.
// Ini adalah cara yang kuat untuk memicu pengambilan data saat opsi tabel berubah.
watch(options, fetchData, { deep: true });

// Panggil fetchData saat komponen pertama kali dimuat
onMounted(fetchData);
</script>