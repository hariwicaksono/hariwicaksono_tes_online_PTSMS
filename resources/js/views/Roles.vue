<template>
    <v-container fluid>
        <h1 class="mb-3">Roles</h1>
        <v-row>
            <v-col cols="12" sm="6">
                <v-text-field v-model="newRole" label="Role baru" />
            </v-col>
            <v-col cols="12" sm="3">
                <v-btn @click="createRole" color="primary" :loading="loading1">Tambah Role</v-btn>
            </v-col>
        </v-row>

        <v-data-table :headers="headers" :items="roles" item-value="id" class="elevation-1" :loading="loading">
            <template #item.updated_at="{ item }">
                {{ $t('created') }}: {{ $helpers.formatDate(item.created_at) }}<br />
                {{ $t('updated') }}: {{ $helpers.formatDate(item.updated_at) }}
            </template>
            <template #item.actions="{ item }">
                <v-btn icon variant="text" @click="editRole(item)" class="mr-2">
                    <v-icon color="primary">mdi-pencil</v-icon>
                </v-btn>
                <v-btn icon variant="text" @click="confirmDelete(item)"
                    :disabled="item.permissions != null && item.permissions.length > 0">
                    <v-icon color="red">mdi-delete</v-icon>
                </v-btn>
            </template>

            <template #item.permissions="{ item }">
                <v-chip v-for="(perm, index) in item.permissions.slice(0, 6)" :key="perm.id" class="ma-1" size="small">
                    {{ perm.name }}
                </v-chip>
                <!-- Tampilkan jika ada lebih dari 6 -->
                <span v-if="item.permissions.length > 6" class="text-caption grey--text">
                    +{{ item.permissions.length - 6 }} more
                </span>
            </template>
        </v-data-table>
    </v-container>

    <v-dialog v-model="editDialog" max-width="500" scrollable>
        <v-card>
            <v-card-title class="d-flex justify-space-between align-center">
                Edit Role
                <v-btn variant="text" icon="mdi-close" @click="editDialog = false"></v-btn>
            </v-card-title>
            <v-divider />
            <v-card-text>
                <v-form ref="editFormRef" v-model="formValid" lazy-validation>
                    <v-text-field v-model="editForm.name" label="Nama Role"
                        :rules="[v => !!v || 'Nama tidak boleh kosong']" required />

                    <v-divider class="my-3" />
                    <div class="mb-2">Permissions:</div>
                    <span v-if="loading2" class="ml-2"><v-progress-circular indeterminate
                            size="20"></v-progress-circular></span>
                    <v-checkbox v-for="perm in allPermissions" :key="perm.id" :label="perm.name" :value="perm.name"
                        v-model="editForm.permissions" density="compact" hide-details class="ms-2" />
                </v-form>
            </v-card-text>

            <v-card-actions>
                <v-spacer />
                <v-btn @click="editDialog = false">{{ $t('cancel') }}</v-btn>
                <v-btn color="primary" variant="flat" :disabled="!formValid" @click="submitEdit"
                    :loading="loading1"><v-icon>mdi-content-save</v-icon> {{ $t('save')
                    }}</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>

    <v-dialog v-model="dialogDelete" max-width="400">
        <v-card>
            <v-card-title class="text-h5">{{ $t('confirmation') }} {{ $t('delete') }}</v-card-title>
            <v-card-text>
                {{ $t('confirm_delete') }} <strong>{{ selectedRole?.name }}</strong>?
            </v-card-text>
            <v-card-actions>
                <v-spacer />
                <v-btn variant="text" @click="dialogDelete = false">{{ $t('cancel') }}</v-btn>
                <v-btn color="red" variant="flat" :loading="loading3" @click="deleteRole">
                    <v-icon>mdi-delete</v-icon>
                    {{ $t('delete') }}
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script setup>
import { ref, onMounted, reactive, computed } from 'vue'
import api from '@/axios'
import router from '@/router'
import { useSnackbar } from '@/stores/snackbar'
import { useI18n } from 'vue-i18n'
import { initLogout } from '../utils/auth'

const loading = ref(false)
const loading1 = ref(false)
const loading2 = ref(false)
const loading3 = ref(false)
const snackbar = useSnackbar()
const roles = ref([])
const newRole = ref('')
const { t } = useI18n();
const selectedRole = ref(null)
const dialogDelete = ref(false)

const headers = computed(() => [
    { title: 'Role', key: 'name' },
    { title: 'Permissions', key: 'permissions', sortable: false },
    { title: t('date'), key: 'updated_at' },
    { title: 'Aksi', key: 'actions', sortable: false }
])

const fetchRoles = async () => {
    loading.value = true
    const res = await api.get('/roles')
    roles.value = res.data
    loading.value = false
}

const createRole = async () => {
    if (!newRole.value) return
    loading1.value = true
    const res = await api.post('/roles', { name: newRole.value })
    newRole.value = ''
    loading1.value = false
    snackbar.showSnackbar(res.data.message)
    fetchRoles()
}

const editDialog = ref(false)
const formValid = ref(true)
const editFormRef = ref(null)
const editForm = reactive({
    id: null,
    name: '',
    permissions: []
})

const allPermissions = ref([])

const fetchPermissions = async () => {
    loading2.value = true
    const res = await api.get('/permissions')
    allPermissions.value = res.data
    loading2.value = false
}

const editRole = (role) => {
    editForm.id = role.id
    editForm.name = role.name
    editForm.permissions = role.permissions.map(p => p.name)
    editDialog.value = true
}

const submitEdit = async () => {
    loading1.value = true
    const valid = await editFormRef.value.validate()
    if (!valid) return

    await api.put(`/roles/${editForm.id}`, {
        name: editForm.name
    })

    const res = await api.put(`/roles/${editForm.id}/permissions`, {
        permissions: editForm.permissions
    })

    loading1.value = false
    editDialog.value = false
    snackbar.showSnackbar(res.data.message)

    if (res) {
        snackbar.showSnackbar(t('relogin_required'))
        setTimeout(() => {
            initLogout()
            setTimeout(() => {
                router.replace('/login')
            }, 100)
        }, 1500)
    }

    fetchRoles()
}

function confirmDelete(item) {
    selectedRole.value = item
    dialogDelete.value = true
}

const deleteRole = async (id) => {
    if (!selectedRole.value) return

    loading3.value = true
    try {
        const res = await api.delete(`/roles/${selectedRole.value.id}`);
        snackbar.showSnackbar(res.data.message)
        fetchRoles(); // Refresh data setelah penghapusan
    } catch (error) {
        console.error("Error deleting role: ", error);
        snackbar.showSnackbar(error.response.data.message)
    } finally {
        loading3.value = false
        dialogDelete.value = false
    }
}

onMounted(() => {
    fetchRoles()
    fetchPermissions()
})
</script>
