<script setup>
import SidebarLayout from '@/Layouts/SidebarLayout.vue';
import { ref, computed, watch } from 'vue';
import ProductModal from '@/Modals/ProductModal.vue';
import ProductUpdateModal from '@/Modals/ProductUpdateModal.vue';
import ProductDeleteModal from '@/Modals/ProductDeleteModal.vue';
import Pagination from '@/Components/Pagination.vue';
import { usePage, router } from '@inertiajs/vue3';

const isProductModalOpen = ref(false);
const isProductUpdateModalOpen = ref(false);
const isProductDeleteModalOpen = ref(false);
const selectedProduct = ref(null);

function openProductModal() {
    isProductModalOpen.value = true;
}

function handleProductSubmit(product) {
    // TODO: handle product submission (e.g., send to backend)
    isProductModalOpen.value = false;
}

const page = usePage();

const products = computed(() => page.props.products || []);
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

function truncateText(text, maxLength = 15) {
    if (!text) return '';
    if (text.length <= maxLength) return text;
    return text.slice(0, maxLength) + '...';
}

function editProduct(product) {
    selectedProduct.value = product;
    isProductUpdateModalOpen.value = true;
}
function deleteProduct(product) {
    selectedProduct.value = product;
    isProductDeleteModalOpen.value = true;
}

function handleProductDeleted(deletedProduct) {
    // Product will be automatically removed from the list due to Inertia refresh
    console.log('Product deleted:', deletedProduct);
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
            '/products',
            { search: newVal },
            { preserveState: true, replace: true }
        );
    }, 2000); // 5 seconds debounce
});
</script>

<template>
    <SidebarLayout>
        <div class="max-w-6xl mx-auto">
            <h1 class="text-2xl font-bold mb-4">Product Monitoring</h1>
            <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-3 mb-6 w-full">
                <input
                    v-model="search"
                    type="text"
                    placeholder="Search products..."
                    class="w-full sm:w-64 px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
                <button class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow transition duration-200 sm:ml-0 sm:mt-0 mt-2"
                    @click="openProductModal">
                    + Add Product
                </button>
            </div>
            <div class="overflow-x-auto bg-white rounded-lg shadow">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Product Image</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Product Name Image</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Description</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Features & Benefits</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Dosage</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Target</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Type</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">
                                Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="product in products" :key="product.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <img v-if="product.image1" :src="`/products_image/${product.image1.replace('products/', '')}`" alt="Image 1"
                                    class="h-12 w-12 object-cover rounded shadow-sm" />
                                <div v-else class="h-12 w-12 bg-gray-200 rounded flex items-center justify-center">
                                    <span class="text-gray-400 text-xs">No Image</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <img v-if="product.image2" :src="`/products_image/${product.image2.replace('products/', '')}`" alt="Image 2"
                                    class="h-12 w-12 object-cover rounded shadow-sm" />
                                <div v-else class="h-12 w-12 bg-gray-200 rounded flex items-center justify-center">
                                    <span class="text-gray-400 text-xs">No Image</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap" :title="product.description">{{
                                truncateText(product.description) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap" :title="product.features">{{
                                truncateText(product.features) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap" :title="product.dosage">{{
                                truncateText(product.dosage) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap" :title="product.target">{{
                                truncateText(product.target) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ product.category }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ product.type }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <button @click="editProduct(product)"
                                    class="inline-flex items-center justify-center p-2 rounded-lg hover:bg-blue-100 group mr-2 transition duration-200"
                                    title="Edit Product">
                                    <svg class="w-5 h-5 text-blue-600 group-hover:text-blue-800" fill="none"
                                        stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button @click="deleteProduct(product)"
                                    class="inline-flex items-center justify-center p-2 rounded-lg hover:bg-red-100 group transition duration-200"
                                    title="Delete Product">
                                    <svg class="w-5 h-5 text-red-600 group-hover:text-red-800" fill="none"
                                        stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </td>
                        </tr>
                        <tr v-if="!products.length">
                            <td colspan="9" class="text-center py-8 text-gray-400">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                    </svg>
                                    <p class="text-lg font-medium">No products found</p>
                                    <p class="text-sm">Get started by adding your first product</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="flex justify-between items-center mb-4 py-4">
                <div v-if="pagination" class="text-sm text-gray-600">
                    Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} products
                </div>
                <div v-if="pagination" class="text-sm text-gray-600">
                    Page {{ pagination.current_page }} of {{ pagination.last_page }}
                </div>
            </div>
            <!-- Pagination Component - Bottom Left -->
            <div class="mt-6">
                <Pagination v-if="pagination" :pagination="pagination" @page-changed="handlePageChanged" />
            </div>
        </div>
        <ProductModal v-model="isProductModalOpen" @submitted="handleProductSubmit" />
        <ProductUpdateModal v-model="isProductUpdateModalOpen" :product="selectedProduct"
            @submitted="handleProductSubmit" />
        <ProductDeleteModal v-model="isProductDeleteModalOpen" :product="selectedProduct"
            @deleted="handleProductDeleted" />
    </SidebarLayout>
</template>
