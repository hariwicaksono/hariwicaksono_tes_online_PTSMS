<template>
  <QuillEditor 
    v-model:content="content"
    content-type="html"
    theme="snow"
    toolbar="full"
  />
</template>

<script setup>
import { QuillEditor } from '@vueup/vue-quill'
import '@vueup/vue-quill/dist/vue-quill.snow.css'
import { ref, watch } from 'vue'

const props = defineProps({
  modelValue: String,
})

const emit = defineEmits(['update:modelValue'])

const content = ref(props.modelValue || '')

watch(content, (val) => {
  emit('update:modelValue', val)
})

watch(() => props.modelValue, (val) => {
  if (val !== content.value) content.value = val
})
</script>
