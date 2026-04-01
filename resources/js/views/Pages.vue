<template>
  <v-container fluid>
    <h1 class="mb-3">Pages</h1>
    <v-card>
      <v-card-title class="d-flex justify-between align-center">
        <v-btn color="primary" @click="openAdd" class="mb-3"><v-icon>mdi-plus</v-icon> {{ $t('add') }}</v-btn>
      </v-card-title>
      <v-card-subtitle>
        <v-text-field v-model="search" placeholder="Cari Judul / Konten"
          append-inner-icon="mdi-magnify" clearable @keyup.enter="fetchPages" @click:clear="fetchPages" />
      </v-card-subtitle>
      <v-data-table-server :headers="headers" :items="pages" :items-length="totalItems" :loading="loading"
        v-model:options="options" @update:options="fetchPages">
        <template #item.updated_at="{ item }">
          {{ $t('created') }}: {{ $helpers.formatDate(item.created_at) }}<br />
          {{ $t('updated') }}: {{ $helpers.formatDate(item.updated_at) }}
        </template>
        <template #item.active="{ item }">
          <v-chip :color="item.active ? 'green' : 'red'" dark>
            {{ item.active ? 'Aktif' : 'Nonaktif' }}
          </v-chip>
        </template>

        <template #item.actions="{ item }">
          <v-btn icon variant="text" @click="openEdit(item)" class="mr-2">
            <v-icon color="primary">mdi-pencil</v-icon>
          </v-btn>
          <v-btn icon variant="text" @click="confirmDelete(item)">
            <v-icon color="red">mdi-delete</v-icon>
          </v-btn>
        </template>
      </v-data-table-server>
    </v-card>

    <!-- Modal Add/Edit -->
    <v-dialog v-model="modal.visible" scrollable max-width="900px">
      <v-card>
        <v-card-title class="d-flex justify-space-between align-center">
          {{ modal.editing ? 'Edit' : 'Add' }} Pages
          <v-btn variant="text" icon="mdi-close" @click="modal.visible = false"></v-btn>
        </v-card-title>
        <v-card-text>
          <v-form ref="formRef" @submit.prevent="savePage">
            <v-text-field v-model="form.title" label="Judul (ID)" :error-messages="errors.title" class="mb-2"
              required />
            <v-text-field v-model="form.title_en" label="Judul (EN)" :error-messages="errors.title_en" class="mb-2"
              required />
            <p class="mb-2">Konten (ID)</p>
            <WysiwygEditor v-model="form.body" :error-messages="errors.body" class="mb-4" />
            <p class="mb-2">Konten (EN)</p>
            <WysiwygEditor v-model="form.body_en" :error-messages="errors.body_en" class="mb-4" />
            <v-switch v-model="form.active" color="success" label="Aktif?" />
          </v-form>
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn @click="modal.visible = false">{{ $t('cancel') }}</v-btn>
          <v-btn color="primary" variant="flat" @click="savePage" :loading="loading1"><v-icon>mdi-content-save</v-icon>
            {{ $t('save') }}</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Konfirmasi Delete -->
    <v-dialog v-model="dialogDelete.visible" max-width="400">
      <v-card>
        <v-card-title class="text-h5">{{ $t('confirmation') }} {{ $t('delete') }}</v-card-title>
        <v-card-text>
          {{ $t('confirm_delete') }} "<strong>{{ dialogDelete.item?.title }}</strong>"?
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn variant="text" @click="dialogDelete.visible = false">{{ $t('cancel') }}</v-btn>
          <v-btn color="red" variant="flat" @click="deletePage">
            <v-icon>mdi-delete</v-icon>
            {{ $t('delete') }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-container>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import api from '@/axios'
import WysiwygEditor from '@/components/WysiwygEditor.vue'
import { useSnackbar } from '@/stores/snackbar'
import { useI18n } from 'vue-i18n'

const search = ref('')
const snackbar = useSnackbar()
const pages = ref([])
const totalItems = ref(0)
const loading = ref(false)
const loading1 = ref(false)
const errors = ref({})
const { t } = useI18n();

const headers = computed(() => [
  { title: 'Title', key: 'title' },
  { title: 'Slug', key: 'slug' },
  { title: 'Aktif', key: 'active' },
  { title: t('date'), key: 'updated_at' },
  { title: 'Actions', key: 'actions', sortable: false },
])

const options = ref({
  page: 1,
  itemsPerPage: 10,
  sortBy: [{ key: 'created_at', order: 'desc' }],
});

const fetchPages = async () => {
  loading.value = true
  const sortBy = options.value.sortBy.length > 0 ? options.value.sortBy[0].key : 'created_at'
  const sortOrder = options.value.sortBy.length > 0 ? options.value.sortBy[0].order : 'desc'

  try {
    const { data } = await api.get('/pages', {
      params: {
        page: options.value.page,
        itemsPerPage: options.value.itemsPerPage,
        sortBy,
        sortDesc: sortOrder === 'desc' ? 'true' : 'false',
        search: search.value, // <--- tambahan di sini
      },
    })
    pages.value = data?.data || []
    totalItems.value = data?.total || 0
  } finally {
    loading.value = false
  }
}

onMounted(fetchPages)

// Modal Tambah/Edit
const modal = reactive({
  visible: false,
  editing: false,
})

const formRef = ref(null)

const form = reactive({
  title: '',
  title_en: '',
  body: '',
  body_en: '',
  active: true,
  id: null,
})

const resetForm = () => {
  Object.assign(form, {
    title: '',
    title_en: '',
    body: '',
    body_en: '',
    active: true,
    id: null,
  })
}

const openAdd = () => {
  resetForm()
  errors.value = {}
  modal.editing = false
  modal.visible = true
}

const openEdit = (item) => {
  Object.assign(form, {
    ...item,
    active: item.active === 1 // pastikan boolean
  })
  errors.value = {}
  modal.editing = true
  modal.visible = true
}

const savePage = async () => {
  loading1.value = true;
  const payload = {
    ...form,
    active: form.active ? 1 : 0
  }
  try {
    if (modal.editing) {
      await api.put(`/pages/${form.id}`, payload)
    } else {
      await api.post('/pages', payload)
    }
    loading1.value = false;
    modal.visible = false
    fetchPages()
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors
    }
    console.error(error)
    snackbar.showSnackbar(error.response.data.message)
    loading1.value = false;
  }
}

// Modal Konfirmasi Delete
const dialogDelete = reactive({
  visible: false,
  item: null,
})

const confirmDelete = (item) => {
  dialogDelete.item = item
  dialogDelete.visible = true
}

const deletePage = async () => {
  try {
    await api.delete(`/pages/${dialogDelete.item.id}`)
    dialogDelete.visible = false
    fetchPages()
  } catch (error) {
    console.error(error)
  }
}
</script>
