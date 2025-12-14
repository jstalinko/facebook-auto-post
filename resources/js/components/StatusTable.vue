<template>
    <div>
        <div v-if="items.length" class="overflow-x-auto">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>Page</TableHead>
                        <TableHead>Jenis</TableHead>
                        <TableHead>Status</TableHead>
                        <TableHead>Waktu</TableHead>
                        <TableHead class="text-right">Pesan</TableHead>
                    </TableRow>
                </TableHeader>

                <TableBody>
                    <TableRow v-for="item in items" :key="item.id">
                        <TableCell>
                            <div class="font-medium">{{ item.facebook_page?.page_name || 'Page' }}</div>
                            <p class="text-xs text-muted-foreground">{{ item.facebook_page?.page_id }}</p>
                        </TableCell>
                        <TableCell class="capitalize">{{ item.type }}</TableCell>
                        <TableCell>
                            <span class="rounded-full bg-secondary px-2 py-1 text-xs font-medium">
                                {{ item.status }}
                            </span>
                        </TableCell>
                        <TableCell class="text-sm text-muted-foreground">
                            {{ formatDate(item.scheduled_at || item.created_at) }}
                        </TableCell>
                        <TableCell class="text-right">
                            <p class="text-sm" :class="{ 'text-destructive': item.status === 'failed' }">
                                {{ item.message || item.error_message || '-' }}
                            </p>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>

        <p v-else class="py-4 text-center text-sm text-muted-foreground">{{ emptyText }}</p>
    </div>
</template>

<script setup>
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table'

defineProps({
    items: {
        type: Array,
        default: () => [],
    },
    emptyText: {
        type: String,
        default: 'Tidak ada data',
    },
})

function formatDate(value) {
    if (!value) return '-'
    return new Date(value).toLocaleString()
}
</script>
