<template>
  <v-container fluid>
    <h1 class="mb-3">Permissions</h1>
    <v-row>
      <v-col cols="12" sm="6">
        <v-text-field v-model="newPermission" label="Permission baru" />
      </v-col>
      <v-col cols="12" sm="3">
        <v-btn @click="createPermission" color="primary" :loading="loading1">Tambah</v-btn>
      </v-col>
    </v-row>

    <v-data-table :headers="headers" :items="permissions" class="elevation-1" :loading="loading">
      <template #item.updated_at="{ item }">
        {{ $t('created') }}: {{ $helpers.formatDate(item.created_at) }}<br />
        {{ $t('updated') }}: {{ $helpers.formatDate(item.updated_at) }}
      </template>
      <template #item.actions="{ item }">
        <v-btn icon variant="text" @click="confirmDelete(item)">
          <v-icon color="red">mdi-delete</v-icon>
        </v-btn>
      </template>
    </v-data-table>
  </v-container>

  <v-dialog v-model="dialogDelete" max-width="400">
    <v-card>
      <v-card-title class="text-h5">{{ $t('confirmation') }} {{ $t('delete') }}</v-card-title>
      <v-card-text>
        {{ $t('confirm_delete') }} <strong>{{ selectedPermission?.name }}</strong>?
      </v-card-text>
      <v-card-actions>
        <v-spacer />
        <v-btn variant="text" @click="dialogDelete = false">{{ $t('cancel') }}</v-btn>
        <v-btn color="red" variant="flat" :loading="loading2" @click="deletePermission">
          <v-icon>mdi-delete</v-icon>
          {{ $t('delete') }}
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import api from '@/axios'
import { useSnackbar } from '@/stores/snackbar'
import { useI18n } from 'vue-i18n'

const loading = ref(false)
const loading1 = ref(false)
const loading2 = ref(false)
const snackbar = useSnackbar()
const { t } = useI18n();
const selectedPermission = ref(null)
const dialogDelete = ref(false)

const permissions = ref([])
const newPermission = ref('')

const headers = computed(() => [
  { title: 'Permission', key: 'name' },
  { title: t('date'), key: 'updated_at' },
  { title: 'Aksi', key: 'actions', sortable: false }
])

const fetchPermissions = async () => {
  loading.value = true
  const res = await api.get('/permissions')
  permissions.value = res.data
  loading.value = false
}

const createPermission = async () => {
  if (!newPermission.value) return
  loading1.value = true
  const res = await api.post('/permissions', { name: newPermission.value })
  newPermission.value = ''
  loading1.value = false
  snackbar.showSnackbar(res.data.message)
  fetchPermissions()
}

function confirmDelete(item) {
  selectedPermission.value = item
  dialogDelete.value = true
}

const deletePermission = async () => {
  if (!selectedPermission.value) return

  loading2.value = true
  try {
    const res = await api.delete(`/permissions/${selectedPermission.value.id}`);
    snackbar.showSnackbar(res.data.message)
    fetchPermissions(); // Refresh data setelah penghapusan
  } catch (error) {
    console.error("Error deleting permission: ", error);
    snackbar.showSnackbar(error.response.data.message)
  } finally {
    loading2.value = false
    dialogDelete.value = false
  }
}

onMounted(fetchPermissions)
</script>
