<script setup lang="ts">
import { SelectValue, type SelectValueProps } from 'reka-ui'

interface Props extends SelectValueProps {}

const props = defineProps<Props>()
</script>

<template>
  <SelectValue v-bind="props">
    <slot />
  </SelectValue>
</template>