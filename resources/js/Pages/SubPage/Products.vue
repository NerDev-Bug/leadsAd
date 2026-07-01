<script setup>
import SidebarLayout from '@/Layouts/SidebarLayout.vue';
import { ref, computed, watch } from 'vue';
import ProductModal from '@/Modals/ProductModal.vue';
import ProductUpdateModal from '@/Modals/ProductUpdateModal.vue';
import ProductDeleteModal from '@/Modals/ProductDeleteModal.vue';
import Pagination from '@/Components/Pagination.vue';
import TableActions from '@/Components/Admin/TableActions.vue';
import TableEmpty from '@/Components/Admin/TableEmpty.vue';
import TableFooter from '@/Components/Admin/TableFooter.vue';
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
        <div class="max-w-7xl mx-auto animate-fade-in">
            <div class="mb-6">
                <h1 class="admin-page-title">Product Monitoring</h1>
                <p class="admin-page-subtitle">Manage and monitor all product listings</p>
            </div>
            <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-3 mb-6 w-full">
                <input v-model="search" type="text" placeholder="Search products..."
                    class="admin-input" />
                <button class="w-full sm:w-auto admin-btn-primary sm:ml-0 sm:mt-0 mt-2"
                    @click="openProductModal">
                    + Add Product
                </button>
            </div>
            <div class="admin-table-wrap overflow-x-auto">
                <table class="admin-table">
                    <thead class="admin-table-head">
                        <tr>
                            <th class="admin-table-th">Product Image</th>
                            <th class="admin-table-th">Product Name Image</th>
                            <th class="admin-table-th">Description</th>
                            <th class="admin-table-th">Features & Benefits</th>
                            <th class="admin-table-th">Dosage</th>
                            <th class="admin-table-th">Target</th>
                            <th class="admin-table-th">Category</th>
                            <th class="admin-table-th">Type</th>
                            <th class="admin-table-th text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="admin-table-body">
                        <tr v-for="product in products" :key="product.id" class="admin-table-row">
                            <td class="admin-table-td whitespace-nowrap">
                                <img v-if="product.image1"
                                    :src="`/products_image/${product.image1.replace('products/', '')}`" alt="Image 1"
                                    class="admin-table-image" />
                                <div v-else class="admin-table-image-placeholder">No Image</div>
                            </td>
                            <td class="admin-table-td whitespace-nowrap">
                                <img v-if="product.image2"
                                    :src="`/products_image/${product.image2.replace('products/', '')}`" alt="Image 2"
                                    class="admin-table-image" />
                                <div v-else class="admin-table-image-placeholder">No Image</div>
                            </td>
                            <td class="admin-table-td whitespace-nowrap" :title="product.description">
                                {{ truncateText(product.description) }}
                            </td>
                            <td class="admin-table-td whitespace-nowrap" :title="product.features">
                                {{ truncateText(product.features) }}
                            </td>
                            <td class="admin-table-td whitespace-nowrap" :title="product.dosage">
                                {{ truncateText(product.dosage) }}
                            </td>
                            <td class="admin-table-td whitespace-nowrap" :title="product.target">
                                {{ truncateText(product.target) }}
                            </td>
                            <td class="admin-table-td whitespace-nowrap">
                                <span class="inline-flex rounded-full bg-brand-50 px-2.5 py-0.5 text-xs font-medium text-brand-700 ring-1 ring-brand-600/20">
                                    {{ product.category }}
                                </span>
                            </td>
                            <td class="admin-table-td whitespace-nowrap">
                                <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600">
                                    {{ product.type }}
                                </span>
                            </td>
                            <td class="admin-table-td-action">
                                <TableActions
                                    edit-title="Edit Product"
                                    delete-title="Delete Product"
                                    @edit="editProduct(product)"
                                    @delete="deleteProduct(product)"
                                />
                            </td>
                        </tr>
                        <TableEmpty
                            v-if="!products.length"
                            :colspan="9"
                            title="No products found"
                            subtitle="Get started by adding your first product"
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
                label="products"
            />

            <div class="mt-4">
                <Pagination v-if="pagination" :pagination="pagination" route-name="/products"
                    @page-changed="handlePageChanged" />
            </div>
        </div>
        <ProductModal v-model="isProductModalOpen" @submitted="handleProductSubmit" />
        <ProductUpdateModal v-model="isProductUpdateModalOpen" :product="selectedProduct"
            @submitted="handleProductSubmit" />
        <ProductDeleteModal v-model="isProductDeleteModalOpen" :product="selectedProduct"
            @deleted="handleProductDeleted" />
    </SidebarLayout>
</template>
