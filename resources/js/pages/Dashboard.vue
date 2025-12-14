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

            <!-- PAGE SECTION -->
            <div v-else>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between">
                        <CardTitle>Facebook Pages</CardTitle>

                        <Button variant="outline" @click="syncPages">
                            Sync Pages
                        </Button>
                    </CardHeader>

                    <CardContent>
                        <div v-if="facebookPages.length">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Page Name</TableHead>
                                        <TableHead>Page ID</TableHead>
                                        <TableHead class="text-right">Action</TableHead>
                                    </TableRow>
                                </TableHeader>

                                <TableBody>
                                    <TableRow v-for="p in facebookPages" :key="p.id">
                                        <TableCell class="font-medium">
                                            {{ p.page_name }}
                                        </TableCell>

                                        <TableCell class="text-muted-foreground">
                                            {{ p.page_id }}
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

                            <!-- POST TYPE -->
                            <div>
                                <label class="mb-1 block text-sm font-medium">
                                    Post Type
                                </label>

                                <select v-model="postType" class="w-full rounded-md border px-3 py-2 text-sm">
                                    <option value="text">Text</option>
                                    <option value="photo">Photo</option>
                                    <option value="video">Video</option>
                                </select>
                            </div>

                            <!-- TEXT -->
                            <Textarea v-if="postType === 'text'" v-model="postMessage"
                                placeholder="Tulis konten postingan..." rows="5" />

                            <!-- MEDIA -->
                            <input v-if="postType !== 'text'" type="file" class="block w-full text-sm"
                                @change="handleFileChange" />
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

import Textarea from '@/components/ui/textarea/Textarea.vue'
import { dashboard } from '@/routes'

/**
 * @typedef {Object} FacebookPage
 * @property {number} id
 * @property {string} page_id
 * @property {string} page_name
 */

const page = usePage()

const facebookConnected = page.props.facebookConnected
/** @type {FacebookPage[]} */
const facebookPages = page.props.facebookPages

const showPostModal = ref(false)
/** @type {import('vue').Ref<FacebookPage|null>} */
const selectedPage = ref(null)

const postType = ref('text')
const postMessage = ref('')
const mediaFile = ref(null)

function openCreatePost(p) {
    selectedPage.value = p
    postType.value = 'text'
    postMessage.value = ''
    mediaFile.value = null
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

    router.post('/facebook/post', form, {
        preserveScroll: true,
        onSuccess: () => {
            showPostModal.value = false
        },
    })
}

function syncPages() {
    router.post('/facebook/get-pages', {
        preserveScroll: true,
    })
}

const breadcrumbs = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
]
</script>
