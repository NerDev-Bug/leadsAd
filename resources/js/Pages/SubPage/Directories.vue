<script setup>
import SidebarLayout from '@/Layouts/SidebarLayout.vue';
import { ref, computed, watch } from 'vue';
import DirectoriesModal from '@/Modals/DirectoriesModal.vue';
import DirectoriesUpdateModal from '@/Modals/DirectoriesUpdateModal.vue';
import DirectoriesDeleteModal from '@/Modals/DirectoriesDeleteModal.vue';
import Pagination from '@/Components/Pagination.vue';
import { usePage, router } from '@inertiajs/vue3';

const isDirectoryModalOpen = ref(false);
const isDirectoryUpdateModalOpen = ref(false);
const isDirectoryDeleteModalOpen = ref(false);
const selectedDirectory = ref(null);

function openDirectoryModal() {
    isDirectoryModalOpen.value = true;
}

function handleDirectorySubmit(directory) {
    isDirectoryModalOpen.value = false;
}

const page = usePage();

const directories = computed(() => page.props.directories || []);
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

function editDirectory(directoryItem) {
    selectedDirectory.value = directoryItem;
    isDirectoryUpdateModalOpen.value = true;
}

function deleteDirectory(directoryItem) {
    selectedDirectory.value = directoryItem;
    isDirectoryDeleteModalOpen.value = true;
}

function handleDirectoryDeleted(deletedDirectory) {
    console.log('Directory deleted:', deletedDirectory);
}

function handlePageChanged(page) {
    console.log('Page changed to:', page);
}

// --- Search Bar Logic ---
const search = ref(page.props.search || '');
let searchTimeout = null;

watch(search, (newVal) => {
    if (searchTimeout) clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(
            '/directories',
            { search: newVal },
            { preserveState: true, replace: true }
        );
    }, 2000); // 2 seconds debounce
});
</script>

<template>
    <SidebarLayout>
        <div class="max-w-7xl mx-auto">
            <h1 class="text-2xl font-bold mb-4">Directories Management</h1>

            <!-- Search + Add Button -->
            <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-3 mb-6 w-full">
                <input
                    v-model="search"
                    type="text"
                    placeholder="Search directories..."
                    class="w-full sm:w-64 px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
                <button
                    class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow transition duration-200 sm:ml-0 sm:mt-0 mt-2"
                    @click="openDirectoryModal"
                >
                    + Add Directory
                </button>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto bg-white rounded-lg shadow">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Area
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Major Island Group
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Provinces/Cities
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Business Name
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Address
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Contact Name
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Contact No
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">
                                Action
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="directory in directories" :key="directory.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4">{{ truncateText(directory.area, 15) }}</td>
                            <td class="px-6 py-4">{{ truncateText(directory.region, 15) }}</td>
                            <td class="px-6 py-4">{{ truncateText(directory.place, 15) }}</td>
                            <td class="px-6 py-4">{{ truncateText(directory.business_name, 20) }}</td>
                            <td class="px-6 py-4">{{ truncateText(directory.address, 25) }}</td>
                            <td class="px-6 py-4">{{ truncateText(directory.contact_name, 15) }}</td>
                            <td class="px-6 py-4">{{ truncateText(directory.contact_no, 15) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <button @click="editDirectory(directory)" class="inline-flex items-center justify-center p-2 rounded-lg hover:bg-blue-100 group mr-2 transition duration-200" title="Edit Directory">
                                    <svg class="w-5 h-5 text-blue-600 group-hover:text-blue-800" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button @click="deleteDirectory(directory)" class="inline-flex items-center justify-center p-2 rounded-lg hover:bg-red-100 group transition duration-200" title="Delete Directory">
                                    <svg class="w-5 h-5 text-red-600 group-hover:text-red-800" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </td>
                        </tr>
                        <tr v-if="!directories.length">
                            <td colspan="8" class="text-center py-8 text-gray-400">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                                    </svg>
                                    <p class="text-lg font-medium">No directories found</p>
                                    <p class="text-sm">Get started by adding your first directory entry</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Info -->
            <div class="flex justify-between items-center mb-4 py-4">
                <div v-if="pagination" class="text-sm text-gray-600">
                    Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} directories
                </div>
                <div v-if="pagination" class="text-sm text-gray-600">
                    Page {{ pagination.current_page }} of {{ pagination.last_page }}
                </div>
            </div>

            <!-- Pagination Component -->
            <div class="mt-6">
                <Pagination v-if="pagination" :pagination="pagination" route-name="/directories" @page-changed="handlePageChanged" />
            </div>
        </div>

        <!-- Modals -->
        <DirectoriesModal v-model="isDirectoryModalOpen" @submitted="handleDirectorySubmit" />
        <DirectoriesUpdateModal v-model="isDirectoryUpdateModalOpen" :directory="selectedDirectory" @submitted="handleDirectorySubmit" />
        <DirectoriesDeleteModal v-model="isDirectoryDeleteModalOpen" :directory="selectedDirectory" @deleted="handleDirectoryDeleted" />
    </SidebarLayout>
</template>
