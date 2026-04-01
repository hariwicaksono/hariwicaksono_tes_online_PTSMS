<template>
    <v-container fluid>
        <h1 class="mb-3">Purchases</h1>

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
                        <v-text-field v-model="filters.search" label="Cari (Tanggal / Total)"
                            append-inner-icon="mdi-magnify" clearable @keyup.enter="loadData" />
                    </v-col>
                    <v-col cols="12" sm="2">
                        <v-btn class="mt-2" @click="loadData">Filter</v-btn>
                    </v-col>
                </v-row>
            </v-card-subtitle>

            <!-- TABLE -->
            <v-data-table-server :headers="headers" :items="items" :items-length="totalItems" :loading="loading"
                v-model:options="options" @update:options="loadData">
                <template #item.total_price="{ item }">
                    Rp {{ Number(item.total_price).toLocaleString('id-ID') }}
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
        <v-dialog v-model="dialog" max-width="700">
            <v-card>
                <v-card-title class="d-flex justify-space-between align-center">
                    {{ editedItem.id ? 'Edit' : 'Tambah' }} Purchase
                    <v-btn icon variant="text" @click="dialog = false">
                        <v-icon>mdi-close</v-icon>
                    </v-btn>
                </v-card-title>

                <v-card-text>
                    <v-text-field v-model="editedItem.date" type="date" label="Tanggal" :error-messages="errors.date"
                        class="mb-4" />

                    <h4 class="mb-2">Items</h4>

                    <v-row v-for="(item, index) in editedItem.items" :key="index" class="mb-2">
                        <v-col cols="5">
                            <v-autocomplete v-model="item.product_id" :items="products" item-title="name"
                                item-value="id" label="Produk" :loading="loadingProducts" clearable
                                @update:model-value="onProductChange(item)" />
                        </v-col>
                        <v-col cols="3">
                            <v-text-field v-model="item.qty" label="Qty" type="number" />
                        </v-col>
                        <v-col cols="3">
                            <v-text-field v-model="item.price" label="Harga" type="number" />
                        </v-col>
                        <v-col cols="1" class="d-flex align-center">
                            <v-btn icon color="red" variant="text" @click="removeItem(index)">
                                <v-icon>mdi-delete</v-icon>
                            </v-btn>
                        </v-col>
                    </v-row>

                    <v-btn variant="outlined" size="small" @click="addItem">
                        <v-icon>mdi-plus</v-icon> Tambah Item
                    </v-btn>

                    <div class="text-right mt-4">
                        <strong>
                            Total:
                            Rp {{ totalPrice.toLocaleString('id-ID') }}
                        </strong>
                    </div>
                </v-card-text>

                <v-card-actions>
                    <v-spacer />
                    <v-btn variant="text" @click="dialog = false">
                        {{ $t('cancel') }}
                    </v-btn>
                    <v-btn color="primary" variant="flat" :loading="loading1" @click="saveItem">
                        <v-icon>mdi-content-save</v-icon>
                        {{ $t('save') }}
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <!-- DELETE -->
        <v-dialog v-model="dialogDelete.visible" max-width="400">
            <v-card>
                <v-card-title class="text-h5">
                    {{ $t('confirmation') }} {{ $t('delete') }}
                </v-card-title>
                <v-card-text>
                    Yakin hapus purchase tanggal
                    <strong>{{ dialogDelete.item?.date }}</strong>?
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
import { ref, reactive, computed, onMounted } from 'vue'
import api from '@/axios'
import { useSnackbar } from '@/stores/snackbar'

const snackbar = useSnackbar()

const headers = [
    { title: 'ID', key: 'id', width: 80 },
    { title: 'Tanggal', key: 'date' },
    { title: 'Total', key: 'total_price' },
    { title: 'Aksi', key: 'actions', sortable: false, width: 200 },
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
    date: '',
    items: [],
})

const totalPrice = computed(() =>
    editedItem.items.reduce(
        (sum, i) => sum + Number(i.qty || 0) * Number(i.price || 0),
        0
    )
)

const loadData = async () => {
    loading.value = true

    const sortBy = options.value.sortBy[0]?.key ?? 'created_at'
    const sortOrder = options.value.sortBy[0]?.order ?? 'desc'

    try {
        const { data } = await api.get('/purchases', {
            params: {
                page: options.value.page,
                itemsPerPage: options.value.itemsPerPage,
                sortBy,
                sortDesc: sortOrder === 'desc',
                search: filters.value.search,
            },
        })

        items.value = data.data
        totalItems.value = data.total
    } finally {
        loading.value = false
    }
}

const openForm = (item = null) => {
    if (item) {
        Object.assign(editedItem, {
            id: item.id,
            date: item.date,
            items: item.items.map(i => ({ ...i })),
        })
    } else {
        Object.assign(editedItem, {
            id: null,
            date: '',
            items: [],
        })
    }

    errors.value = {}
    dialog.value = true
}

const addItem = () => {
    editedItem.items.push({
        product_id: '',
        qty: 1,
        price: 0,
    })
}

const removeItem = (index) => {
    editedItem.items.splice(index, 1)
}

const saveItem = async () => {
    loading1.value = true
    try {
        if (editedItem.id) {
            await api.put(`/purchase/update/${editedItem.id}`, editedItem)
        } else {
            await api.post('/purchase/create', editedItem)
        }

        dialog.value = false
        snackbar.showSnackbar('Data berhasil disimpan')
        await loadData()
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors
        }
        snackbar.showSnackbar('Gagal menyimpan data')
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
    await api.delete(`/purchase/delete/${dialogDelete.item.id}`)
    dialogDelete.visible = false
    snackbar.showSnackbar('Data berhasil dihapus')
    loadData()
}

const products = ref([])
const loadingProducts = ref(false)

const loadProducts = async () => {
    loadingProducts.value = true
    try {
        const { data } = await api.get('/products', {
            params: {
                itemsPerPage: 1000, // ambil banyak untuk dropdown
            },
        })

        products.value = data.data
    } finally {
        loadingProducts.value = false
    }
}

const onProductChange = (row) => {
    const product = products.value.find(p => p.id === row.product_id)
    if (product) {
        row.price = product.price
        row.qty = row.qty || 1
    }
}

onMounted(() => {
    loadData()
    loadProducts()
})
</script>