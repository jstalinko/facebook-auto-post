<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import Button from '@/components/ui/button/Button.vue'
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table'
import { dashboard } from '@/routes'
import type { BreadcrumbItem } from '@/types'
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
import { ref } from 'vue'
const showPostModal = ref(false)
const selectedPage = ref<null | {
    id: number
    page_id: string
    page_name: string
}>(null)

const postMessage = ref('')

function openCreatePost(page: typeof selectedPage.value) {
    selectedPage.value = page
    postMessage.value = ''
    showPostModal.value = true
}

function submitPost() {
    if (!selectedPage.value) return

    router.post(
        '/facebook/post',
        {
            page_id: selectedPage.value.page_id,
            message: postMessage.value,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                showPostModal.value = false
            },
        }
    )
}


const page = usePage()

const facebookConnected = page.props.facebookConnected as boolean
const facebookPages = page.props.facebookPages as {
    id: number
    page_id: string
    page_name: string
}[]

function syncPages() {
    router.post('/facebook/get-pages', {
        preserveScroll: true,
    })
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
]
</script>


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
                                    <TableRow v-for="page in facebookPages" :key="page.id">
                                        <TableCell class="font-medium">
                                            {{ page.page_name }}
                                        </TableCell>

                                        <TableCell class="text-muted-foreground">
                                            {{ page.page_id }}
                                        </TableCell>

                                        <TableCell class="text-right">
                                            <Button size="sm" @click="openCreatePost(page)">
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
                <Dialog v-model:open="showPostModal">
                    <DialogContent class="sm:max-w-lg">
                        <DialogHeader>
                            <DialogTitle>
                                Create Post
                            </DialogTitle>
                            <p class="text-sm text-muted-foreground">
                                {{ selectedPage?.page_name }}
                            </p>
                        </DialogHeader>

                        <div class="space-y-4">
                            <Textarea v-model="postMessage" placeholder="Tulis konten postingan..." rows="5" />
                        </div>

                        <DialogFooter>
                            <Button variant="secondary" @click="showPostModal = false">
                                Cancel
                            </Button>

                            <Button :disabled="!postMessage.trim()" @click="submitPost">
                                Post
                            </Button>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>


            </div>


        </div>
    </AppLayout>
</template>
