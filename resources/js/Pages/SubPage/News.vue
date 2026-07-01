<script setup>
import SidebarLayout from '@/Layouts/SidebarLayout.vue';
import { ref, computed, watch } from 'vue';
import NewsModal from '@/Modals/NewsModal.vue';
import NewsUpdateModal from '@/Modals/NewsUpdateModal.vue';
import NewsDeleteModal from '@/Modals/NewsDeleteModal.vue';
import ArchiveNewsModal from '@/Modals/ArchiveNewsModal.vue';
import Pagination from '@/Components/Pagination.vue';
import TableActions from '@/Components/Admin/TableActions.vue';
import TableEmpty from '@/Components/Admin/TableEmpty.vue';
import TableFooter from '@/Components/Admin/TableFooter.vue';
import { usePage, router } from '@inertiajs/vue3';

const isNewsModalOpen = ref(false);
const isNewsUpdateModalOpen = ref(false);
const isNewsDeleteModalOpen = ref(false);
const isArchiveModalOpen = ref(false);
const selectedNews = ref(null);

function openNewsModal() {
    isNewsModalOpen.value = true;
}
function openArchiveModal() {
    isArchiveModalOpen.value = true;
}

function handleNewsSubmit(news) {
    // TODO: handle news submission (e.g., send to backend)
    isNewsModalOpen.value = false;
}

const page = usePage();

const news = computed(() => page.props.news || []);
const pagination = computed(() => page.props.pagination || {
    current_page: 1,
    last_page: 1,
    per_page: 20,
    total: 0,
    from: 0,
    to: 0,
    has_more_pages: false,
    has_previous_page: false
});

function truncateText(text, maxLength = 30) {
    if (!text) return '';
    if (text.length <= maxLength) return text;
    return text.slice(0, maxLength) + '...';
}

function formatDate(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

function editNews(newsItem) {
    selectedNews.value = newsItem;
    isNewsUpdateModalOpen.value = true;
}

function deleteNews(newsItem) {
    selectedNews.value = newsItem;
    isNewsDeleteModalOpen.value = true;
}

function handleNewsDeleted(deletedNews) {
    // News will be automatically removed from the list due to Inertia refresh
    console.log('News deleted:', deletedNews);
}

function handlePageChanged(page) {
    console.log('Page changed to:', page);
}

// --- Search Bar Logic ---
const search = ref(page.props.search || '');
let searchTimeout = null;

watch(search, (newVal, oldVal) => {
    if (searchTimeout) clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(
            '/news',
            { search: newVal },
            { preserveState: true, replace: true }
        );
    }, 2000); // 2 seconds debounce
});
</script>

<template>
    <SidebarLayout>
        <div class="max-w-7xl mx-auto animate-fade-in">
            <div class="mb-6">
                <h1 class="admin-page-title">News Management</h1>
                <p class="admin-page-subtitle">Create, edit, and archive news articles</p>
            </div>
            <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-3 mb-6 w-full">
                <input
                    v-model="search"
                    type="text"
                    placeholder="Search news..."
                    class="admin-input"
                />
                <div class="flex gap-2 w-full sm:w-auto">
                    <button class="w-full sm:w-auto admin-btn-primary sm:ml-0 sm:mt-0 mt-2"
                        @click="openNewsModal">
                        + Add News
                    </button>
                    <button class="w-full sm:w-auto admin-btn-secondary sm:ml-0 sm:mt-0 mt-2"
                        @click="openArchiveModal">
                        Archive
                    </button>
                </div>
            </div>
            <div class="admin-table-wrap overflow-x-auto">
                <table class="admin-table">
                    <thead class="admin-table-head">
                        <tr>
                            <th class="admin-table-th">Featured Image</th>
                            <th class="admin-table-th">Article Image</th>
                            <th class="admin-table-th">Title</th>
                            <th class="admin-table-th">Content</th>
                            <th class="admin-table-th">Published Date</th>
                            <th class="admin-table-th text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="admin-table-body">
                        <tr v-for="newsItem in news" :key="newsItem.id" class="admin-table-row">
                            <td class="admin-table-td whitespace-nowrap">
                                <img v-if="newsItem.featured_image"
                                    :src="`/news_image/${newsItem.featured_image.replace('news/', '')}`"
                                    alt="Featured Image"
                                    class="admin-table-image h-16 w-24 rounded-xl" />
                                <div v-else class="admin-table-image-placeholder h-16 w-24">No Image</div>
                            </td>
                            <td class="admin-table-td whitespace-nowrap">
                                <div class="relative h-16 w-32 flex items-center">
                                    <template v-if="newsItem.featured_image_2">
                                        <template v-for="(img, idx) in newsItem.featured_image_2.split(',').filter(i => i.trim()).slice(0, 3)" :key="idx">
                                            <img
                                                :src="`/news_image/${img.trim().replace('news/', '')}`"
                                                alt="Article Image"
                                                class="absolute h-16 w-24 rounded-xl object-cover shadow-sm ring-2 ring-white transition-all duration-200"
                                                :style="{ left: `${idx * 24}px`, zIndex: 10 + idx }"
                                            />
                                        </template>
                                        <span
                                            v-if="newsItem.featured_image_2.split(',').filter(i => i.trim()).length > 3"
                                            class="absolute left-[72px] top-1/2 -translate-y-1/2 rounded-md bg-slate-800 px-2 py-1 text-xs font-medium text-white shadow"
                                            :style="{ zIndex: 20 }"
                                        >
                                            +{{ newsItem.featured_image_2.split(',').filter(i => i.trim()).length - 3 }}
                                        </span>
                                    </template>
                                    <div v-else class="admin-table-image-placeholder h-16 w-24">No Image</div>
                                </div>
                            </td>
                            <td class="admin-table-td whitespace-nowrap">
                                <span class="admin-table-td-primary" :title="newsItem.title">
                                    {{ truncateText(newsItem.title, 15) }}
                                </span>
                            </td>
                            <td class="admin-table-td">
                                <span :title="newsItem.content">{{ truncateText(newsItem.content, 15) }}</span>
                            </td>
                            <td class="admin-table-td whitespace-nowrap">
                                <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600">
                                    {{ formatDate(newsItem.published_at) }}
                                </span>
                            </td>
                            <td class="admin-table-td-action">
                                <TableActions
                                    edit-title="Edit News"
                                    delete-title="Delete News"
                                    @edit="editNews(newsItem)"
                                    @delete="deleteNews(newsItem)"
                                />
                            </td>
                        </tr>
                        <TableEmpty
                            v-if="!news.length"
                            :colspan="6"
                            title="No news articles found"
                            subtitle="Get started by adding your first news article"
                        />
                    </tbody>
                </table>
            </div>

            <TableFooter
                v-if="pagination"
                :from="pagination.from"
                :to="pagination.to"
                :total="pagination.total"
                :current-page="pagination.current_page"
                :last-page="pagination.last_page"
                label="news articles"
            />

            <div class="mt-4">
                <Pagination v-if="pagination" :pagination="pagination" route-name="/news" @page-changed="handlePageChanged" />
            </div>
        </div>

        <!-- Modals -->
        <NewsModal v-model="isNewsModalOpen" @submitted="handleNewsSubmit" />
        <NewsUpdateModal v-model="isNewsUpdateModalOpen" :news="selectedNews" @submitted="handleNewsSubmit" />
        <NewsDeleteModal v-model="isNewsDeleteModalOpen" :news="selectedNews" @deleted="handleNewsDeleted" />
        <ArchiveNewsModal v-model="isArchiveModalOpen" />
    </SidebarLayout>
</template>
