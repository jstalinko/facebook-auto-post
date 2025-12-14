<script setup>
import { computed, ref, watch } from 'vue'
import { CalendarIcon, ChevronDownIcon, X } from 'lucide-vue-next'
import { getLocalTimeZone, parseDate } from '@internationalized/date'
import {
    CalendarCell,
    CalendarCellTrigger,
    CalendarGrid,
    CalendarGridBody,
    CalendarGridHead,
    CalendarGridRow,
    CalendarHeadCell,
    CalendarHeader,
    CalendarHeading,
    CalendarNext,
    CalendarPrev,
    CalendarRoot,
    PopoverContent,
    PopoverRoot,
    PopoverTrigger,
} from 'reka-ui'

import Button from '@/components/ui/button/Button.vue'
import Input from '@/components/ui/input/Input.vue'

const props = defineProps({
    modelValue: {
        type: String,
        default: '',
    },
    placeholder: {
        type: String,
        default: 'Pilih tanggal',
    },
})

const emit = defineEmits(['update:modelValue'])

const selectedDate = ref(null)
const timeValue = ref('')
const open = ref(false)

watch(
    () => props.modelValue,
    value => {
        if (!value) {
            selectedDate.value = null
            timeValue.value = ''
            return
        }

        const [datePart, timePart] = value.split(/[T ]/)
        selectedDate.value = parseDate(datePart)
        timeValue.value = (timePart || '').substring(0, 5)
    },
    { immediate: true },
)

watch([selectedDate, timeValue], () => emitValue())

const dateLabel = computed(() => {
    if (!selectedDate.value) return props.placeholder

    const jsDate = selectedDate.value.toDate(getLocalTimeZone())
    return jsDate.toLocaleDateString('id-ID', {
        weekday: 'short',
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    })
})

function emitValue() {
    if (!selectedDate.value) {
        emit('update:modelValue', '')
        return
    }

    const base = selectedDate.value.toDate(getLocalTimeZone())
    const [hours, minutes] = (timeValue.value || '00:00').split(':')
    base.setHours(Number(hours) || 0, Number(minutes) || 0, 0, 0)

    const year = base.getFullYear()
    const month = String(base.getMonth() + 1).padStart(2, '0')
    const day = String(base.getDate()).padStart(2, '0')
    const hour = String(base.getHours()).padStart(2, '0')
    const minute = String(base.getMinutes()).padStart(2, '0')

    emit('update:modelValue', `${year}-${month}-${day} ${hour}:${minute}`)
}

function clearValue() {
    selectedDate.value = null
    timeValue.value = ''
}

function onDateSelect(value) {
    selectedDate.value = value
    open.value = false
}
</script>

<template>
    <div class="flex flex-col gap-4">
        <div class="flex flex-col gap-4 md:flex-row md:items-end">
            <div class="flex-1 space-y-2">
                <label class="text-sm font-medium text-foreground">Tanggal</label>
                <PopoverRoot v-model:open="open">
                    <PopoverTrigger as-child>
                        <Button variant="outline"
                            class="w-full justify-between rounded-lg border bg-background px-3 font-normal">
                            <span class="flex min-w-0 items-center gap-2 text-left">
                                <CalendarIcon class="h-4 w-4 shrink-0" />
                                <span class="truncate">{{ dateLabel }}</span>
                            </span>
                            <span class="flex items-center gap-2 text-muted-foreground">
                                <X v-if="selectedDate" class="h-4 w-4" @click.stop="clearValue" />
                                <ChevronDownIcon class="h-4 w-4" />
                            </span>
                        </Button>
                    </PopoverTrigger>

                    <PopoverContent align="start" class="w-auto p-0">
                        <CalendarRoot v-model="selectedDate" weekday-format="short" locale="id" fixed-weeks
                            v-slot="{ weekDays = [], grid = [] }">
                            <div class="p-3">
                                <div class="flex items-center justify-between pb-3">
                                    <CalendarPrev class="h-8 w-8 rounded-md border" />
                                    <CalendarHeader class="text-sm font-medium">
                                        <CalendarHeading />
                                    </CalendarHeader>
                                    <CalendarNext class="h-8 w-8 rounded-md border" />
                                </div>

                                <div class="space-y-1">
                                    <CalendarGrid v-for="month in grid || []" :key="month.value?.toString() || ''"
                                        class="space-y-1">
                                        <CalendarGridHead>
                                            <CalendarGridRow class="grid grid-cols-7 text-center text-xs text-muted-foreground">
                                                <CalendarHeadCell v-for="day in weekDays" :key="day"
                                                    class="rounded-md py-1 font-medium">
                                                    {{ day }}
                                                </CalendarHeadCell>
                                            </CalendarGridRow>
                                        </CalendarGridHead>

                                        <CalendarGridBody>
                                            <CalendarGridRow v-for="(week, index) in month.weeks" :key="index"
                                                class="grid grid-cols-7">
                                                <CalendarCell v-for="day in week" :key="day.value?.toString() || ''" :date="day.value">
                                                    <CalendarCellTrigger :day="day"
                                                        class="flex h-9 w-9 items-center justify-center rounded-md text-sm font-normal hover:bg-accent hover:text-accent-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 data-[disabled]:pointer-events-none data-[disabled]:opacity-40 data-[selected]:bg-primary data-[selected]:text-primary-foreground"
                                                        @click="onDateSelect(day.value)">
                                                        {{ day.day }}
                                                    </CalendarCellTrigger>
                                                </CalendarCell>
                                            </CalendarGridRow>
                                        </CalendarGridBody>
                                    </CalendarGrid>
                                </div>
                            </div>
                        </CalendarRoot>
                    </PopoverContent>
                </PopoverRoot>
            </div>

            <div class="w-full space-y-2 md:w-40">
                <label class="text-sm font-medium text-foreground">Waktu (24 jam)</label>
                <Input v-model="timeValue" type="time" step="60"
                    class="h-10 rounded-lg border bg-background" />
            </div>
        </div>

        <p class="text-xs text-muted-foreground">Atur tanggal dan waktu dalam format 24 jam untuk penjadwalan yang lebih akurat.</p>
    </div>
</template>
