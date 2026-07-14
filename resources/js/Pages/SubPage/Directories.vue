<script setup>
import SidebarLayout from '@/Layouts/SidebarLayout.vue';
import { ref, computed, watch } from 'vue';
import DirectoriesModal from '@/Modals/DirectoriesModal.vue';
import DirectoriesUpdateModal from '@/Modals/DirectoriesUpdateModal.vue';
import DirectoriesDeleteModal from '@/Modals/DirectoriesDeleteModal.vue';
import DirectoriesImportModal from '@/Modals/DirectoriesImportModal.vue';
import Pagination from '@/Components/Pagination.vue';
import TableActions from '@/Components/Admin/TableActions.vue';
import TableEmpty from '@/Components/Admin/TableEmpty.vue';
import TableFooter from '@/Components/Admin/TableFooter.vue';
import { usePage, router } from '@inertiajs/vue3';

const isDirectoryModalOpen = ref(false);
const isDirectoryUpdateModalOpen = ref(false);
const isDirectoryDeleteModalOpen = ref(false);
const isDirectoryImportModalOpen = ref(false);
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

function handleDirectoryImported() {
    router.visit('/directories');
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
        <div class="max-w-7xl mx-auto animate-fade-in">
            <div class="mb-6">
                <h1 class="admin-page-title">Directories Management</h1>
                <p class="admin-page-subtitle">Manage distributor and directory listings</p>
            </div>

            <!-- Search + Add Button -->
            <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-3 mb-6 w-full">
                <input
                    v-model="search"
                    type="text"
                    placeholder="Search directories..."
                    class="admin-input"
                />
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                    <button
                        class="w-full sm:w-auto admin-btn-secondary"
                        @click="isDirectoryImportModalOpen = true"
                    >
                        Import CSV
                    </button>
                    <button
                        class="w-full sm:w-auto admin-btn-primary"
                        @click="openDirectoryModal"
                    >
                        + Add Directory
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="admin-table-wrap overflow-x-auto">
                <table class="admin-table">
                    <thead class="admin-table-head">
                        <tr>
                            <th class="admin-table-th">Area</th>
                            <th class="admin-table-th">Major Island Group</th>
                            <th class="admin-table-th">Provinces/Cities</th>
                            <th class="admin-table-th">Business Name</th>
                            <th class="admin-table-th">Address</th>
                            <th class="admin-table-th">Contact Name</th>
                            <th class="admin-table-th">Contact No</th>
                            <th class="admin-table-th text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="admin-table-body">
                        <tr v-for="directory in directories" :key="directory.id" class="admin-table-row">
                            <td class="admin-table-td">{{ truncateText(directory.area, 15) }}</td>
                            <td class="admin-table-td">{{ truncateText(directory.region, 15) }}</td>
                            <td class="admin-table-td">{{ truncateText(directory.place, 15) }}</td>
                            <td class="admin-table-td">
                                <span class="admin-table-td-primary">{{ truncateText(directory.business_name, 20) }}</span>
                            </td>
                            <td class="admin-table-td">{{ truncateText(directory.address, 25) }}</td>
                            <td class="admin-table-td">{{ truncateText(directory.contact_name, 15) }}</td>
                            <td class="admin-table-td">
                                <span class="inline-flex rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-medium text-blue-700 ring-1 ring-blue-600/20">
                                    {{ truncateText(directory.contact_no, 15) }}
                                </span>
                            </td>
                            <td class="admin-table-td-action">
                                <TableActions
                                    edit-title="Edit Directory"
                                    delete-title="Delete Directory"
                                    @edit="editDirectory(directory)"
                                    @delete="deleteDirectory(directory)"
                                />
                            </td>
                        </tr>
                        <TableEmpty
                            v-if="!directories.length"
                            :colspan="8"
                            title="No directories found"
                            subtitle="Get started by adding your first directory entry"
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
                label="directories"
            />

            <div class="mt-4">
                <Pagination v-if="pagination" :pagination="pagination" route-name="/directories" @page-changed="handlePageChanged" />
            </div>
        </div>

        <!-- Modals -->
        <DirectoriesModal v-model="isDirectoryModalOpen" @submitted="handleDirectorySubmit" />
        <DirectoriesUpdateModal v-model="isDirectoryUpdateModalOpen" :directory="selectedDirectory" @submitted="handleDirectorySubmit" />
        <DirectoriesDeleteModal v-model="isDirectoryDeleteModalOpen" :directory="selectedDirectory" @deleted="handleDirectoryDeleted" />
        <DirectoriesImportModal v-model="isDirectoryImportModalOpen" @imported="handleDirectoryImported" />
    </SidebarLayout>
</template>
