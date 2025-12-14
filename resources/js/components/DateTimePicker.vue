<script setup>
import { computed, ref, watch } from 'vue'
import { CalendarIcon, X } from 'lucide-vue-next'
import {
    DatePickerCalendar,
    DatePickerCell,
    DatePickerCellTrigger,
    DatePickerContent,
    DatePickerGrid,
    DatePickerGridBody,
    DatePickerGridHead,
    DatePickerGridRow,
    DatePickerHeadCell,
    DatePickerHeader,
    DatePickerHeading,
    DatePickerNext,
    DatePickerPrev,
    DatePickerRoot,
    DatePickerTrigger,
} from 'reka-ui'
import { getLocalTimeZone, parseDate } from '@internationalized/date'

import Button from '@/components/ui/button/Button.vue'
import Input from '@/components/ui/input/Input.vue'

const props = defineProps({
    modelValue: {
        type: String,
        default: '',
    },
    placeholder: {
        type: String,
        default: 'Pilih jadwal',
    },
})

const emit = defineEmits(['update:modelValue'])

const selectedDate = ref(null)
const timeValue = ref('')

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

const formattedLabel = computed(() => {
    if (!selectedDate.value) return props.placeholder

    const jsDate = selectedDate.value.toDate(getLocalTimeZone())
    const [hours, minutes] = (timeValue.value || '00:00').split(':')
    jsDate.setHours(Number(hours) || 0, Number(minutes) || 0, 0, 0)

    return new Intl.DateTimeFormat('id-ID', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(jsDate)
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

    emit('update:modelValue', `${year}-${month}-${day}T${hour}:${minute}`)
}

function clearValue() {
    selectedDate.value = null
    timeValue.value = ''
}
</script>

<template>
    <DatePickerRoot v-model="selectedDate" granularity="day">
        <DatePickerTrigger as-child>
            <Button variant="outline" class="w-full justify-start gap-2 text-left font-normal">
                <CalendarIcon class="h-4 w-4" />
                <span>
                    {{ formattedLabel }}
                </span>

                <X v-if="selectedDate" class="ml-auto h-4 w-4 opacity-60" @click.stop="clearValue" />
            </Button>
        </DatePickerTrigger>

        <DatePickerContent class="w-auto p-3">
            <DatePickerCalendar v-slot="{ weekDays, grid }">
                <div class="flex items-center justify-between px-2 pb-4">
                    <DatePickerPrev class="h-8 w-8 rounded-md border" />
                    <DatePickerHeader class="text-sm font-medium">
                        <DatePickerHeading />
                    </DatePickerHeader>
                    <DatePickerNext class="h-8 w-8 rounded-md border" />
                </div>

                <div class="space-y-3">
                    <DatePickerGrid v-for="month in grid || []" :key="month.value?.toString() || ''" class="space-y-1">
                        <DatePickerGridHead>
                            <DatePickerGridRow class="grid grid-cols-7">
                                <DatePickerHeadCell v-for="day in weekDays" :key="day"
                                    class="text-muted-foreground rounded-md w-9 text-center text-[0.8rem] font-normal">
                                    {{ day }}
                                </DatePickerHeadCell>
                            </DatePickerGridRow>
                        </DatePickerGridHead>

                        <DatePickerGridBody>
                            <DatePickerGridRow v-for="(week, weekIndex) in month.weeks || []" :key="weekIndex"
                                class="grid grid-cols-7">
                                <DatePickerCell v-for="day in week" :key="day.value?.toString() || ''" :date="day.value">
                                    <DatePickerCellTrigger :day="day"
                                        class="flex h-9 w-9 items-center justify-center rounded-md text-sm font-normal hover:bg-accent hover:text-accent-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 data-[disabled]:pointer-events-none data-[disabled]:opacity-50 data-[selected]:bg-primary data-[selected]:text-primary-foreground">
                                        {{ day.day }}
                                    </DatePickerCellTrigger>
                                </DatePickerCell>
                            </DatePickerGridRow>
                        </DatePickerGridBody>
                    </DatePickerGrid>
                </div>
            </DatePickerCalendar>

            <div class="mt-3 space-y-1">
                <p class="text-xs font-medium text-muted-foreground">Jam (24 jam)</p>
                <Input v-model="timeValue" type="time" step="60" class="h-10" />
            </div>
        </DatePickerContent>
    </DatePickerRoot>
</template>
