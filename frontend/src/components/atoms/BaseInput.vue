<script setup>
defineProps({
  modelValue: { type: String, default: '' },
  type:       { type: String, default: 'text' },
  placeholder:{ type: String, default: '' },
  label:      { type: String, default: '' },
})
defineEmits(['update:modelValue'])
</script>

<template>
  <div class="mb-3">
    <label v-if="label" class="form-label fw-bold">{{ label }}</label>
    <input
      :type="type"
      :value="modelValue"
      :placeholder="placeholder"
      class="form-control"
      @input="$emit('update:modelValue', $event.target.value)"
    />
  </div>
</template>