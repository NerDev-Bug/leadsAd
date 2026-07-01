<script setup>
import SidebarLayout from '@/Layouts/SidebarLayout.vue';
import { ref, computed, watch } from 'vue';
import CareersModal from '@/Modals/CareersModal.vue';
import CareersUpdateModal from '@/Modals/CareersUpdateModal.vue';
import CareersDeleteModal from '@/Modals/CareersDeleteModal.vue';
import Pagination from '@/Components/Pagination.vue';
import TableActions from '@/Components/Admin/TableActions.vue';
import TableEmpty from '@/Components/Admin/TableEmpty.vue';
import TableFooter from '@/Components/Admin/TableFooter.vue';
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
        <div class="max-w-7xl mx-auto animate-fade-in">
            <div class="mb-6">
                <h1 class="admin-page-title">Careers Management</h1>
                <p class="admin-page-subtitle">Manage job openings and career listings</p>
            </div>
            <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-3 mb-6 w-full">
                <input
                    v-model="search"
                    type="text"
                    placeholder="Search careers..."
                    class="admin-input"
                />
                <button class="w-full sm:w-auto admin-btn-primary sm:ml-0 sm:mt-0 mt-2"
                    @click="openCareersModal">
                    + Add Career
                </button>
            </div>
            <div class="admin-table-wrap overflow-x-auto">
                <table class="admin-table">
                    <thead class="admin-table-head">
                        <tr>
                            <th class="admin-table-th">Employment Type</th>
                            <th class="admin-table-th">Position</th>
                            <th class="admin-table-th">Subsidiary</th>
                            <th class="admin-table-th">Location</th>
                            <th class="admin-table-th">Job Description</th>
                            <th class="admin-table-th">Qualification</th>
                            <th class="admin-table-th text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="admin-table-body">
                        <tr v-for="career in careers" :key="career.id" class="admin-table-row">
                            <td class="admin-table-td whitespace-nowrap">
                                <span class="admin-table-td-primary" :title="career.employment_type">
                                    {{ truncateText(career.employment_type, 15) }}
                                </span>
                            </td>
                            <td class="admin-table-td whitespace-nowrap">
                                <span class="admin-table-td-primary" :title="career.position">
                                    {{ truncateText(career.position, 15) }}
                                </span>
                            </td>
                            <td class="admin-table-td">
                                <span :title="career.details">{{ truncateText(career.details, 15) }}</span>
                            </td>
                            <td class="admin-table-td whitespace-nowrap">
                                <span :title="career.location">{{ truncateText(career.location, 15) }}</span>
                            </td>
                            <td class="admin-table-td whitespace-nowrap">
                                <span :title="career.job_description">{{ truncateText(career.job_description, 15) }}</span>
                            </td>
                            <td class="admin-table-td whitespace-nowrap">
                                <span :title="career.qualifications">{{ truncateText(career.qualifications, 15) }}</span>
                            </td>
                            <td class="admin-table-td-action">
                                <TableActions
                                    edit-title="Edit Career"
                                    delete-title="Delete Career"
                                    @edit="editCareer(career)"
                                    @delete="deleteCareer(career)"
                                />
                            </td>
                        </tr>
                        <TableEmpty
                            v-if="!careers.length"
                            :colspan="7"
                            title="No career postings found"
                            subtitle="Get started by adding your first career posting"
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
                label="career postings"
            />

            <div class="mt-4">
                <Pagination v-if="pagination" :pagination="pagination" route-name="/careers" @page-changed="handlePageChanged" />
            </div>
        </div>

        <!-- Modals -->
        <CareersModal v-model="isCareersModalOpen" @submitted="handleCareersSubmit" />
        <CareersUpdateModal v-model="isCareersUpdateModalOpen" :career="selectedCareer" @submitted="handleCareersSubmit" />
        <CareersDeleteModal v-model="isCareersDeleteModalOpen" :career="selectedCareer" @deleted="handleCareerDeleted" />
    </SidebarLayout>
</template>
