<script setup lang="ts">
import type { SelectLabelProps } from "reka-ui"
import type { HTMLAttributes } from "vue"
import { reactiveOmit } from "@vueuse/core"
import { SelectLabel } from "reka-ui"

import { cn } from "@/lib/utils"

defineOptions({
  inheritAttrs: false,
})

const props = defineProps<SelectLabelProps & { class?: HTMLAttributes["class"] }>()

const delegated = reactiveOmit(props, "class")
</script>

<template>
  <SelectLabel
    data-slot="select-label"
    v-bind="{ ...$attrs, ...delegated }"
    :class="cn('px-2 py-1.5 text-sm font-semibold text-foreground', props.class)"
  >
    <slot />
  </SelectLabel>
</template>
