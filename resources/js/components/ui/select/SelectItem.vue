<script setup lang="ts">
import { SelectItem, type SelectItemProps } from 'reka-ui'
import { cn } from '@/lib/utils'

interface Props extends SelectItemProps {}

const props = defineProps<Props>()
</script>

<template>
  <SelectItem
    v-bind="props"
    :class="cn(
      'relative flex w-full cursor-default select-none items-center rounded-sm py-1.5 pl-2 pr-8 text-sm outline-none focus:bg-accent focus:text-accent-foreground data-[disabled]:pointer-events-none data-[disabled]:opacity-50',
      props.class,
    )"
  >
    <slot />
  </SelectItem>
</template>