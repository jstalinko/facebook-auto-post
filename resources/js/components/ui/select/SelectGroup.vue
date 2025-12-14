<script setup lang="ts">
import type { SelectGroupProps } from "reka-ui"
import { SelectGroup } from "reka-ui"

defineProps<SelectGroupProps>()
</script>

<template>
  <SelectGroup data-slot="select-group" v-bind="$attrs">
    <slot />
  </SelectGroup>
</template>
