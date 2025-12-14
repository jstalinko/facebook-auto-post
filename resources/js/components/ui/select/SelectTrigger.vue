<script setup lang="ts">
import type { SelectTriggerProps } from "reka-ui"
import type { HTMLAttributes } from "vue"
import { reactiveOmit } from "@vueuse/core"
import { SelectTrigger } from "reka-ui"

import { cn } from "@/lib/utils"

defineOptions({
  inheritAttrs: false,
})

const props = withDefaults(
  defineProps<SelectTriggerProps & { class?: HTMLAttributes["class"] }>(),
  {
    disabled: false,
  },
)

const delegated = reactiveOmit(props, "class")
</script>

<template>
  <SelectTrigger
    data-slot="select-trigger"
    v-bind="{ ...$attrs, ...delegated }"
    :class="cn('flex h-10 w-full items-center justify-between rounded-md border bg-background px-3 py-2 text-sm shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50', props.class)"
  >
    <slot />
  </SelectTrigger>
</template>
