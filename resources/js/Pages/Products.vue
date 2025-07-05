<script setup>
import SidebarLayout from '@/Layouts/SidebarLayout.vue';
import { ref } from 'vue';
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
const products = page.props.products || [];
const pagination = page.props.pagination || {
    current_page: 1,
    last_page: 1,
    per_page: 20,
    total: 0,
    from: 0,
    to: 0,
    has_more_pages: false,
    has_previous_page: false
};

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
</script>

<template>
    <SidebarLayout>
        <div class="max-w-6xl mx-auto">
            <div class="flex justify-start items-center mb-4">
                <h1 class="text-2xl font-bold">Product Monitoring</h1>
            </div>
            <div class="flex justify-end items-center mb-6">
                <button class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow"
                    @click="openProductModal">
                    + Add Product
                </button>
            </div>
            <div class="overflow-x-auto bg-white rounded-lg shadow">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Image 1(Logo)</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Image 2(Name)</th>
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
                        <tr v-for="product in products" :key="product.id">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <img v-if="product.image1" :src="`/storage/${product.image1}`" alt="Image 1"
                                    class="h-12 w-12 object-cover rounded" />
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <img v-if="product.image2" :src="`/storage/${product.image2}`" alt="Image 2"
                                    class="h-12 w-12 object-cover rounded" />
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
                                    class="inline-flex items-center justify-center p-1 rounded hover:bg-blue-100 group mr-2"
                                    title="Edit">
                                    <svg class="w-5 h-5 text-blue-600 group-hover:text-blue-800" fill="none"
                                        stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path
                                            d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 112.828 2.828L11.828 15.828a2 2 0 01-2.828 0L9 13z" />
                                    </svg>
                                </button>
                                <button @click="deleteProduct(product)"
                                    class="inline-flex items-center justify-center p-1 rounded hover:bg-red-100 group"
                                    title="Delete">
                                    <svg class="w-5 h-5 text-red-600 group-hover:text-red-800" fill="none"
                                        stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="3 6 5 6 21 6" />
                                        <path
                                            d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                                        <line x1="10" y1="11" x2="10" y2="17" />
                                        <line x1="14" y1="11" x2="14" y2="17" />
                                    </svg>
                                </button>
                            </td>
                        </tr>
                        <tr v-if="!products.length">
                            <td colspan="9" class="text-center py-4 text-gray-400">No products found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="flex justify-end items-center mb-4 py-2">
                <div v-if="pagination" class="text-sm text-gray-600">
                    Page {{ pagination.current_page }} of {{ pagination.last_page }}
                    <span class="mx-2">•</span>
                    {{ pagination.total }} total products
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
