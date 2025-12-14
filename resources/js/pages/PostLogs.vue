<template>
    <Head title="Manage Posts" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold">Manage Postingan</h1>
                    <p class="text-sm text-muted-foreground">Pantau antrian, jadwal, dan hasil postingan Facebook.</p>
                </div>

                <div class="flex items-center gap-2">
                    <label class="text-sm font-medium">Filter Status</label>
                    <select v-model="statusFilter" class="rounded-md border px-3 py-2 text-sm shadow-sm">
                        <option value="all">Semua</option>
                        <option value="queued">Post Antrian</option>
                        <option value="scheduled">Post Terjadwal</option>
                        <option value="completed">Post Selesai</option>
                        <option value="failed">Post Gagal</option>
                    </select>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <Card v-for="stat in stats" :key="stat.label">
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm text-muted-foreground">{{ stat.label }}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p class="text-2xl font-semibold">{{ stat.count }}</p>
                    </CardContent>
                </Card>
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <Card v-for="section in visibleSections" :key="section.title">
                    <CardHeader>
                        <CardTitle>{{ section.title }}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <StatusTable :items="section.items" :empty-text="section.emptyText" />
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/layouts/AppLayout.vue'
import StatusTable from '@/components/StatusTable.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Head, router, usePage } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { dashboard } from '@/routes'

const page = usePage()

const queuedLogs = computed(() => page.props.queuedLogs || [])
const scheduledLogs = computed(() => page.props.scheduledLogs || [])
const completedLogs = computed(() => page.props.completedLogs || [])
const failedLogs = computed(() => page.props.failedLogs || [])

const initialFilter = page.props.statusFilter || null
const statusFilter = ref(initialFilter || 'all')

const sections = computed(() => ([
    {
        key: 'queued',
        title: 'Post Antrian',
        items: queuedLogs.value,
        emptyText: 'Belum ada antrian.',
    },
    {
        key: 'scheduled',
        title: 'Post Terjadwal',
        items: scheduledLogs.value,
        emptyText: 'Belum ada jadwal.',
    },
    {
        key: 'completed',
        title: 'Post Selesai',
        items: completedLogs.value,
        emptyText: 'Belum ada post selesai.',
    },
    {
        key: 'failed',
        title: 'Post Gagal',
        items: failedLogs.value,
        emptyText: 'Tidak ada post gagal.',
    },
]))

const visibleSections = computed(() => {
    if (statusFilter.value === 'all') return sections.value
    return sections.value.filter(section => section.key === statusFilter.value)
})

const stats = computed(() => ([
    { label: 'Post Antrian', count: queuedLogs.value.length },
    { label: 'Post Terjadwal', count: scheduledLogs.value.length },
    { label: 'Post Selesai', count: completedLogs.value.length },
    { label: 'Post Gagal', count: failedLogs.value.length },
]))

watch(statusFilter, (value) => {
    const params = value === 'all' ? {} : { status: value }
    router.get('/posts', params, {
        preserveScroll: true,
        preserveState: true,
        replace: true,
    })
})

const breadcrumbs = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Manage Post',
        href: '/posts',
    },
]
</script>
