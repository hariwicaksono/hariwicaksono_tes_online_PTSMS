<template>
    <HomeLayout>
        <v-container>
            <div class="mt-8 mb-8">
                <div role="status" class="animate-pulse mt-10" v-if="loading == true">
                    <span class="sr-only">Loading...</span>
                </div>
                <div v-else>
                    <h2 class="mb-5">{{ pages.title }}</h2>
                    <div v-html="pages.body"></div>
                </div>
            </div>
        </v-container>
    </HomeLayout>
</template>

<script setup>
import HomeLayout from '@/layouts/HomeLayout.vue'
import { ref, onMounted } from 'vue'
import axios from 'axios'

const pages = ref([])
const loading = ref(false)

const fetchPages = async () => {
    loading.value = true
    const { data } = await axios.get('/api/pages/slug/terms')
    pages.value = data
    loading.value = false
}

onMounted(fetchPages)
</script>
