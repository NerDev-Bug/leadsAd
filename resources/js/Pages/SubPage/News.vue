<script setup>
import SidebarLayout from '@/Layouts/SidebarLayout.vue';
import { ref, computed, watch } from 'vue';
import NewsModal from '@/Modals/NewsModal.vue';
import NewsUpdateModal from '@/Modals/NewsUpdateModal.vue';
import NewsDeleteModal from '@/Modals/NewsDeleteModal.vue';
import ArchiveNewsModal from '@/Modals/ArchiveNewsModal.vue';
import Pagination from '@/Components/Pagination.vue';
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
        <div class="max-w-7xl mx-auto">
            <h1 class="text-2xl font-bold mb-4">News Management</h1>
            <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-3 mb-6 w-full">
                <input
                    v-model="search"
                    type="text"
                    placeholder="Search news..."
                    class="w-full sm:w-64 px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
                <div class="flex gap-2 w-full sm:w-auto">
                    <button class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow transition duration-200 sm:ml-0 sm:mt-0 mt-2"
                        @click="openNewsModal">
                        + Add News
                    </button>
                    <button class="w-full sm:w-auto bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded shadow transition duration-200 sm:ml-0 sm:mt-0 mt-2"
                        @click="openArchiveModal">
                        Archive
                    </button>
                </div>
            </div>
            <div class="overflow-x-auto bg-white rounded-lg shadow">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Featured Image
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Article Image
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Title
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Content
                            </th>

                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Published Date
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">
                                Action
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="newsItem in news" :key="newsItem.id" class="hover:bg-gray-50">
                            <!-- Featured Image -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <img v-if="newsItem.featured_image"
                                         :src="`/news_image/${newsItem.featured_image.replace('news/', '')}`"
                                         alt="Featured Image"
                                         class="h-16 w-24 object-cover rounded shadow-sm" />
                                    <div v-else class="h-16 w-24 bg-gray-200 rounded flex items-center justify-center">
                                        <span class="text-gray-400 text-xs">No Image</span>
                                    </div>
                                </div>
                            </td>
                            <!-- Article Images (featured_image_2, multiple) -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="relative h-16 w-32 flex items-center">
                                    <template v-if="newsItem.featured_image_2">
                                        <template v-for="(img, idx) in newsItem.featured_image_2.split(',').filter(i => i.trim()).slice(0, 3)" :key="idx">
                                            <img
                                                :src="`/news_image/${img.trim().replace('news/', '')}`"
                                                alt="Article Image"
                                                class="absolute h-16 w-24 object-cover rounded shadow-sm border-2 border-white transition-all duration-200"
                                                :style="{
                                                    left: `${idx * 24}px`,
                                                    zIndex: 10 + idx
                                                }"
                                            />
                                        </template>
                                        <span
                                            v-if="newsItem.featured_image_2.split(',').filter(i => i.trim()).length > 3"
                                            class="absolute left-[72px] top-1/2 -translate-y-1/2 bg-gray-700 text-white text-xs px-2 py-1 rounded shadow"
                                            :style="{ zIndex: 20 }"
                                        >
                                            +{{ newsItem.featured_image_2.split(',').filter(i => i.trim()).length - 3 }}
                                        </span>
                                    </template>
                                    <template v-else>
                                        <div class="h-16 w-24 bg-gray-200 rounded flex items-center justify-center">
                                            <span class="text-gray-400 text-xs">No Image</span>
                                        </div>
                                    </template>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900" :title="newsItem.title">
                                    {{ truncateText(newsItem.title, 15) }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900 max-w-xs" :title="newsItem.content">
                                    {{ truncateText(newsItem.content, 15) }}
                                </div>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ formatDate(newsItem.published_at) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <button @click="editNews(newsItem)"
                                    class="inline-flex items-center justify-center p-2 rounded-lg hover:bg-blue-100 group mr-2 transition duration-200"
                                    title="Edit News">
                                    <svg class="w-5 h-5 text-blue-600 group-hover:text-blue-800" fill="none"
                                        stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button @click="deleteNews(newsItem)"
                                    class="inline-flex items-center justify-center p-2 rounded-lg hover:bg-red-100 group transition duration-200"
                                    title="Delete News">
                                    <svg class="w-5 h-5 text-red-600 group-hover:text-red-800" fill="none"
                                        stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </td>
                        </tr>
                        <tr v-if="!news.length">
                            <td colspan="5" class="text-center py-8 text-gray-400">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                                    </svg>
                                    <p class="text-lg font-medium">No news articles found</p>
                                    <p class="text-sm">Get started by adding your first news article</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="flex justify-between items-center mb-4 py-4">
                <div v-if="pagination" class="text-sm text-gray-600">
                    Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} news articles
                </div>
                <div v-if="pagination" class="text-sm text-gray-600">
                    Page {{ pagination.current_page }} of {{ pagination.last_page }}
                </div>
            </div>

            <!-- Pagination Component -->
            <div class="mt-6">
                <Pagination v-if="pagination" :pagination="pagination" @page-changed="handlePageChanged" />
            </div>
        </div>

        <!-- Modals -->
        <NewsModal v-model="isNewsModalOpen" @submitted="handleNewsSubmit" />
        <NewsUpdateModal v-model="isNewsUpdateModalOpen" :news="selectedNews" @submitted="handleNewsSubmit" />
        <NewsDeleteModal v-model="isNewsDeleteModalOpen" :news="selectedNews" @deleted="handleNewsDeleted" />
        <ArchiveNewsModal v-model="isArchiveModalOpen" />
    </SidebarLayout>
</template>
