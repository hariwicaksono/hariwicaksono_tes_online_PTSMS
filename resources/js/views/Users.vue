<template>
    <v-container fluid>
        <h1 class="mb-3">{{ $t('users') }}</h1>
        <v-card>
            <v-card-title>
                <v-btn color="primary" @click="openForm()" class="mb-3"
                    v-if="can('user.create')"><v-icon>mdi-plus</v-icon> {{ $t('add') }}</v-btn>
            </v-card-title>

            <v-card-subtitle>
                <v-row>
                    <v-col cols="12" md="3">
                        <v-select v-model="filters.role" label="Filter by Role" :items="allRoles" clearable
                            @update:modelValue="fetchUsers" />
                    </v-col>
                    <v-col cols="12" md="3">
                        <v-select v-model="filters.status" label="Filter by Status" :items="[
                            { title: 'Aktif', value: 1 },
                            { title: 'Nonaktif', value: 0 },
                        ]" clearable @update:modelValue="fetchUsers" />
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field v-model="filters.search" label="Search" @keyup.enter="fetchUsers" />
                    </v-col>
                </v-row>
            </v-card-subtitle>
            <v-data-table-server :headers="headers" :items="users" :items-length="totalItems" :loading="loading"
                v-model:options="options" @update:options="fetchUsers">
                <template #item.roles="{ item }">
                    <!-- <v-chip v-for="role in item.roles" :key="role" color="primary" size="small" class="me-1">
                        {{ role }}
                    </v-chip> -->
                    <v-select v-model="userRoleSelection[item.id]" :items="allRoles" label="Role" multiple
                        density="compact" hide-details chips :loading="roleLoading[item.id] === true"
                        :disabled="roleLoading[item.id] === true" @update:modelValue="(val) => updateRoles(item, val)"
                        class="w-100" />
                </template>
                <template #item.status="{ item }">
                    <v-switch :model-value="!!item.status" @update:modelValue="val => toggleStatus(item, val)"
                        hide-details color="green" :disabled="item.is_superadmin == 1" v-if="can('user.update')" />
                </template>
                <template #item.updated_at="{ item }">
                    {{ $t('created') }}: {{ $helpers.formatDate(item.updated_at) }}<br />
                    {{ $t('updated') }}: {{ $helpers.formatDate(item.updated_at) }}
                </template>
                <template #item.actions="{ item }">
                    <v-btn icon variant="text" @click="openForm(item)" class="mr-2" v-if="can('user.update')">
                        <v-icon color="primary">mdi-pencil</v-icon>
                    </v-btn>
                    <v-btn icon variant="text" @click="confirmDelete(item)" :disabled="item.is_superadmin == 1"
                        v-if="can('user.delete')">
                        <v-icon color="red">mdi-delete</v-icon>
                    </v-btn>
                </template>
            </v-data-table-server>
        </v-card>
    </v-container>

    <!-- Dialog -->
    <v-dialog v-model="dialog" width="500" scrollable>
        <v-card>
            <v-card-title class="d-flex justify-space-between align-center">
                {{ form.id ? $t('edit') : $t('add') }} User
                <v-btn variant="text" icon="mdi-close" @click="dialog = false"></v-btn>
            </v-card-title>
            <v-divider />
            <v-card-text>
                <v-form @submit.prevent="saveUser">
                    <v-text-field v-model="form.name" label="Name" :error-messages="errors.name" class="mb-2"
                        required />
                    <v-text-field v-model="form.email" label="Email" :error-messages="errors.email" class="mb-2"
                        required />
                    <v-text-field v-model="form.password" label="Password" :type="'password'"
                        :error-messages="errors.password" class="mb-2" :required="!form.id" />
                    <v-select v-model="form.status" label="Status" :items="[
                        { title: 'Active', value: 1 },
                        { title: 'Inactive', value: 0 }
                    ]" :error-messages="errors.status" class="mb-2" required />

                    <v-btn type="submit" color="primary" class="mr-2"
                        :loading="saving"><v-icon>mdi-content-save</v-icon>
                        {{ $t('save') }}</v-btn>
                    <v-btn text @click="dialog = false">{{ $t('cancel') }}</v-btn>
                </v-form>
            </v-card-text>
        </v-card>
    </v-dialog>

    <v-dialog v-model="dialogDelete" max-width="400">
        <v-card>
            <v-card-title class="text-h5">{{ $t('confirmation') }} {{ $t('delete') }}</v-card-title>
            <v-card-text>
                {{ $t('confirm_delete') }} <strong>{{ selectedUser?.email }}</strong>?
            </v-card-text>
            <v-card-actions>
                <v-spacer />
                <v-btn variant="text" @click="dialogDelete = false">{{ $t('cancel') }}</v-btn>
                <v-btn color="red" variant="flat" :loading="loading2" @click="deleteUser">
                    <v-icon>mdi-delete</v-icon>
                    {{ $t('delete') }}
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script setup>
import { ref, onMounted, reactive } from 'vue'
import api from '@/axios'
import { useSnackbar } from '@/stores/snackbar'
import { can } from '@/utils/auth'
import { useI18n } from 'vue-i18n'

const snackbar = useSnackbar()
const errors = ref({})
const users = ref([])
const allRoles = ref([])
const totalItems = ref(0)
const loading = ref(false)
const loading1 = ref(false)
const loading2 = ref(false)
const currentUser = ref({})
const { t } = useI18n();
const selectedUser = ref(null)
const dialogDelete = ref(false)

const headers = [
    { title: 'ID', key: 'id' },
    { title: 'Name', key: 'name' },
    { title: 'Email', key: 'email' },
    { title: 'Role', key: 'roles' },
    { title: 'Status', key: 'status' },
    { title: t('date'), key: 'updated_at' },
    { title: 'Actions', key: 'actions', sortable: false }
]

const userRoleSelection = reactive({}) // key: user.id, value: array of roles
const roleLoading = reactive({}) // key: user.id, value: boolean

// Saat data user dimuat
const syncUserRoles = (users) => {
    users.forEach(user => {
        userRoleSelection[user.id] = [...user.roles]
        roleLoading[user.id] = false // Reset loading state
    })
}

const filters = ref({
    role: "",
    status: "",
    search: "",
});

const options = ref({
    page: 1,
    itemsPerPage: 10,
    sortBy: [{ key: 'created_at', order: 'desc' }], // Ini adalah format yang diharapkan oleh v-data-table-server
});

const fetchUsers = async () => {
    loading.value = true
    const sortBy = options.value.sortBy.length > 0 ? options.value.sortBy[0].key : 'created_at'
    const sortOrder = options.value.sortBy.length > 0 ? options.value.sortBy[0].order : 'desc'

    const res = await api.get('/users', {
        params: {
            page: options.value.page,
            itemsPerPage: options.value.itemsPerPage,
            sortBy: sortBy, // Kirim key kolom
            sortDesc: sortOrder === 'desc' ? 'true' : 'false', // Kirim 'true' atau 'false'
            search: filters.value.search,
            role: filters.value.role,
            status: filters.value.status,
        }
    })

    // Tambahkan pemetaan untuk ambil nama role saja
    users.value = res.data.data.map(user => ({
        ...user,
        roles: user.roles?.map(r => r.name) || [] // misalnya: ['admin', 'editor']
    }))

    totalItems.value = res.data.total

    syncUserRoles(users.value) // Sinkronisasi roles dengan reactive object
    loading.value = false
}

// Fetch all roles
const fetchRoles = async () => {
    const res = await api.get('/roles')
    allRoles.value = res.data.map(r => r.name) // ['admin', 'editor', 'viewer']
}

const updateRoles = async (user, updatedRoles) => {
    roleLoading[user.id] = true
    try {
        const res = await api.put(`/users/${user.id}/roles`, { roles: updatedRoles })
        user.roles = [...updatedRoles]
        snackbar.showSnackbar(res.data.message)
    } catch (error) {
        snackbar.showSnackbar(error.response.data.message)
        console.error('Failed to update roles: ', error)
    } finally {
        roleLoading[user.id] = false
    }
}

const dialog = ref(false)
const saving = ref(false)
const form = ref({ id: null, name: '', email: '', password: '', role: '', status: 1 })

const openForm = (user = null) => {
    form.value = user
        ? { ...user, password: '' }
        : { id: null, name: '', email: '', password: '', role: '', status: 1 }
    errors.value = {}
    dialog.value = true
}

const saveUser = async () => {
    saving.value = true
    errors.value = {}
    const payload = {
        ...form.value,
        status: form.value.status ? 1 : 0
    }
    try {
        if (form.value.id) {
            const res = await api.put(`/users/${form.value.id}`, payload)
            snackbar.showSnackbar(res.data.message)
        } else {
            const res = await api.post('/users', payload)
            snackbar.showSnackbar(res.data.message)
        }
        dialog.value = false
        await fetchUsers()
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors
        }
        snackbar.showSnackbar(error.response.data.message)
    } finally {
        saving.value = false
    }
}

function confirmDelete(item) {
    selectedUser.value = item
    dialogDelete.value = true
}

const deleteUser = async () => {
    if (!selectedUser.value) return

    loading2.value = true
    try {
        const res = await api.delete(`/users/${selectedUser.value.id}`);
        snackbar.showSnackbar(res.data.message)
        loading2.value = false
        dialogDelete.value = false
        fetchUsers(); // Refresh data setelah penghapusan
    } catch (error) {
        loading2.value = false
        console.error("Error deleting user: ", error);
        snackbar.showSnackbar(error.response.data.message)
    }
}

const toggleStatus = async (user, newStatus) => {
    const oldStatus = user.status
    user.status = newStatus ? 1 : 0

    try {
        const res = await api.patch(`/users/${user.id}/status`)
        snackbar.showSnackbar(`The user status has been successfully changed to ${res.data.status ? 'Active' : 'Inactive'}`)
    } catch (error) {
        user.status = oldStatus
        snackbar.showSnackbar(error.response.data.message)
    }
}

onMounted(async () => {
    await fetchUsers()
    try {
        const res = await api.get('/me')
        currentUser.value = res.data
    } catch {
        currentUser.value = { role: 'user' } // fallback
    }

    fetchRoles()
})
</script>
