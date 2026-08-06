<template>

    <main>

        <div>

            <NewsArchivedDetailView

                v-if="detailItem"

                :item="detailItem"

                :busy="actionBusyId === detailItem.id"

                :image-url="imageUrl"

                :format-date="formatDate"

                @back="closeDetail"
                @restore="restoreItem(detailItem)"

                @edit="editItem(detailItem)"

                @delete="deleteItem(detailItem)"

            />



            <template v-else>

            <div v-if="loading" class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="n in 6"
                    :key="`skeleton-${n}`"
                    class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm"
                    aria-hidden="true"
                >
                    <div class="h-40 animate-pulse bg-slate-200" />
                    <div class="space-y-3 p-4">
                        <div class="h-4 w-3/4 animate-pulse rounded-md bg-slate-200" />
                        <div class="h-3 w-full animate-pulse rounded bg-slate-100" />
                        <div class="h-3 w-full animate-pulse rounded bg-slate-100" />
                        <div class="h-3 w-2/3 animate-pulse rounded bg-slate-100" />
                        <div class="flex items-center justify-between pt-2">
                            <div class="h-3 w-24 animate-pulse rounded bg-slate-100" />
                            <div class="flex gap-2">
                                <div class="h-7 w-14 animate-pulse rounded-lg bg-slate-200" />
                                <div class="h-7 w-7 animate-pulse rounded-lg bg-slate-100" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div
                v-else-if="!archived.length"
                class="admin-table-empty rounded-xl border border-gray-200 py-14 text-center"
            >
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100">
                    <Archive class="h-8 w-8 text-slate-400" />
                </div>
                <p class="text-base font-semibold text-slate-700">{{ emptyTitle }}</p>
                <p class="mt-1 text-sm text-slate-400">{{ emptySubtitle }}</p>
            </div>

            <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">

                <div v-for="item in archived" :key="item.id" class="rounded-2xl border border-gray-200 bg-white shadow-sm flex flex-col overflow-visible">

                    <div class="relative overflow-hidden rounded-t-2xl">

                        <img v-if="item.featured_image" :src="imageUrl(item.featured_image)" alt="image" class="h-40 w-full object-cover" />

                        <div v-else class="h-40 w-full bg-slate-100 flex items-center justify-center text-sm text-slate-500">No Image</div>



                        <div class="absolute left-3 top-3 rounded-md bg-black/60 px-3 py-1 text-xs font-medium text-white">

                            Archived on {{ formatDate(item.created_at || item.published_at) }}

                        </div>

                    </div>



                    <div class="p-4 flex-1 flex flex-col">

                        <h3 class="text-sm font-semibold text-slate-900 mb-2" :title="item.title">{{ truncateText(item.title, 70) }}</h3>



                        <p class="text-xs text-slate-600 mb-4 break-words" style="display:-webkit-box; -webkit-line-clamp:3; -webkit-box-orient:vertical; overflow:hidden;">{{ stripHtml(item.content) }}</p>



                        <div class="flex items-center justify-between mt-auto">

                            <div class="flex items-center gap-3 text-xs text-slate-500">

                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">

                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />

                                </svg>

                                <span>{{ formatDate(item.published_at) }}</span>

                            </div>



                            <div class="flex items-center gap-2">

                                <button @click="openView(item)" class="inline-flex items-center gap-2 rounded-lg border border-green-500 px-3 py-1 text-xs font-semibold text-green-600 hover:bg-green-50">View</button>



                                <NewsArchivedCardActions

                                    :busy="actionBusyId === item.id"

                                    @view="openView(item)"

                                    @restore="restoreItem(item)"

                                    @edit="editItem(item)"

                                    @delete="deleteItem(item)"

                                />

                            </div>

                        </div>

                    </div>

                </div>

            </div>



            <div v-if="!loading" class="mt-6">

            <TableFooter

                v-if="pagination"

                :from="pagination.from"

                :to="pagination.to"

                :total="pagination.total"

                :current-page="pagination.current_page"

                :last-page="pagination.last_page"

                label="archived news"

            />



            <div class="mt-4">

                <Pagination

                    v-if="pagination"

                    :pagination="pagination"

                    :navigate="false"

                    @page-changed="fetchData"

                />

            </div>

            </div>

            </template>

            <ArchiveNewsUpdateModal
                v-model="editModalOpen"
                :item="editingItem"
                @updated="onArchivedUpdated"
            />

        </div>

    </main>

</template>



<script setup>

import { ref, watch, onMounted, computed } from 'vue'

import Swal from 'sweetalert2'
import { Archive } from '@lucide/vue'

import Pagination from '@/Components/Pagination.vue'

import TableFooter from '@/Components/Admin/TableFooter.vue'

import NewsArchivedCardActions from '@/Components/sections/archived/NewsArchivedCardActions.vue'

import NewsArchivedDetailView from '@/Components/sections/archived/NewsArchivedDetailView.vue'
import ArchiveNewsUpdateModal from '@/Modals/ArchiveNewsUpdateModal.vue'
import { csrfHeaders } from '@/utils/csrf'



const props = defineProps({

    search: { type: String, default: '' },

    filter: { type: String, default: '' }

})

const hasActiveFilters = computed(() => Boolean(props.search?.trim() || props.filter?.trim()))

const emptyTitle = computed(() =>
    hasActiveFilters.value ? 'No archived news found' : 'No archived news yet'
)

const emptySubtitle = computed(() => {
    if (props.search?.trim() && props.filter?.trim()) {
        return 'Try adjusting your search or date filter.'
    }
    if (props.search?.trim()) {
        return 'No articles match your search. Try a different keyword.'
    }
    if (props.filter?.trim()) {
        return 'No articles match this date range. Try another filter.'
    }
    return 'Archived articles will appear here when you archive news from the News page.'
})



const detailOpen = defineModel('detailOpen', { type: Boolean, default: false })



const archived = ref([])

const pagination = ref(null)

const loading = ref(false)

const detailItem = ref(null)

const actionBusyId = ref(null)

const editModalOpen = ref(false)

const editingItem = ref(null)



let fetchTimeout = null

function imageUrl(path) {

    if (!path) return ''

    const file = path.split('/').pop()

    return `/archive_news/${file}`

}



function stripHtml(value) {

    if (!value) return ''

    return String(value).replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim()

}



function truncateText(text, max = 100) {

    if (!text) return ''

    return text.length > max ? text.slice(0, max) + '...' : text

}



function formatDate(dateString) {

    if (!dateString) return ''

    const d = new Date(dateString)

    return d.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })

}



async function fetchData(page = 1) {

    loading.value = true

    const params = new URLSearchParams()

    if (props.search) params.append('search', props.search)

    if (props.filter) params.append('filter', props.filter)

    params.append('page', page)



    try {

        const res = await fetch(`/archive-news?${params.toString()}`, { headers: { Accept: 'application/json' } })

        const json = await res.json()

        archived.value = json.data || []

        pagination.value = json.pagination || null

    } catch (e) {

        console.error(e)

    } finally {

        loading.value = false

    }

}



function openView(item) {

    detailItem.value = item

    detailOpen.value = true

}



function closeDetail() {

    detailItem.value = null

    detailOpen.value = false

}



watch(detailOpen, (isOpen) => {

    if (!isOpen) {

        detailItem.value = null

    }

})



function editItem(item) {

    editingItem.value = item

    editModalOpen.value = true

}



function onArchivedUpdated(updatedItem) {

    if (!updatedItem?.id) {

        fetchData(pagination.value?.current_page || 1)

        return

    }



    archived.value = archived.value.map((entry) => (entry.id === updatedItem.id ? updatedItem : entry))



    if (detailItem.value?.id === updatedItem.id) {

        detailItem.value = updatedItem

    }

}



async function restoreItem(item) {

    const result = await Swal.fire({

        icon: 'question',

        title: 'Restore this article?',

        text: 'It will be moved back to active news.',

        showCancelButton: true,

        confirmButtonText: 'Restore',

        confirmButtonColor: '#057A31',

        cancelButtonText: 'Cancel',

    })



    if (!result.isConfirmed) return



    actionBusyId.value = item.id

    try {

        const res = await fetch(`/archive-news/${item.id}/restore`, {

            method: 'POST',

            headers: csrfHeaders(),

            credentials: 'same-origin',

        })

        if (!res.ok) {
            if (res.status === 419) {
                throw new Error('Your session expired. Please refresh the page and try again.')
            }
            const data = await res.json().catch(() => ({}))
            throw new Error(data.message || 'Restore failed')
        }



        await Swal.fire({

            icon: 'success',

            title: 'Restored!',

            text: 'The news has been successfully restored.',

            confirmButtonColor: '#057A31',

        })

        closeDetail()

        await fetchData(pagination.value?.current_page || 1)

    } catch (e) {

        console.error(e)

        await Swal.fire({ icon: 'error', title: 'Error', text: e.message || 'Failed to restore. Please try again.' })

    } finally {

        actionBusyId.value = null

    }

}



async function deleteItem(item) {

    const result = await Swal.fire({

        icon: 'warning',

        title: 'Delete permanently?',

        text: 'This archived article cannot be recovered.',

        showCancelButton: true,

        confirmButtonText: 'Delete',

        confirmButtonColor: '#dc2626',

        cancelButtonText: 'Cancel',

    })



    if (!result.isConfirmed) return



    actionBusyId.value = item.id

    try {

        const res = await fetch(`/archive-news/${item.id}`, {

            method: 'DELETE',

            headers: csrfHeaders(),

            credentials: 'same-origin',

        })

        if (!res.ok) {
            if (res.status === 419) {
                throw new Error('Your session expired. Please refresh the page and try again.')
            }
            const data = await res.json().catch(() => ({}))
            throw new Error(data.message || 'Delete failed')
        }



        await Swal.fire({

            icon: 'success',

            title: 'Deleted',

            text: 'The archived article was permanently removed.',

            confirmButtonColor: '#057A31',

        })

        closeDetail()

        await fetchData(pagination.value?.current_page || 1)

    } catch (e) {

        console.error(e)

        await Swal.fire({ icon: 'error', title: 'Error', text: e.message || 'Failed to delete. Please try again.' })

    } finally {

        actionBusyId.value = null

    }

}



watch(() => props.search, () => {

    if (fetchTimeout) clearTimeout(fetchTimeout)

    fetchTimeout = setTimeout(() => {

        closeDetail()

        fetchData(1)

    }, 500)

})



watch(() => props.filter, () => {

    if (fetchTimeout) clearTimeout(fetchTimeout)

    fetchTimeout = setTimeout(() => {

        closeDetail()

        fetchData(1)

    }, 500)

})



onMounted(() => {

    fetchData(1)

})



</script>

