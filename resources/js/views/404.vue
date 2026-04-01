<template>
  <v-container class="fill-height d-flex flex-column align-center justify-center text-center px-4">
    <v-img :aspect-ratio="1" class="bg-white" src="/images/oops-404.png" width="400"></v-img>
    <h1 class="text-h3 font-weight-bold mb-2">404 - Halaman Tidak Ditemukan</h1>
    <p class="text-body-1 mb-6">
      Halaman yang Anda cari mungkin telah dipindahkan, dihapus, atau tidak pernah ada.
    </p>
    <v-btn color="primary" variant="text" size="large" @click="$router.push('/')">
      <v-icon start>mdi-home</v-icon>
      Kembali ke Beranda
    </v-btn>
  </v-container>
</template>

<script setup>
</script>
