<template>
    <v-container fluid>
        <h1 class="mb-3">Products</h1>

        <v-card>
            <v-card-title class="d-flex justify-space-between align-center">
                <v-btn color="primary" @click="openForm()">
                    <v-icon>mdi-plus</v-icon> {{ $t('add') }}
                </v-btn>
            </v-card-title>

            <!-- FILTER -->
            <v-card-subtitle>
                <v-row class="mt-2">
                    <v-col cols="12" sm="6">
                        <v-text-field
                            v-model="filters.search"
                            label="Cari Nama Produk"
                            append-inner-icon="mdi-magnify"
                            clearable
                            @keyup.enter="loadData"
                        />
                    </v-col>
                    <v-col cols="12" sm="2">
                        <v-btn class="mt-2" @click="loadData">Filter</v-btn>
                    </v-col>
                </v-row>
            </v-card-subtitle>

            <!-- TABLE -->
            <v-data-table-server
                :headers="headers"
                :items="items"
                :items-length="totalItems"
                :loading="loading"
                v-model:options="options"
                @update:options="loadData"
            >
                <template #item.price="{ item }">
                    Rp {{ Number(item.price).toLocaleString('id-ID') }}
                </template>

                <template #item.actions="{ item }">
                    <v-btn icon variant="text" @click="openForm(item)">
                        <v-icon color="primary">mdi-pencil</v-icon>
                    </v-btn>
                    <v-btn icon variant="text" @click="confirmDelete(item)">
                        <v-icon color="red">mdi-delete</v-icon>
                    </v-btn>
                </template>
            </v-data-table-server>
        </v-card>

        <!-- DIALOG ADD / EDIT -->
        <v-dialog v-model="dialog" max-width="500">
            <v-card>
                <v-card-title class="d-flex justify-space-between align-center">
                    {{ editedItem.id ? 'Edit' : 'Tambah' }} Product
                    <v-btn icon variant="text" @click="dialog = false">
                        <v-icon>mdi-close</v-icon>
                    </v-btn>
                </v-card-title>

                <v-card-text>
                    <v-text-field
                        v-model="editedItem.name"
                        label="Nama Produk"
                        :error-messages="errors.name"
                        class="mb-3"
                    />

                    <v-text-field
                        v-model="editedItem.price"
                        label="Harga"
                        type="number"
                        :error-messages="errors.price"
                    />
                </v-card-text>

                <v-card-actions>
                    <v-spacer />
                    <v-btn variant="text" @click="dialog = false">
                        {{ $t('cancel') }}
                    </v-btn>
                    <v-btn
                        color="primary"
                        variant="flat"
                        :loading="loading1"
                        @click="saveItem"
                    >
                        <v-icon>mdi-content-save</v-icon>
                        {{ $t('save') }}
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <!-- KONFIRMASI DELETE -->
        <v-dialog v-model="dialogDelete.visible" max-width="400">
            <v-card>
                <v-card-title class="text-h5">
                    {{ $t('confirmation') }} {{ $t('delete') }}
                </v-card-title>
                <v-card-text>
                    {{ $t('confirm_delete') }}
                    <strong>{{ dialogDelete.item?.name }}</strong>?
                </v-card-text>
                <v-card-actions>
                    <v-spacer />
                    <v-btn variant="text" @click="dialogDelete.visible = false">
                        {{ $t('cancel') }}
                    </v-btn>
                    <v-btn color="red" variant="flat" @click="deleteItem">
                        <v-icon>mdi-delete</v-icon>
                        {{ $t('delete') }}
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-container>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import api from '@/axios'
import { useSnackbar } from '@/stores/snackbar'

const snackbar = useSnackbar()

const headers = [
    { title: 'ID', key: 'id', width: 80 },
    { title: 'Nama Produk', key: 'name' },
    { title: 'Harga', key: 'price', width: 150 },
    { title: 'Aksi', key: 'actions', sortable: false, width: 120 },
]

const items = ref([])
const totalItems = ref(0)
const loading = ref(false)
const loading1 = ref(false)
const errors = ref({})

const filters = ref({
    search: '',
})

const options = ref({
    page: 1,
    itemsPerPage: 10,
    sortBy: [{ key: 'created_at', order: 'desc' }],
})

const dialog = ref(false)
const editedItem = reactive({
    id: null,
    name: '',
    price: '',
})

const loadData = async () => {
    loading.value = true

    const sortBy = options.value.sortBy[0]?.key ?? 'created_at'
    const sortOrder = options.value.sortBy[0]?.order ?? 'desc'

    try {
        const { data } = await api.get('/products', {
            params: {
                page: options.value.page,
                itemsPerPage: options.value.itemsPerPage,
                sortBy,
                sortDesc: sortOrder === 'desc' ? 'true' : 'false',
                search: filters.value.search,
            },
        })

        items.value = data.data
        totalItems.value = data.total
    } catch (error) {
        console.error(error)
    } finally {
        loading.value = false
    }
}

const openForm = (item = null) => {
    if (item) {
        Object.assign(editedItem, item)
    } else {
        Object.assign(editedItem, {
            id: null,
            name: '',
            price: '',
        })
    }

    errors.value = {}
    dialog.value = true
}

const saveItem = async () => {
    loading1.value = true
    try {
        if (editedItem.id) {
            await api.put(`/product/update/${editedItem.id}`, editedItem)
        } else {
            await api.post('/product/create', editedItem)
        }

        dialog.value = false
        snackbar.showSnackbar('Data berhasil disimpan')
        await loadData()
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors
        }
        snackbar.showSnackbar(error.response?.data?.message || 'Gagal menyimpan')
    } finally {
        loading1.value = false
    }
}

const dialogDelete = reactive({
    visible: false,
    item: null,
})

const confirmDelete = (item) => {
    dialogDelete.item = item
    dialogDelete.visible = true
}

const deleteItem = async () => {
    try {
        await api.delete(`/product/delete/${dialogDelete.item.id}`)
        dialogDelete.visible = false
        snackbar.showSnackbar('Data berhasil dihapus')
        await loadData()
    } catch (error) {
        console.error(error)
    }
}

onMounted(loadData)
</script>