<template>
    <v-container fluid>
        <h1 class="mb-3">{{ $t('settings') }}</h1>
        <v-card>
            <v-card-title>
                <v-text-field v-model="search" prepend-inner-icon="mdi-magnify" :label="t('search_settings')"
                    class="mb-4" clearable />
            </v-card-title>
            <v-data-table :headers="headers" :items="filteredSettings" :search="search" item-value="key"
                items-per-page="-1" :loading="loading">
                <template #item.updated_at="{ item }">
                    {{ $helpers.formatDate(item.updated_at) }}
                </template>
                <template #item.actions="{ item }">
                    <div v-if="item.key == 'app_developer'"></div>
                    <div v-else>
                        <v-btn icon variant="text" @click="openDialog(item)">
                            <v-icon color="primary">mdi-pencil</v-icon>
                        </v-btn>
                    </div>
                </template>
            </v-data-table>
        </v-card>

        <!-- Dialog Edit -->
        <v-dialog v-model="dialog" width="500">
            <v-card>
                <v-card-title class="d-flex justify-space-between align-center">
                    Edit {{ $t('settings') }}
                    <v-btn variant="text" icon="mdi-close" @click="dialog = false"></v-btn>
                </v-card-title>
                <v-divider />
                <v-card-text>
                    <v-text-field label="Key" v-model="editedItem.key" disabled />

                    <!-- Jika site_logo -->
                    <div v-if="editedItem.key === 'site_logo'">
                        <v-file-input v-model="editedItem.file" label="Upload Logo" accept="image/*"
                            @change="onFileChange" :error-messages="errors.file" hide-details="auto" class="mb-3" />
                        <v-img :src="previewUrl || editedItem.value" max-width="150" />
                    </div>

                    <!-- Jika site_background -->
                    <div v-else-if="editedItem.key === 'site_background'">
                        <v-file-input v-model="editedItem.file" label="Upload Background" accept="image/*"
                            @change="onFileChange" :error-messages="errors.file" hide-details="auto" class="mb-3" />
                        <v-img :src="previewUrl || editedItem.value" max-width="150" />
                    </div>

                    <!-- Jika value biasa -->
                    <v-textarea rows="3" v-else label="Value" v-model="editedItem.value" :error-messages="errors.value"
                        hide-details="auto" />
                </v-card-text>

                <v-card-actions>
                    <v-spacer />
                    <v-btn text @click="dialog = false">{{ $t('cancel') }}</v-btn>
                    <v-btn color="primary" variant="flat" @click="saveSetting" :loading="loading1">
                        <v-icon>mdi-content-save</v-icon>
                        {{ $t('save') }}
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-container>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/axios'
import { useSnackbar } from '@/stores/snackbar'
import { useI18n } from 'vue-i18n'
import eventBus from '@/eventBus'

const { t } = useI18n()
const settings = ref([])
const search = ref('')
const dialog = ref(false)
const loading = ref(false)
const loading1 = ref(false)

const editedItem = ref({
    key: '',
    value: '',
    file: null,
})

const previewUrl = ref(null)
const errors = ref({})
const snackbar = useSnackbar()

const headers = [
    { title: 'ID', key: 'id' },
    { title: 'Key', key: 'key' },
    { title: 'Value', key: 'value', sortable: false },
    { title: 'Updated', key: 'updated_at' },
    { title: 'Actions', key: 'actions', sortable: false },
]

const filteredSettings = computed(() => {
    if (!search.value) return settings.value
    const term = search.value.toLowerCase()

    return settings.value.filter(item =>
        item.key.toLowerCase().includes(term) ||
        (item.value && item.value.toString().toLowerCase().includes(term))
    )
})

const fetchSettings = async () => {
    loading.value = true
    const { data } = await api.get('/settings')
    settings.value = data
    loading.value = false

    // Load new settings
    const res = await api.get('/settings/app')
    localStorage.setItem('setting', JSON.stringify(res.data))

    // Push Mitt
    eventBus.emit('settings-updated', res.data)
}

const openDialog = (item) => {
    editedItem.value = {
        key: item.key,
        value: item.value,
        file: null,
    }
    previewUrl.value = null
    errors.value = {}
    dialog.value = true
}

const onFileChange = (files) => {
    const file = Array.isArray(files) ? files[0] : files
    if (file) {
        const reader = new FileReader()
        reader.onload = (e) => (previewUrl.value = e.target.result)
        reader.readAsDataURL(file)
        editedItem.value.file = file
    } else {
        editedItem.value.file = null
    }
}

const saveSetting = async () => {
    errors.value = {}
    const form = new FormData()

    if (editedItem.value.key === 'site_logo') {
        if (!editedItem.value.file) {
            errors.value.logo = ['Pilih file terlebih dahulu']
            snackbar.showSnackbar('Pilih file terlebih dahulu!')
            return
        }
        form.append('logo', editedItem.value.file)
    } else if (editedItem.value.key === 'site_background') {
        if (!editedItem.value.file) {
            errors.value.background = ['Pilih file terlebih dahulu']
            snackbar.showSnackbar('Pilih file terlebih dahulu!')
            return
        }
        form.append('background', editedItem.value.file)
    } else {
        form.append('value', editedItem.value.value)
    }

    loading1.value = true
    try {
        const res = await api.post(`/settings/${editedItem.value.key}`, form)
        snackbar.showSnackbar(res.data.message)
        dialog.value = false
        fetchSettings()
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors
            //setTimeout(() => (errors.value = {}), 4000)
        }
        snackbar.showSnackbar(error.response?.data?.message || 'Failed to save setting')
    }
    loading1.value = false
}

onMounted(fetchSettings)
</script>
