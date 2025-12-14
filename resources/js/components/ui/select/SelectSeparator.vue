<script setup lang="ts">
import type { SelectSeparatorProps } from "reka-ui"
import type { HTMLAttributes } from "vue"
import { reactiveOmit } from "@vueuse/core"
import { SelectSeparator } from "reka-ui"

import { cn } from "@/lib/utils"

defineOptions({
  inheritAttrs: false,
})

const props = defineProps<SelectSeparatorProps & { class?: HTMLAttributes["class"] }>()

const delegated = reactiveOmit(props, "class")
</script>

<template>
  <SelectSeparator
    data-slot="select-separator"
    v-bind="{ ...$attrs, ...delegated }"
    :class="cn('my-1 h-px bg-border', props.class)"
  />
</template>
