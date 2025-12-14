<script setup>
import { computed, ref, watch } from 'vue'

const props = defineProps({
    modelValue: {
        type: String,
        default: '',
    },
})

const emit = defineEmits(['update:modelValue'])

const day = ref('')
const month = ref('')
const year = ref('')
const hour = ref('')
const minute = ref('')

const months = [
    { value: '01', label: 'Januari' },
    { value: '02', label: 'Februari' },
    { value: '03', label: 'Maret' },
    { value: '04', label: 'April' },
    { value: '05', label: 'Mei' },
    { value: '06', label: 'Juni' },
    { value: '07', label: 'Juli' },
    { value: '08', label: 'Agustus' },
    { value: '09', label: 'September' },
    { value: '10', label: 'Oktober' },
    { value: '11', label: 'November' },
    { value: '12', label: 'Desember' },
]

const years = ['2025', '2026']

const hours = Array.from({ length: 24 }, (_, i) => String(i).padStart(2, '0'))
const minutes = Array.from({ length: 60 }, (_, i) => String(i).padStart(2, '0'))

const daysInMonth = computed(() => {
    if (!month.value || !year.value) return 31
    return new Date(Number(year.value), Number(month.value), 0).getDate()
})

const days = computed(() => {
    return Array.from({ length: daysInMonth.value }, (_, i) => String(i + 1).padStart(2, '0'))
})

watch([month, year], () => {
    if (day.value && Number(day.value) > daysInMonth.value) {
        day.value = String(daysInMonth.value).padStart(2, '0')
    }
})

watch(
    () => props.modelValue,
    value => {
        if (!value) {
            day.value = ''
            month.value = ''
            year.value = ''
            hour.value = ''
            minute.value = ''
            return
        }

        const [datePart, timePart] = value.split('T')
        const [y, m, d] = datePart.split('-')
        const [h, min] = (timePart || '00:00').split(':')

        year.value = y
        month.value = m
        day.value = d
        hour.value = h
        minute.value = min
    },
    { immediate: true },
)

watch([day, month, year, hour, minute], () => {
    if (day.value && month.value && year.value && hour.value && minute.value) {
        emit('update:modelValue', `${year.value}-${month.value}-${day.value}T${hour.value}:${minute.value}`)
    } else {
        emit('update:modelValue', '')
    }
})
</script>

<template>
    <div class="flex items-center gap-2">
        <select v-model="day"
            class="h-9 w-20 rounded-md border border-input bg-background px-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring">
            <option value="">Tanggal</option>
            <option v-for="d in days" :key="d" :value="d">{{ d }}</option>
        </select>

        <select v-model="month"
            class="h-9 flex-1 rounded-md border border-input bg-background px-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring">
            <option value="">Bulan</option>
            <option v-for="m in months" :key="m.value" :value="m.value">{{ m.label }}</option>
        </select>

        <select v-model="year"
            class="h-9 w-20 rounded-md border border-input bg-background px-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring">
            <option value="">Tahun</option>
            <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
        </select>

        <span class="text-muted-foreground">/</span>

        <select v-model="hour"
            class="h-9 w-16 rounded-md border border-input bg-background px-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring">
            <option value="">Jam</option>
            <option v-for="h in hours" :key="h" :value="h">{{ h }}</option>
        </select>

        <span class="text-muted-foreground">:</span>

        <select v-model="minute"
            class="h-9 w-16 rounded-md border border-input bg-background px-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring">
            <option value="">Menit</option>
            <option v-for="m in minutes" :key="m" :value="m">{{ m }}</option>
        </select>
    </div>
</template>