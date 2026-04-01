<template>
    <v-container fluid>
        <h1 class="mb-3">Menu</h1>
        <v-card>
            <v-card-title class="mb-3">
                <div class="d-flex justify-between w-100 align-center">
                    <div>
                        <v-btn color="primary" @click="openAdd()" class="mr-2"><v-icon>mdi-plus</v-icon> {{ $t('add')
                        }}</v-btn>
                        <v-btn color="success" @click="saveOrder" :loading="loading1"><v-icon>mdi-content-save</v-icon>
                            {{ $t('save') }}
                            Urutan</v-btn>
                    </div>
                </div>
            </v-card-title>

            <v-card-text>
                <div v-if="loadMenu == true">
                    <v-skeleton-loader type="list-item-three-line"></v-skeleton-loader>
                    <v-skeleton-loader type="list-item-three-line"></v-skeleton-loader>
                    <v-skeleton-loader type="list-item-three-line"></v-skeleton-loader>
                    <v-skeleton-loader type="list-item-three-line"></v-skeleton-loader>
                </div>
                <div v-else>
                    <draggable v-model="menus" item-key="id" handle=".drag-handle"
                        :group="{ name: 'menu', pull: true, put: true }" :force-fallback="true" :fallback-on-body="true"
                        :scroll="true" :scroll-sensitivity="120" :scroll-speed="20">
                        <template #item="{ element }">
                            <v-card class="mb-2" :color="element.is_active ? 'grey-lighten-4' : 'red-lighten-4'">
                                <v-card-text class="d-flex justify-between align-start">
                                    <div class="d-flex align-center mr-5">
                                        <v-icon class="drag-handle mr-2">mdi-drag</v-icon>
                                        <div>
                                            <strong>{{ element.title }}</strong>
                                            <v-chip size="x-small" :color="element.is_active ? 'green' : 'red'"
                                                class="ml-1" label>
                                                {{ element.is_active ? 'Active' : 'Inactive' }}
                                            </v-chip><br />
                                            <small>{{ element.route || '—' }}</small>
                                        </div>
                                    </div>

                                    <div>
                                        <v-btn icon variant="text" @click="openAdd(element)">
                                            <v-icon>mdi-plus</v-icon>
                                        </v-btn>
                                        <v-btn icon variant="text" @click="openEdit(element)">
                                            <v-icon
                                                :color="element.is_active ? 'primary' : 'primary'">mdi-pencil</v-icon>
                                        </v-btn>
                                        <v-btn icon variant="text" @click="confirmDelete(element)">
                                            <v-icon color="red">mdi-delete</v-icon>
                                        </v-btn>
                                    </div>
                                </v-card-text>

                                <v-card-text v-if="element.children?.length">
                                    <draggable v-model="element.children" item-key="id" handle=".drag-handle"
                                        :group="{ name: 'menu', pull: true, put: true }">
                                        <template #item="{ element: child }">
                                            <v-card class="mb-2 ml-6"
                                                :color="child.is_active ? 'grey-lighten-3' : 'red-lighten-4'">
                                                <v-card-text class="d-flex justify-between align-start">
                                                    <div class="d-flex align-center mr-5">
                                                        <v-icon class="drag-handle mr-2">mdi-drag</v-icon>
                                                        <div>
                                                            <strong>{{ child.title }}</strong>
                                                            <v-chip size="x-small"
                                                                :color="child.is_active ? 'green' : 'red'" class="ml-1"
                                                                label>
                                                                {{ child.is_active ? 'Active' : 'Inactive' }}
                                                            </v-chip><br />
                                                            <small>{{ child.route || '—' }}</small>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <v-btn icon variant="text" @click="openEdit(child)">
                                                            <v-icon
                                                                :color="child.is_active ? 'primary' : 'primary'">mdi-pencil</v-icon>
                                                        </v-btn>
                                                        <v-btn icon variant="text" @click="confirmDelete(child)">
                                                            <v-icon color="red">mdi-delete</v-icon>
                                                        </v-btn>
                                                    </div>
                                                </v-card-text>
                                            </v-card>
                                        </template>
                                    </draggable>
                                </v-card-text>
                            </v-card>
                        </template>
                    </draggable>

                </div>
            </v-card-text>
        </v-card>

        <!-- Dialog Form -->
        <v-dialog v-model="formDialog" max-width="500">
            <v-card>
                <v-card-title class="d-flex justify-space-between align-center">
                    {{ form.id ? 'Edit' : 'Tambah' }} Menu
                    <v-btn variant="text" icon="mdi-close" @click="formDialog = false"></v-btn>
                </v-card-title>
                <v-card-text>
                    <v-text-field v-model="form.title" label="Title" :error-messages="errors.title" class="mb-2" />
                    <v-text-field v-model="form.icon" label="Icon (mdi-xxx)" :error-messages="errors.icon"
                        class="mb-2" />
                    <v-text-field v-model="form.route" label="Route (/admin/xxx)" class="mb-2" />
                    <v-text-field v-model="form.permission_key" label="Permission (opsional)" class="mb-2" />
                    <v-switch v-model="form.is_active" color="success" label="Aktif?" />
                </v-card-text>
                <v-card-actions>
                    <v-spacer />
                    <v-btn @click="formDialog = false">{{ $t('cancel') }}</v-btn>
                    <v-btn color="primary" variant="flat" @click="save"
                        :loading="loading"><v-icon>mdi-content-save</v-icon>
                        {{ $t('save') }}</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <v-dialog v-model="dialogDelete" max-width="400">
            <v-card>
                <v-card-title class="text-h5">{{ $t('confirmation') }} {{ $t('delete') }}</v-card-title>
                <v-card-text>
                    {{ $t('confirm_delete') }} <strong>{{ selectedMenu?.title }}</strong>?
                </v-card-text>
                <v-card-actions>
                    <v-spacer />
                    <v-btn variant="text" @click="dialogDelete = false">{{ $t('cancel') }}</v-btn>
                    <v-btn color="red" variant="flat" :loading="loading2" @click="deleteMenu">
                        <v-icon>mdi-delete</v-icon>
                        {{ $t('delete') }}
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-container>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import draggable from 'vuedraggable'
import api from '@/axios'
import { useSnackbar } from '@/stores/snackbar'
import eventBus from '@/eventBus'

const menus = ref([])
const formDialog = ref(false)
const dialogDelete = ref(false)
const form = ref({})
const loadMenu = ref(false)
const loading = ref(false)
const loading1 = ref(false)
const loading2 = ref(false)
const snackbar = useSnackbar()
const errors = ref({})
const selectedMenu = ref(null)

const fetchMenus = async () => {
    loadMenu.value = true
    try {
        const { data } = await api.get('/menus/all')
        menus.value = data
    } finally {
        loadMenu.value = false
    }
}

onMounted(fetchMenus)

const openAdd = (parent = null) => {
    form.value = {
        id: null,
        title: '',
        icon: '',
        route: '',
        permission_key: '',
        parent_id: parent ? parent.id : null,
        is_active: true
    }
    formDialog.value = true
}

const openEdit = (menu) => {
    form.value = {
        ...menu,
        is_active: !!menu.is_active // pastikan boolean
    }
    formDialog.value = true
}

const save = async () => {
    loading.value = true;
    errors.value = {}; // Reset error validasi sebelumnya
    const payload = {
        ...form.value,
        is_active: form.value.is_active ? 1 : 0
    };
    try {
        if (form.value.id) {
            const res = await api.put(`/menus/${form.value.id}`, payload);
            snackbar.showSnackbar(res.data.message)
        } else {
            await api.post('/menus', payload);
        }
        formDialog.value = false;
        await fetchMenus(); // pastikan fetch selesai sebelum loading false
        // === EMIT EVENT ke komponen lain ===
        eventBus.emit('menus-updated', menus.value)
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors || {};
        }
        console.error('Save menu error: ', error);
        snackbar.showSnackbar(error.response.data.message);
    } finally {
        loading.value = false;
    }
};

function confirmDelete(item) {
    selectedMenu.value = item
    dialogDelete.value = true
}

const deleteMenu = async () => {
    if (!selectedMenu.value) return

    loading2.value = true
    try {
        const res = await api.delete(`/menus/${selectedMenu.value.id}`);
        snackbar.showSnackbar(res.data.message)
        await fetchMenus(); // Refresh data setelah penghapusan
        // === EMIT EVENT ke komponen lain ===
        eventBus.emit('menus-updated', menus.value)
    } catch (error) {
        console.error("Error deleting menu: ", error);
        snackbar.showSnackbar(error.response.data.message);
    } finally {
        loading2.value = false
        dialogDelete.value = false
    }
}

const saveOrder = async () => {
    loading1.value = true;
    const flattened = []

    const flatten = (items, parentId = null) => {
        items.forEach((item, index) => {
            flattened.push({
                id: item.id,
                order: index,
                parent_id: parentId,
            })
            if (item.children && item.children.length) {
                flatten(item.children, item.id)
            }
        })
    }

    flatten(menus.value)

    const res = await api.post('/menus/reorder-nested', { orders: flattened })
    loading1.value = false;
    snackbar.showSnackbar(res.data.message)
    await fetchMenus()
    // === EMIT EVENT ke komponen lain ===
    eventBus.emit('menus-updated', menus.value)
}
</script>