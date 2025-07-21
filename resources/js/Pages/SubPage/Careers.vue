<script setup>
import SidebarLayout from '@/Layouts/SidebarLayout.vue';
import { ref, computed, watch } from 'vue';
import CareersModal from '@/Modals/CareersModal.vue';
import CareersUpdateModal from '@/Modals/CareersUpdateModal.vue';
import CareersDeleteModal from '@/Modals/CareersDeleteModal.vue';
import Pagination from '@/Components/Pagination.vue';
import { usePage, router } from '@inertiajs/vue3';

const isCareersModalOpen = ref(false);
const isCareersUpdateModalOpen = ref(false);
const isCareersDeleteModalOpen = ref(false);
const selectedCareer = ref(null);

function openCareersModal() {
    isCareersModalOpen.value = true;
}

function handleCareersSubmit(career) {
    // TODO: handle career submission (e.g., send to backend)
    isCareersModalOpen.value = false;
}

const page = usePage();

const careers = computed(() => page.props.careers || []);
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

function editCareer(careerItem) {
    selectedCareer.value = careerItem;
    isCareersUpdateModalOpen.value = true;
}

function deleteCareer(careerItem) {
    selectedCareer.value = careerItem;
    isCareersDeleteModalOpen.value = true;
}

function handleCareerDeleted(deletedCareer) {
    // Career will be automatically removed from the list due to Inertia refresh
    console.log('Career deleted:', deletedCareer);
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
            '/careers',
            { search: newVal },
            { preserveState: true, replace: true }
        );
    }, 2000); // 2 seconds debounce
});
</script>

<template>
    <SidebarLayout>
        <div class="max-w-7xl mx-auto">
            <h1 class="text-2xl font-bold mb-4">Careers Management</h1>
            <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-3 mb-6 w-full">
                <input
                    v-model="search"
                    type="text"
                    placeholder="Search careers..."
                    class="w-full sm:w-64 px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
                <button class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow transition duration-200 sm:ml-0 sm:mt-0 mt-2"
                    @click="openCareersModal">
                    + Add Career
                </button>
            </div>
            <div class="overflow-x-auto bg-white rounded-lg shadow">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Employment Type
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Position
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Details
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Location
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Job Description
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">
                                Action
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="career in careers" :key="career.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900" :title="career.employment_type">
                                    {{ truncateText(career.employment_type, 15) }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900" :title="career.position">
                                    {{ truncateText(career.position, 15) }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900 max-w-xs" :title="career.details">
                                    {{ truncateText(career.details, 15) }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900" :title="career.location">
                                    {{ truncateText(career.location, 15) }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 max-w-xs" :title="career.job_description">
                                    {{ truncateText(career.job_description, 15) }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <button @click="editCareer(career)"
                                    class="inline-flex items-center justify-center p-2 rounded-lg hover:bg-blue-100 group mr-2 transition duration-200"
                                    title="Edit Career">
                                    <svg class="w-5 h-5 text-blue-600 group-hover:text-blue-800" fill="none"
                                        stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button @click="deleteCareer(career)"
                                    class="inline-flex items-center justify-center p-2 rounded-lg hover:bg-red-100 group transition duration-200"
                                    title="Delete Career">
                                    <svg class="w-5 h-5 text-red-600 group-hover:text-red-800" fill="none"
                                        stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </td>
                        </tr>
                        <tr v-if="!careers.length">
                            <td colspan="6" class="text-center py-8 text-gray-400">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                                    </svg>
                                    <p class="text-lg font-medium">No career postings found</p>
                                    <p class="text-sm">Get started by adding your first career posting</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="flex justify-between items-center mb-4 py-4">
                <div v-if="pagination" class="text-sm text-gray-600">
                    Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} career postings
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
        <CareersModal v-model="isCareersModalOpen" @submitted="handleCareersSubmit" />
        <CareersUpdateModal v-model="isCareersUpdateModalOpen" :career="selectedCareer" @submitted="handleCareersSubmit" />
        <CareersDeleteModal v-model="isCareersDeleteModalOpen" :career="selectedCareer" @deleted="handleCareerDeleted" />
    </SidebarLayout>
</template>
