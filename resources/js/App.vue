<template>
  <router-view />

  <v-snackbar v-model="snackbar.visible.value" :color="snackbar.color.value" :timeout="snackbar.timeout.value" location="bottom center" class="mb-5">
    {{ snackbar.message }}
    <template #actions>
      <v-btn icon @click="snackbar.closeSnackbar()">
        <v-icon>mdi-close</v-icon>
      </v-btn>
    </template>
  </v-snackbar>

</template>

<script setup>
import { useSnackbar } from '@/stores/snackbar'
const snackbar = useSnackbar()
</script>

<style>
/* global style if needed */
</style>
