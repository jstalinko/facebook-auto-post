<template>

    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4">
            <!-- LOGIN BUTTON -->
            <div v-if="!facebookConnected">
                <Button as-child class="w-full">
                    <a href="/auth/facebook">
                        Login with Facebook
                    </a>
                </Button>
            </div>

            <div v-else class="space-y-6">
                <div class="grid gap-4 xl:grid-cols-3">
                    <Card class="xl:col-span-2">
                        <CardHeader class="flex flex-wrap items-center justify-between gap-2">
                            <div>
                                <CardTitle>Akun Facebook</CardTitle>
                                <p class="text-sm text-muted-foreground">Kelola koneksi dan sinkronisasi fanspage.</p>
                            </div>

                            <Button as-child variant="secondary">
                                <a href="/auth/facebook">Tambah Akun Facebook</a>
                            </Button>
                        </CardHeader>
                        <CardContent>
                            <div class="grid gap-4 md:grid-cols-2">
                                <div class="space-y-2">
                                    <label class="text-sm font-medium">Gunakan akun</label>
                                    <Select v-model="selectedFacebookUserId">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Pilih akun facebook" />
                                        </SelectTrigger>

                                        <SelectContent>
                                            <SelectItem v-for="fb in facebookUsers" :key="fb.id" :value="fb.id">
                                                {{ fb.name || 'Facebook User' }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>

                                <div class="space-y-2">
                                    <label class="text-sm font-medium">Tindakan</label>
                                    <div class="flex flex-wrap gap-3">
                                        <Button variant="outline" @click="syncPages" :disabled="!selectedFacebookUserId">
                                            Sync Pages
                                        </Button>
                                        <Button variant="outline" @click="selectedPageIds = []">
                                            Bersihkan Pilihan
                                        </Button>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- PAGE SECTION -->
                <Card>
                    <CardHeader class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                        <CardTitle>Facebook Pages</CardTitle>

                        <div class="flex flex-wrap gap-3">
                            <Button variant="outline" @click="syncPages" :disabled="!selectedFacebookUserId">
                                Sync Pages
                            </Button>
                            <Button variant="outline" @click="selectedPageIds = []">
                                Reset Pilihan
                            </Button>
                        </div>
                    </CardHeader>

                    <CardContent>
                        <div v-if="facebookPages.length" class="overflow-x-auto">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead class="w-12 text-center">Pilih</TableHead>
                                        <TableHead>Page Name</TableHead>
                                        <TableHead>Page ID</TableHead>
                                        <TableHead>Akun FB</TableHead>
                                        <TableHead class="text-right">Action</TableHead>
                                    </TableRow>
                                </TableHeader>

                                <TableBody>
                                    <TableRow v-for="p in facebookPages" :key="p.id">
                                        <TableCell class="text-center">
                                            <input v-model="selectedPageIds" :value="p.page_id" type="checkbox"
                                                class="h-4 w-4 rounded border" />
                                        </TableCell>
                                        <TableCell class="font-medium">
                                            {{ p.page_name }}
                                        </TableCell>

                                        <TableCell class="text-muted-foreground">
                                            {{ p.page_id }}
                                        </TableCell>

                                        <TableCell class="text-muted-foreground">
                                            {{ p.facebook_user?.name || 'Akun' }}
                                        </TableCell>

                                        <TableCell class="text-right">
                                            <Button size="sm" @click="openCreatePost(p)">
                                                Create Post
                                            </Button>
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </div>

                        <div v-else class="py-6 text-center text-sm text-muted-foreground">
                            Belum ada fanspage. Klik <strong>Sync Pages</strong> untuk mengambil data.
                        </div>
                    </CardContent>
                </Card>

                <!-- BULK POST -->
                <Card>
                    <CardHeader>
                        <CardTitle>Bulk Post</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <label class="text-sm font-medium">Post Type</label>
                                <Select v-model="bulkPostType">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Pilih tipe post" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="text">Text</SelectItem>
                                        <SelectItem value="photo">Photo</SelectItem>
                                        <SelectItem value="video">Video</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div class="space-y-2">
                                <label class="text-sm font-medium">Schedule (opsional)</label>
                                <DateTimePicker v-model="bulkScheduledAt" placeholder="Pilih tanggal & jam" />
                            </div>
                        </div>

                        <div class="mt-4 space-y-2">
                            <label class="text-sm font-medium">Pesan</label>
                            <Textarea v-model="bulkPostMessage" placeholder="Tulis konten postingan..." rows="4" />
                        </div>

                        <div class="mt-4" v-if="bulkPostType !== 'text'">
                            <input type="file" class="block w-full text-sm" @change="handleBulkFileChange" />
                        </div>

                        <div class="mt-4 flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                            <p class="text-sm text-muted-foreground">Pilih minimal satu fanspage untuk bulk post.</p>
                            <Button :disabled="!canSubmitBulk" @click="submitBulkPost">Kirim</Button>
                        </div>
                    </CardContent>
                </Card>

                <!-- CREATE POST MODAL -->
                <Dialog v-model:open="showPostModal">
                    <DialogContent class="sm:max-w-lg">
                        <DialogHeader>
                            <DialogTitle>Create Post</DialogTitle>
                            <p class="text-sm text-muted-foreground">
                                {{ selectedPage?.page_name }}
                            </p>
                        </DialogHeader>

                        <div class="space-y-4">
                            <div class="space-y-2">
                                <label class="block text-sm font-medium">
                                    Post Type
                                </label>

                                <Select v-model="postType">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Pilih tipe post" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="text">Text</SelectItem>
                                        <SelectItem value="photo">Photo</SelectItem>
                                        <SelectItem value="video">Video</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <Textarea v-if="postType === 'text'" v-model="postMessage"
                                placeholder="Tulis konten postingan..." rows="5" />

                            <input v-if="postType !== 'text'" type="file" class="block w-full text-sm"
                                @change="handleFileChange" />

                            <div class="space-y-2">
                                <label class="block text-sm font-medium">Schedule (opsional)</label>
                                <DateTimePicker v-model="scheduledAt" placeholder="Pilih tanggal & jam" />
                            </div>
                        </div>

                        <DialogFooter>
                            <Button variant="secondary" @click="showPostModal = false">
                                Cancel
                            </Button>

                            <Button :disabled="!canSubmit" @click="submitPost">
                                Post
                            </Button>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>
            </div>
        </div>
    </AppLayout>
</template>
<script setup>
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

import Button from '@/components/ui/button/Button.vue'
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table'

import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card'

import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogFooter,
} from '@/components/ui/dialog'
import DateTimePicker from '@/components/DateTimePicker.vue'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'

import Textarea from '@/components/ui/textarea/Textarea.vue'
import StatusTable from '@/components/StatusTable.vue'
import { dashboard } from '@/routes'

/**
 * @typedef {Object} FacebookPage
 * @property {number} id
 * @property {string} page_id
 * @property {string} page_name
 */

const page = usePage()

const facebookUsers = computed(() => page.props.facebookUsers || [])
const facebookPages = computed(() => page.props.facebookPages || [])
const facebookConnected = computed(() => facebookUsers.value.length > 0)

const selectedFacebookUserId = ref(facebookUsers.value[0]?.id || null)

const showPostModal = ref(false)
const selectedPage = ref(null)

const postType = ref('text')
const postMessage = ref('')
const mediaFile = ref(null)
const scheduledAt = ref('')

const selectedPageIds = ref([])

const bulkPostType = ref('text')
const bulkPostMessage = ref('')
const bulkMediaFile = ref(null)
const bulkScheduledAt = ref('')

function openCreatePost(p) {
    selectedPage.value = p
    postType.value = 'text'
    postMessage.value = ''
    mediaFile.value = null
    scheduledAt.value = ''
    showPostModal.value = true
}

function handleFileChange(e) {
    mediaFile.value = e.target.files[0] ?? null
}

const canSubmit = computed(() => {
    if (postType.value === 'text') {
        return postMessage.value.trim().length > 0
    }

    return mediaFile.value !== null
})

function submitPost() {
    if (!selectedPage.value) return

    const form = new FormData()
    form.append('page_id', selectedPage.value.page_id)
    form.append('type', postType.value)

    if (postType.value === 'text') {
        form.append('message', postMessage.value)
    } else {
        form.append('media', mediaFile.value)
        form.append('message', postMessage.value)
    }

    if (scheduledAt.value) {
        form.append('scheduled_at', scheduledAt.value)
    }

    router.post('/facebook/post', form, {
        preserveScroll: true,
        onSuccess: () => {
            showPostModal.value = false
            scheduledAt.value = ''
        },
    })
}

function syncPages() {
    if (!selectedFacebookUserId.value) return

    router.post('/facebook/get-pages', {
        facebook_user_id: selectedFacebookUserId.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            bulkMediaFile.value = null
            bulkPostMessage.value = ''
            bulkScheduledAt.value = ''
            bulkPostType.value = 'text'
            selectedPageIds.value = []
        },
    })
}

const canSubmitBulk = computed(() => {
    if (!selectedPageIds.value.length) return false
    if (bulkPostType.value === 'text') {
        return bulkPostMessage.value.trim().length > 0
    }

    return bulkMediaFile.value !== null
})

function handleBulkFileChange(e) {
    bulkMediaFile.value = e.target.files[0] ?? null
}

function submitBulkPost() {
    if (!selectedPageIds.value.length) return

    const form = new FormData()
    selectedPageIds.value.forEach(id => form.append('page_ids[]', id))
    form.append('type', bulkPostType.value)
    form.append('message', bulkPostMessage.value)

    if (bulkMediaFile.value) {
        form.append('media', bulkMediaFile.value)
    }

    if (bulkScheduledAt.value) {
        form.append('scheduled_at', bulkScheduledAt.value)
    }

    router.post('/facebook/bulk-post', form, {
        preserveScroll: true,
        onSuccess: () => {
            bulkMediaFile.value = null
            bulkPostMessage.value = ''
            bulkScheduledAt.value = ''
            bulkPostType.value = 'text'
            selectedPageIds.value = []
        },
    })
}

const canSubmitBulk = computed(() => {
    if (!selectedPageIds.value.length) return false
    if (bulkPostType.value === 'text') {
        return bulkPostMessage.value.trim().length > 0
    }

    return bulkMediaFile.value !== null
})

function handleBulkFileChange(e) {
    bulkMediaFile.value = e.target.files[0] ?? null
}

function submitBulkPost() {
    if (!selectedPageIds.value.length) return

    const form = new FormData()
    selectedPageIds.value.forEach(id => form.append('page_ids[]', id))
    form.append('type', bulkPostType.value)
    form.append('message', bulkPostMessage.value)

    if (bulkMediaFile.value) {
        form.append('media', bulkMediaFile.value)
    }

    if (bulkScheduledAt.value) {
        form.append('scheduled_at', bulkScheduledAt.value)
    }

    router.post('/facebook/bulk-post', form, {
        preserveScroll: true,
        onSuccess: () => {
            bulkMediaFile.value = null
            bulkPostMessage.value = ''
            bulkScheduledAt.value = ''
            bulkPostType.value = 'text'
            selectedPageIds.value = []
        },
    })
}

const breadcrumbs = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
]
</script>
