<template>
    <v-container fluid>
        <h1 class="mb-4">Report Purchases</h1>

        <!-- FILTER -->
        <v-card class="mb-4">
            <v-card-text>
                <v-row>
                    <v-col cols="12" md="3">
                        <v-text-field v-model="filters.start_date" label="Start Date" type="date" />
                    </v-col>

                    <v-col cols="12" md="3">
                        <v-text-field v-model="filters.end_date" label="End Date" type="date" />
                    </v-col>

                    <v-col cols="12" md="4">
                        <v-autocomplete v-model="filters.product_id" :items="products" item-title="name" item-value="id"
                            label="Product (Optional)" clearable />
                    </v-col>

                    <v-col cols="12" md="2">
                        <v-btn color="primary" block @click="fetchReport">
                            <v-icon start>mdi-magnify</v-icon>
                            Search
                        </v-btn>
                    </v-col>
                </v-row>
            </v-card-text>
        </v-card>

        <!-- TABLE -->
        <v-card>
            <v-data-table :headers="headers" :items="items" :loading="loading" class="elevation-1">
                <template #item.total_amount="{ item }">
                    Rp {{ formatCurrency(item.total_amount) }}
                </template>
            </v-data-table>
        </v-card>
    </v-container>
</template>

<script>
import api from '@/axios'
import { useSnackbar } from '@/stores/snackbar'

export default {
    name: 'PurchaseReport',

    data() {
        return {
            loading: false,
            items: [],
            products: [],
            filters: {
                start_date: '',
                end_date: '',
                product_id: null
            },
            headers: [
                { title: 'Tanggal', value: 'tanggal' },
                { title: 'Produk', value: 'nama_produk' },
                { title: 'Total Transaksi', value: 'total_transaksi' },
                { title: 'Total Qty', value: 'total_qty' },
                { title: 'Total Amount', value: 'total_amount' }
            ]
        }
    },

    created() {
        this.setDefaultDate()
        this.fetchProducts()
    },

    methods: {
        setDefaultDate() {
            const today = new Date().toISOString().substring(0, 10)
            this.filters.start_date = today
            this.filters.end_date = today
        },

        async fetchProducts() {
            try {
                const res = await api.get('/products')
                this.products = res.data.data ?? []
            } catch (error) {
                console.error(error)
            }
        },

        async fetchReport() {
            this.loading = true
            try {
                const res = await api.get('/report/purchases', {
                    params: this.filters
                })
                this.items = res.data.data
            } catch (error) {
                console.error(error)
            } finally {
                this.loading = false
            }
        },

        formatCurrency(value) {
            return new Intl.NumberFormat('id-ID').format(value)
        }
    }
}
</script>